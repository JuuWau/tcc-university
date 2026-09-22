<?php

namespace App\Services;

use App\Data\PrePatients\PrePatientTableFiltersData;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\PrePatient;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class PrePatientService
{
        public function paginate(PrePatientTableFiltersData $filters)
        {
                $query = $this->baseQuery($filters);

                $this->applyFilters($query, $filters);

                return $query
                        ->orderBy(
                                $this->resolveSortField($filters->sortField),
                                $filters->sortDir
                        )
                        ->paginate(
                                $filters->perPage,
                                ['pre_patients.*'],
                                'page',
                                $filters->page
                        );
        }

        private function baseQuery(PrePatientTableFiltersData $filters): Builder
        {
                return PrePatient::query()
                        ->where('university_id', $filters->universityId)
                        ->with([
                                'waitingLists.clinic',
                        ]);
        }

        private function applyFilters(Builder $query, PrePatientTableFiltersData $filters): void
        {
                $query
                        ->when(
                                $filters->search,
                                function (Builder $query, string $search) {
                                        $query->where(function (Builder $query) use ($search) {
                                                $query->where('name', 'ilike', "%{$search}%")
                                                        ->orWhere('cpf', 'ilike', "%{$search}%");
                                        });
                                }
                        )
                        ->when(
                                $filters->status,
                                fn(Builder $query, string $status) =>
                                $query->where('status', $status)
                        )
                        ->when(
                                $filters->clinicId,
                                fn(Builder $query, int $clinicId) =>
                                $query->whereHas(
                                        'waitingLists',
                                        fn(Builder $query) =>
                                        $query->where('clinic_id', $clinicId)
                                )
                        );
        }

        private function resolveSortField(string $sortField): string
        {
                return match ($sortField) {
                        'name' => 'name',
                        'status' => 'status',
                        'created_at' => 'created_at',
                        default => 'created_at',
                };
        }

        public function create(array $data): PrePatient
        {
                return PrePatient::create([
                        'university_id' => auth()->user()->university_id,
                        'name' => $data['name'],
                        'cpf' => $data['cpf'] ?? null,
                        'birth_date' => $data['birth_date'] ?? null,
                        'biological_sex' => $data['biological_sex'],
                        'phone' => $data['phone'] ?? null,
                        'email' => $data['email'] ?? null,
                        'patient_type' => $data['patient_type'],
                        'status' => PrePatient::STATUS_WAITING,
                ]);
        }

        public function update(PrePatient $prePatient, array $data,): PrePatient
        {
                $prePatient->update([
                        'name' => $data['name'],
                        'cpf' => $data['cpf'] ?? null,
                        'birth_date' => $data['birth_date'] ?? null,
                        'biological_sex' => $data['biological_sex'],
                        'phone' => $data['phone'] ?? null,
                        'email' => $data['email'] ?? null,
                        'patient_type' => $data['patient_type'],
                ]);

                return $prePatient->fresh();
        }

        public function delete(PrePatient $prePatient): void
        {
                if ($prePatient->waitingLists()->exists()) {
                        throw new \RuntimeException('Não é possível excluir o pré-paciente, pois ele possui vínculo com uma ou mais clínicas.',);
                }
                $prePatient->delete();
        }

        public function convert(PrePatient $prePatient, string $code): Patient
        {
                return DB::transaction(function () use ($prePatient, $code) {
                        if ($prePatient->status !== PrePatient::STATUS_WAITING) {
                                throw new \RuntimeException(
                                        'Este pré-paciente não pode mais ser convertido.',
                                );
                        }

                        $patient = Patient::create([
                                'university_id' => $prePatient->university_id,
                                'pre_patient_id' => $prePatient->id,
                                'code' => $code,
                                'name' => $prePatient->name,
                                'cpf' => $prePatient->cpf,
                                'birth_date' => $prePatient->birth_date,
                                'biological_sex' => $prePatient->biological_sex,
                                'phone' => $prePatient->phone,
                                'email' => $prePatient->email,
                                'patient_type' => $prePatient->patient_type,
                        ]);

                        $prePatient->prePatientClinics()->delete();

                        $prePatient->waitingLists()->delete();

                        $prePatient->update([
                                'status' => PrePatient::STATUS_CONVERTED,
                        ]);

                        return $patient;
                });
        }

        public function availableForClinic(Clinic $clinic)
        {
                return PrePatient::query()
                        ->where('status', PrePatient::STATUS_WAITING)
                        ->whereDoesntHave('waitingLists', function ($query) use ($clinic) {
                                $query->where('clinic_id', $clinic->id);
                        })
                        ->orderBy('name')
                        ->get();
        }
}
