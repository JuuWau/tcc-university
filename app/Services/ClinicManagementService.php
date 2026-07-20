<?php

namespace App\Services;

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
    public function listClinics(int $universityId): Collection
    {
        return Clinic::query()
            ->select([
                'id',
                'name',
            ])
            ->where('university_id', $universityId)
            ->withCount([
                'patientClinics as active_patients_count',
                'waitingList as waiting_patients_count',
            ])
            ->orderBy('name')
            ->get();
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

            return $patientClinic;
        });
    }

    public function removeEnrollment(Clinic $clinic, Patient $patient): void 
    {
        DB::transaction(function () use ($clinic, $patient) {
            PatientClinic::where('clinic_id', $clinic->id)
                ->where('patient_id', $patient->id)
                ->delete();
        });
    }

    public function storeWaitingList(Clinic $clinic, array $patientIds): void 
    {
        DB::transaction(function () use ($clinic, $patientIds) {
            $rows = collect($patientIds)
                ->unique()
                ->map(fn ($patientId) => [
                    'clinic_id' => $clinic->id,
                    'patient_id' => $patientId,
                    'enrolled_at' => now(),
                ])
                ->all();
            ClinicWaitingList::insert($rows);
        });
    }
}