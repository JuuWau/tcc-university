<?php

namespace App\Services;

use App\Constants\ActivityModules;
use App\Models\Clinic;
use App\Models\ScheduleEnrollment;
use App\Models\ScheduleSlot;
use Illuminate\Support\Facades\DB;

class ClinicService
{
    public function all(int $universityId)
    {
        return Clinic::query()
            ->with('specialty')
            ->where('university_id', $universityId)
            ->orderBy('name')
            ->get();
    }

    public function create(array $data, int $universityId): Clinic
    {
        return DB::transaction(function () use ($data, $universityId) {
            $clinic = Clinic::create([
                'university_id' => $universityId,
                'name' => trim((string) $data['name']),
                'active' => true,
                'specialty_id' => $data['specialty_id'],
            ]);

            ActivityLogService::created(
                ActivityModules::CLINICS,
                "Cadastrou a clínica '{$clinic->name}'.",
                $clinic,
            );

            return $clinic;
        });
    }

    public function update(Clinic $clinic, array $data): Clinic
    {
        return DB::transaction(function () use ($clinic, $data) {
            $clinic->fill([
                'name' => trim((string) $data['name']),
                'specialty_id' => $data['specialty_id'],
            ]);

            $changes = ActivityLogService::getChanges($clinic);

            if (empty($changes)) {
                return $clinic;
            }

            $clinic->save();

            ActivityLogService::updated(
                ActivityModules::CLINICS,
                "Atualizou a clínica '{$clinic->name}'.",
                $clinic,
                $changes,
            );

            return $clinic;
        });
    }

    public function deactivate(Clinic $clinic): void
    {
        DB::transaction(function () use ($clinic) {
            $clinic->fill([
                'active' => false,
            ]);

            $changes = ActivityLogService::getChanges($clinic);

            $clinic->save();

            $slotIds = ScheduleSlot::query()
                ->where('clinic_id', $clinic->id)
                ->whereDate('date', '>=', now()->toDateString())
                ->pluck('id');

            if ($slotIds->isNotEmpty()) {
                ScheduleEnrollment::query()
                    ->whereIn('schedule_slot_id', $slotIds)
                    ->delete();

                ScheduleSlot::query()
                    ->whereIn('id', $slotIds)
                    ->delete();
            }

            if (!empty($changes)) {
                ActivityLogService::updated(
                    ActivityModules::CLINICS,
                    "Inativou a clínica '{$clinic->name}'.",
                    $clinic,
                    $changes,
                );
            }
        });
    }

    public function activate(Clinic $clinic): void
    {
        DB::transaction(function () use ($clinic) {
            $clinic->fill([
                'active' => true,
            ]);

            $changes = ActivityLogService::getChanges($clinic);

            if (empty($changes)) {
                return;
            }

            $clinic->save();

            ActivityLogService::updated(
                ActivityModules::CLINICS,
                "Ativou a clínica '{$clinic->name}'.",
                $clinic,
                $changes,
            );
        });
    }

    public function destroy(Clinic $clinic): void
    {
        $hasPastHistory = ScheduleSlot::withTrashed()
            ->where('clinic_id', $clinic->id)
            ->whereDate('date', '<', now()->toDateString())
            ->exists();

        if ($hasPastHistory) {
            throw new \DomainException(
                'Não é possível excluir clínica com histórico de agendas realizadas. Utilize inativação.'
            );
        }

        DB::transaction(function () use ($clinic) {
            $slotIds = ScheduleSlot::query()
                ->where('clinic_id', $clinic->id)
                ->pluck('id');

            if ($slotIds->isNotEmpty()) {
                ScheduleEnrollment::query()
                    ->whereIn('schedule_slot_id', $slotIds)
                    ->delete();

                ScheduleSlot::query()
                    ->whereIn('id', $slotIds)
                    ->delete();
            }

            $clinic->fill([
                'active' => false,
            ]);

            $changes = ActivityLogService::getChanges($clinic);

            $clinic->save();

            if (!empty($changes)) {
                ActivityLogService::deleted(
                    ActivityModules::CLINICS,
                    "Removeu a clínica '{$clinic->name}'.",
                    $clinic,
                    $changes,
                );
            }

            $clinic->delete();
        });
    }

    public function getClinics(?int $universityId)
    {
        return Clinic::query()
            ->when($universityId, fn($q) => $q->where('university_id', $universityId))
            ->where('active', true)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn($clinic) => [
                'id' => $clinic->id,
                'label' => $clinic->name,
            ]);
    }
}
