<?php

namespace App\Services;

use App\Constants\ActivityModules;
use App\Data\ClinicsManagement\ClinicManagementIndexFiltersData;
use App\Data\ClinicsManagement\ClinicManagementTableFiltersData;
use App\Models\Clinic;
use App\Models\ClinicWaitingList;
use App\Models\Patient;
use App\Models\PatientClinic;
use App\Models\PrePatient;
use App\Models\PrePatientClinic;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ClinicManagementService
{
    public function listClinics(ClinicManagementIndexFiltersData $filters): LengthAwarePaginator
    {
        return Clinic::query()
            ->select([
                'id',
                'name',
            ])
            ->where('university_id', $filters->universityId)
            ->when($filters->search, function ($query) use ($filters) {
                $query->where(
                    'name',
                    'like',
                    '%' . $filters->search . '%'
                );
            })
            ->withCount([
                'prePatientClinics as active_patients_count',
                'waitingList as waiting_patients_count',
            ])
            ->orderBy('name')
            ->paginate(
                $filters->perPage,
                ['*'],
                'page',
                $filters->page
            );
    }

    public function paginate(Clinic $clinic,  ClinicManagementTableFiltersData $filters): LengthAwarePaginator
    {
        if ($filters->status === 'waiting') {
                    $query = ClinicWaitingList::query()
                            ->with('clinic');
            } else {
                    $query = PrePatientClinic::query()
                            ->with('clinic');
            }

        if ($filters->search) {
            $query->whereHas('prePatient', function ($q) use ($filters) {
                $q->where(
                    'name',
                    'ilike',
                    "%{$filters->search}%"
                );
            });
        }

        $query->orderBy('enrolled_at', 'asc');

        return $query->paginate(
            $filters->perPage,
            ['*'],
            'page',
            $filters->page
        );
    }

    public function enrollPatient(Clinic $clinic, array $data): PrePatientClinic
    {
        return DB::transaction(function () use ($clinic, $data) {
            $prePatient = PrePatient::find($data['pre_patient_id']);

            if (!$prePatient) {
                throw new \Exception('Pré-paciente não encontrado.');
            }

            if (PrePatientClinic::where('clinic_id', $clinic->id)
                ->where('pre_patient_id', $prePatient->id)
                ->exists()
            ) {
                throw new \Exception(
                    'Pré-paciente já está inscrito nessa clínica.'
                );
            }

            $patientClinic = PrePatientClinic::create([
                'clinic_id' => $clinic->id,
                'pre_patient_id' => $prePatient->id,
                'enrolled_at' => now(),
            ]);

            ClinicWaitingList::where('clinic_id', $clinic->id)
                ->where('pre_patient_id', $data['pre_patient_id'])
                ->delete();

            $changes = ActivityLogService::getCreatedChanges($patientClinic);

            ActivityLogService::trackBelongsToChange(
                $changes,
                'clinic_id',
                'clínica',
                Clinic::class,
                null,
                $clinic->id,
                fn(Clinic $clinic) => $clinic->name ?? "ID: {$clinic->id}",
            );

            ActivityLogService::trackBelongsToChange(
                $changes,
                'pre_patient_id',
                'pré-paciente',
                PrePatient::class,
                null,
                $prePatient->id,
                fn(PrePatient $prePatient) => $prePatient->name ?? "ID: {$prePatient->id}",
            );

            ActivityLogService::created(
                ActivityModules::PATIENTS,
                "Pré-paciente {$prePatient->name} inscrito na clínica '{$clinic->name}'.",
                $patientClinic,
                $changes,
            );

            return $patientClinic;
        });
    }


    public function removeEnrollment(Clinic $clinic, PrePatient $prePatient): void 
    {
        DB::transaction(function () use ($clinic, $prePatient) {
            $waitingList = PrePatientClinic::where('clinic_id', $clinic->id)
                ->where('pre_patient_id', $prePatient->id)
                ->first();

            if (!$waitingList) {
                throw new \Exception('Inscrição não encontrada.');
            }

            $changes = ActivityLogService::getCreatedChanges($waitingList);

            ActivityLogService::trackRelationChanges(
                $changes,
                'clínica',
                [$clinic->name],
                [],
            );

            ActivityLogService::trackBelongsToChange(
                $changes,
                'pre_patient_id',
                'pré-paciente',
                PrePatient::class,
                $prePatient->id,
                null,
                fn(PrePatient $prePatient) =>
                $prePatient->name ?? "ID: {$prePatient->id}",
            );

            $waitingList->delete();

            ActivityLogService::deleted(
                ActivityModules::PATIENTS,
                "Inscrição do pré-paciente '{$prePatient->name}' removida da lista de espera da clínica '{$clinic->name}'.",
                $waitingList,
                $changes,
            );
        });
    }


    public function storeWaitingList(Clinic $clinic, array $prePatientIds): void
    {
        DB::transaction(function () use ($clinic, $prePatientIds) {
            $prePatients = PrePatient::whereIn('id', $prePatientIds)->get();

            $rows = collect($prePatientIds)
                ->unique()
                ->map(fn($prePatientId) => [
                    'clinic_id' => $clinic->id,
                    'pre_patient_id' => $prePatientId,
                    'enrolled_at' => now(),
                ])
                ->all();

            ClinicWaitingList::insert($rows);

            foreach ($prePatients as $prePatient) {
                $waitingList = ClinicWaitingList::where('clinic_id', $clinic->id)
                    ->where('pre_patient_id', $prePatient->id)
                    ->first();

                if (!$waitingList) {
                    continue;
                }

                $changes = ActivityLogService::getCreatedChanges($waitingList);

                ActivityLogService::trackRelationChanges(
                    $changes,
                    'clínica',
                    [],
                    [$clinic->name],
                );

                ActivityLogService::trackBelongsToChange(
                    $changes,
                    'pre_patient_id',
                    'pré-paciente',
                    PrePatient::class,
                    null,
                    $prePatient->id,
                    fn(PrePatient $prePatient) => $prePatient->name ?? "ID: {$prePatient->id}",
                );

                ActivityLogService::created(
                    ActivityModules::PATIENTS,
                    "Pré-paciente {$prePatient->name} adicionado à lista de espera da clínica '{$clinic->name}'.",
                    $waitingList,
                    $changes,
                );
            }
        });
    }
}
