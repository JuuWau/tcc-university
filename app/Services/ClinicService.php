<?php

namespace App\Services;

use App\Models\Clinic;
use App\Models\ScheduleEnrollment;
use App\Models\ScheduleSlot;
use Illuminate\Support\Facades\DB;

class ClinicService
{
    public function all(int $universityId)
    {
        return Clinic::query()
            ->where('university_id', $universityId)
            ->orderBy('name')
            ->get();
    }

    public function create(array $data, int $universityId): Clinic
    {
        return Clinic::create([
            'university_id' => $universityId,
            'name' => trim((string) $data['name']),
            'active' => true,
        ]);
    }

    public function update(Clinic $clinic, array $data): Clinic
    {
        $clinic->update([
            'name' => trim((string) $data['name']),
        ]);

        return $clinic;
    }

    public function deactivate(Clinic $clinic): void
    {
        DB::transaction(function () use ($clinic) {
            $clinic->update(['active' => false]);

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
        });
    }

    public function activate(Clinic $clinic): void
    {
        $clinic->update(['active' => true]);
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

            $clinic->update(['active' => false]);
            $clinic->delete();
        });
    }
}
