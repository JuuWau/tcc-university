<?php

namespace App\Services;

use App\Models\Clinic;
use App\Models\ScheduleSlot;
use Illuminate\Support\Facades\DB;

class ScheduleSlotService
{
    public function listOpenSchedulesForClinic(
        int $universityId,
        int $clinicId,
        ?int $periodId = null,
        ?string $date = null
    ): array {
        $clinic = Clinic::query()
            ->where('university_id', $universityId)
            ->where('id', $clinicId)
            ->first();

        if (! $clinic) {
            return [];
        }

        $baseQuery = ScheduleSlot::query()
            ->where('university_id', $universityId)
            ->where('clinic_id', $clinicId)
            ->whereDate('date', '>=', now()->toDateString());

        $periodOptions = (clone $baseQuery)
            ->with('period:id,calendar_year,semester,academic_year')
            ->get()
            ->map(fn (ScheduleSlot $slot) => $slot->period)
            ->filter()
            ->unique('id')
            ->sortBy([
                ['calendar_year', 'desc'],
                ['academic_year', 'asc'],
                ['semester', 'asc'],
            ])
            ->values()
            ->map(fn ($period) => [
                'id' => $period->id,
                'label' => "{$period->calendar_year}/{$period->semester} - {$period->academic_year}º ano",
            ])
            ->toArray();

        $slots = $baseQuery
            ->when($periodId, fn ($query) => $query->where('period_id', $periodId))
            ->when($date, fn ($query) => $query->whereDate('date', $date))
            ->with([
                'period:id,calendar_year,semester,academic_year',
                'responsible.person:id,user_id,name',
            ])
            ->orderBy('date')
            ->orderBy('start_time')
            ->get()
            ->map(fn (ScheduleSlot $slot) => [
                'id' => $slot->id,
                'date' => $slot->date,
                'start_time' => $slot->start_time,
                'end_time' => $slot->end_time,
                'available_slots' => $slot->available_slots,
                'period_id' => $slot->period_id,
                'responsible_id' => $slot->responsible_id,
                'period_label' => $slot->period
                    ? "{$slot->period->calendar_year}/{$slot->period->semester} - {$slot->period->academic_year}º ano"
                    : '—',
                'responsible_name' => $slot->responsible?->person?->name ?? '—',
            ])
            ->toArray();

        return [
            'clinic' => [
                'id' => $clinic->id,
                'name' => $clinic->name,
            ],
            'periods' => $periodOptions,
            'slots' => $slots,
        ];
    }

    public function listClinicsWithOpenDays(int $universityId): array
    {
        return Clinic::query()
            ->where('university_id', $universityId)
            ->where('active', true)
            ->whereHas('scheduleSlots', fn ($query) => $query->whereDate('date', '>=', now()->toDateString()))
            ->with([
                'scheduleSlots' => fn ($query) => $query
                    ->whereDate('date', '>=', now()->toDateString())
                    ->orderBy('date')
                    ->orderBy('start_time'),
            ])
            ->orderBy('name')
            ->get()
            ->map(function (Clinic $clinic) {
                $slots = $clinic->scheduleSlots;
                $firstSlot = $slots->first();

                return [
                    'clinic_id' => $clinic->id,
                    'clinic_name' => $clinic->name,
                    'open_days_count' => $slots->pluck('date')->unique()->count(),
                    'open_slots_count' => $slots->count(),
                    'next_open_day' => optional($firstSlot)->date,
                    'next_start_time' => optional($firstSlot)->start_time,
                    'next_end_time' => optional($firstSlot)->end_time,
                ];
            })
            ->values()
            ->toArray();
    }

    public function getOpenClinicsForStudentPeriod(int $universityId, int $periodId, int $studentId): array
    {
        return Clinic::query()
            ->where('university_id', $universityId)
            ->where('active', true)
            ->whereHas('scheduleSlots', function ($query) use ($periodId) {
                $query->where('period_id', $periodId)
                    ->whereDate('date', '>=', now()->toDateString());
            })
            ->with([
                'scheduleSlots' => fn ($query) => $query
                    ->where('period_id', $periodId)
                    ->whereDate('date', '>=', now()->toDateString())
                    ->orderBy('date')
                    ->orderBy('start_time'),
            ])
            ->withCount([
                'scheduleSlots as enrolled_slots_count' => function ($query) use ($periodId, $studentId) {
                    $query->where('period_id', $periodId)
                        ->whereDate('date', '>=', now()->toDateString())
                        ->whereHas('enrollments', function ($q) use ($studentId) {
                            $q->where('student_id', $studentId);
                        });
                }
            ])
            ->orderBy('name')
            ->get()
            ->map(function (Clinic $clinic) {
                $slots = $clinic->scheduleSlots;
                $totalSlots = $slots->count();
                $enrolledSlots = $clinic->enrolled_slots_count;

                $status = match (true) {
                    $enrolledSlots === 0 => 'not_enrolled',
                    $enrolledSlots === $totalSlots => 'fully_enrolled',
                    default => 'partially_enrolled',
                };

                return [
                    'clinic_id' => $clinic->id,
                    'clinic_name' => $clinic->name,
                    'open_days_count' => $slots->pluck('date')->unique()->count(),
                    'open_slots_count' => $totalSlots,
                    'enrolled_slots_count' => $enrolledSlots,
                    'enrollment_status' => $status,
                ];
            })
            ->values()
            ->toArray();
    }

    public function listForUniversity(int $universityId): array
    {
        return ScheduleSlot::query()
            ->where('university_id', $universityId)
            ->with('clinic:id,name')
            ->orderBy('date')
            ->orderBy('start_time')
            ->get()
            ->map(fn (ScheduleSlot $slot) => [
                'id' => $slot->id,
                'university_id' => $slot->university_id,
                'period_id' => $slot->period_id,
                'clinic_id' => $slot->clinic_id,
                'clinic_name' => $slot->clinic?->name,
                'responsible_id' => $slot->responsible_id,
                'date' => $slot->date,
                'start_time' => $slot->start_time,
                'end_time' => $slot->end_time,
                'available_slots' => $slot->available_slots,
            ])
            ->toArray();
    }

    /**
     * @param array{
     *   clinic_id: int,
     *   available_chairs?: int|null,
     *   period_id: int,
     *   responsible_id: int,
     *   days: array<int,string>,
     *   start_time: string,
     *   end_time: string
     * } $data
     * @return array<int, ScheduleSlot>
     */
    public function open(array $data, int $universityId): array
    {
        $clinic = Clinic::query()
            ->where('id', $data['clinic_id'])
            ->where('university_id', $universityId)
            ->where('active', true)
            ->first();

        if (! $clinic) {
            throw new \DomainException(json_encode([
                'message' => 'Clínica inválida ou inativa para esta universidade.',
            ], JSON_UNESCAPED_UNICODE));
        }

        $days = collect($data['days'])->unique()->sort()->values()->all();

        return DB::transaction(function () use ($data, $days, $universityId) {
            foreach ($days as $day) {
                $conflict = $this->findConflict(
                    $universityId,
                    $data['clinic_id'],
                    $day,
                    $data['start_time'],
                    $data['end_time'],
                    null
                );

                $formattedDay = \Carbon\Carbon::parse($day)->format('d/m/Y');

                if ($conflict) {
                    throw new \DomainException(json_encode([
                        'message' => "Conflito no dia {$formattedDay}, já possui agenda aberta nesse dia",
                        'conflict' => [
                            'requested_date' => $day,
                            'clinic_id' => $conflict->clinic_id,
                            'clinic_name' => $conflict->clinic?->name,
                            'date' => $conflict->date,
                            'start_time' => substr((string) $conflict->start_time, 0, 5),
                            'end_time' => substr((string) $conflict->end_time, 0, 5),
                            'period_id' => $conflict->period_id,
                        ],
                    ], JSON_UNESCAPED_UNICODE));
                }
            }

            $created = [];
            foreach ($days as $day) {
                $created[] = ScheduleSlot::create([
                    'university_id' => $universityId,
                    'period_id' => $data['period_id'],
                    'clinic_id' => $data['clinic_id'],
                    'responsible_id' => $data['responsible_id'],
                    'date' => $day,
                    'start_time' => $data['start_time'],
                    'end_time' => $data['end_time'],
                    'available_slots' => $data['available_chairs'] ?? 0,
                ]);
            }

            return collect($created)
                ->sortBy(fn (ScheduleSlot $slot) => sprintf('%s %s', $slot->date, $slot->start_time))
                ->values()
                ->all();
        });
    }

    /**
     * @param array{
     *   period_id: int,
     *   responsible_id: int,
     *   date: string,
     *   start_time: string,
     *   end_time: string,
     *   available_slots: int
     * } $data
     */
    public function updateSlot(ScheduleSlot $slot, array $data, int $universityId): ScheduleSlot
    {
        if ($slot->university_id !== $universityId) {
            throw new \DomainException(json_encode([
                'message' => 'Agenda não encontrada.',
            ], JSON_UNESCAPED_UNICODE));
        }

        $date = $data['date'] ?? $slot->date;
        $startTime = $data['start_time'] ?? $slot->start_time;
        $endTime = $data['end_time'] ?? $slot->end_time;

        $conflict = $this->findConflict(
            $universityId,
            $slot->clinic_id,
            $date,
            $startTime,
            $endTime,
            $slot->id
        );

        if ($conflict) {
            throw new \DomainException(json_encode([
                'message' => 'Conflito de agenda na clínica neste horário.',
                'conflict' => [
                    'clinic_id' => $conflict->clinic_id,
                    'clinic_name' => $conflict->clinic?->name,
                    'date' => $conflict->date,
                    'start_time' => substr((string) $conflict->start_time, 0, 5),
                    'end_time' => substr((string) $conflict->end_time, 0, 5),
                    'period_id' => $conflict->period_id,
                ],
            ], JSON_UNESCAPED_UNICODE));
        }

        $slot->update([
            'period_id' => $data['period_id'] ?? $slot->period_id,
            'responsible_id' => $data['responsible_id'] ?? $slot->responsible_id,
            'date' => $date,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'available_slots' => $data['available_slots'] ?? $slot->available_slots,
        ]);

        return $slot->fresh(['period', 'responsible.person']);
    }

    public function updateMultipleSlots(array $data, int $universityId)
    {
        $ids = collect($data['ids'])
            ->map(fn($id) => (int) $id)
            ->toArray();

        $slots = ScheduleSlot::whereIn('id', $ids)->get();

        foreach ($slots as $slot) {
            $this->updateSlot($slot, $data, $universityId);
        }
    }

    public function deleteSlot(ScheduleSlot $slot, int $universityId): void
    {
        if ($slot->university_id !== $universityId) {
            throw new \DomainException(json_encode([
                'message' => 'Agenda não encontrada.',
            ], JSON_UNESCAPED_UNICODE));
        }

        $slot->delete();
    }

    public function deleteMultipleSlots(array $ids, $universityId)
    {
        ScheduleSlot::whereIn('id', $ids)
            ->where('university_id', $universityId)
            ->delete();
    }

    private function findConflict(
        int $universityId,
        int $clinicId,
        string $date,
        string $startTime,
        string $endTime,
        ?int $exceptSlotId = null
    ): ?ScheduleSlot {
        $query = ScheduleSlot::query()
            ->with('clinic:id,name')
            ->where('university_id', $universityId)
            ->where('clinic_id', $clinicId)
            ->whereDate('date', $date)
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->orderBy('start_time');

        if ($exceptSlotId !== null) {
            $query->where('id', '!=', $exceptSlotId);
        }

        return $query->first();
    }
}
