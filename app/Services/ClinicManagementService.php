<?php

namespace App\Services;

use App\Constants\ActivityModules;
use App\Data\ClinicsManagement\ClinicManagementIndexFiltersData;
use App\Data\ClinicsManagement\ClinicManagementTableFiltersData;
use App\Models\Clinic;
use App\Models\ClinicWaitingList;
use App\Models\Patient;
use App\Models\PatientClinic;
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
                'patientClinics as active_patients_count',
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

    public function paginate(Clinic $clinic, ClinicManagementTableFiltersData $filters): LengthAwarePaginator
    {
        if ($filters->status === 'waiting') {
            $query = ClinicWaitingList::query()
                ->with('patient');
        } else {
            $query = PatientClinic::query()
                ->with('patient');
        }

        $query->where('clinic_id', $clinic->id);

        if ($filters->search) {
            $query->whereHas('patient', function ($q) use ($filters) {
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

    public function enrollPatient(Clinic $clinic, array $data): PatientClinic
    {
        return DB::transaction(function () use ($clinic, $data) {
            $patient = Patient::find($data['patient_id']);

            if (!$patient) {
                throw new \Exception('Paciente não encontrado.');
            }

            if (PatientClinic::where('clinic_id', $clinic->id)
                ->where('patient_id', $data['patient_id'])
                ->exists()
            ) {
                throw new \Exception(
                    'Paciente já está inscrito nesta clínica.'
                );
            }

            $patientClinic = PatientClinic::create([
                'clinic_id' => $clinic->id,
                'patient_id' => $data['patient_id'],
                'enrolled_at' => now(),
            ]);

            ClinicWaitingList::where('clinic_id', $clinic->id)
                ->where('patient_id', $data['patient_id'])
                ->delete();

            $changes = ActivityLogService::getCreatedChanges($patientClinic);

            ActivityLogService::trackRelationChanges(
                $changes,
                'clínica',
                [],
                [$clinic->name],
            );

            ActivityLogService::trackBelongsToChange(
                $changes,
                'patient_id',
                'paciente',
                Patient::class,
                null,
                $patient->id,
                fn(Patient $patient) => $patient->name ?? "ID: {$patient->id}",
            );

            ActivityLogService::created(
                ActivityModules::PATIENTS,
                "Paciente {$patient->code} - {$patient->name} inscrito na clínica '{$clinic->name}'.",
                $patientClinic,
                $changes,
            );

            return $patientClinic;
        });
    }

    public function removeEnrollment(Clinic $clinic, Patient $patient): void
    {
        DB::transaction(function () use ($clinic, $patient) {
            $patientClinic = PatientClinic::where('clinic_id', $clinic->id)
                ->where('patient_id', $patient->id)
                ->first();

            if (!$patientClinic) {
                throw new \Exception('Inscrição não encontrada.');
            }

            $changes = ActivityLogService::getCreatedChanges($patientClinic);

            ActivityLogService::trackRelationChanges(
                $changes,
                'clínica',
                [$clinic->name],
                [],
            );

            ActivityLogService::trackBelongsToChange(
                $changes,
                'patient_id',
                'paciente',
                Patient::class,
                $patient->id,
                null,
                fn(Patient $patient) => $patient->name ?? "ID: {$patient->id}",
            );

            $patientClinic->delete();

            ActivityLogService::deleted(
                ActivityModules::PATIENTS,
                "Inscrição do paciente '{$patient->code} - {$patient->name}' removida da clínica '{$clinic->name}'.",
                $patientClinic,
                $changes,
            );
        });
    }

    public function storeWaitingList(Clinic $clinic, array $patientIds): void
    {
        DB::transaction(function () use ($clinic, $patientIds) {
            $patients = Patient::whereIn('id', $patientIds)->get();

            $rows = collect($patientIds)
                ->unique()
                ->map(fn($patientId) => [
                    'clinic_id' => $clinic->id,
                    'patient_id' => $patientId,
                    'enrolled_at' => now(),
                ])
                ->all();

            ClinicWaitingList::insert($rows);

            foreach ($patients as $patient) {
                $waitingList = ClinicWaitingList::where('clinic_id', $clinic->id)
                    ->where('patient_id', $patient->id)
                    ->first();

                if ($waitingList) {
                    $changes = ActivityLogService::getCreatedChanges($waitingList);

                    ActivityLogService::trackRelationChanges(
                        $changes,
                        'clínica',
                        [],
                        [$clinic->name],
                    );

                    ActivityLogService::trackBelongsToChange(
                        $changes,
                        'patient_id',
                        'paciente',
                        Patient::class,
                        null,
                        $patient->id,
                        fn(Patient $patient) => $patient->name ?? "ID: {$patient->id}",
                    );

                    ActivityLogService::created(
                        ActivityModules::PATIENTS,
                        "Paciente {$patient->code} - {$patient->name} adicionado à lista de espera da clínica '{$clinic->name}'.",
                        $waitingList,
                        $changes,
                    );
                }
            }
        });
    }
}
