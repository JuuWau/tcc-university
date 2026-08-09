<?php

namespace App\Services;

use App\Constants\ActivityLogPrefixes;
use App\Constants\ActivityModules;
use App\Data\OpenClinicsManagement\OpenClinicsManagementFiltersData;
use App\Data\SchedulesEnrollment\OpenClinicsSchedulesEnrollmentFiltersData;
use App\Jobs\ProcessDeleteScheduleSlotsJob;
use App\Jobs\ProcessOpenScheduleJob;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Period;
use App\Models\ScheduleEnrollment;
use App\Models\ScheduleSlot;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ScheduleSlotService
{
    public function listOpenSchedulesForClinic(
        int $universityId,
        int $clinicId,
        ?int $periodId = null,
        ?string $date = null,
        ?int $studentId = null,
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
            ->map(fn(ScheduleSlot $slot) => $slot->period)
            ->filter()
            ->unique('id')
            ->sortBy([
                ['calendar_year', 'desc'],
                ['academic_year', 'asc'],
                ['semester', 'asc'],
            ])
            ->values()
            ->map(fn($period) => [
                'id' => $period->id,
                'label' => "{$period->academic_year}º ano {$period->semester}º semestre de {$period->calendar_year}",
            ])
            ->toArray();

        $slots = $baseQuery
            ->when($periodId, fn($query) => $query->where('period_id', $periodId))
            ->when($date, fn($query) => $query->whereDate('date', $date))
            ->with([
                'period:id,calendar_year,semester,academic_year',
                'responsibles.person:id,user_id,name',
            ])
            ->withExists([
                'enrollments as is_enrolled' => fn($query) => $query
                    ->where('student_id', $studentId)
                    ->where('status', 'active')
            ])
            ->orderBy('date')
            ->orderBy('start_time')
            ->get()
            ->map(fn(ScheduleSlot $slot) => [
                'id' => $slot->id,
                'date' => $slot->date->format('Y-m-d'),
                'start_time' => $slot->start_time,
                'end_time' => $slot->end_time,
                'available_slots' => $slot->available_slots,
                'allow_student_booking' => (bool) $slot->allow_student_booking,
                'allow_student_enrollment' => (bool) $slot->allow_student_enrollment,
                'allow_procedure_booking' => (bool) $slot->allow_procedure_booking,
                'is_enrolled' => (bool) $slot->is_enrolled,
                'period_id' => $slot->period_id,
                'responsible_ids' => $slot->responsibles->pluck('id'),

                'period_label' => $slot->period
                    ? "{$slot->period->academic_year}º ano {$slot->period->semester}º semestre de {$slot->period->calendar_year}"
                    : '—',

                'responsible_names' => $slot->responsibles
                    ->map(fn($user) => $user->person?->name)
                    ->filter()
                    ->values(),
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

    public function listClinicsWithOpenDays(OpenClinicsManagementFiltersData $filters): LengthAwarePaginator
    {
        $today = now()->toDateString();

        $query = Clinic::query()
            ->where('university_id', $filters->universityId)
            ->where('active', true)
            ->whereHas('scheduleSlots', function ($query) use ($today) {
                $query->whereDate('date', '>=', $today);
            })
            ->when($filters->search, function ($query) use ($filters) {
                $query->where(
                    'name',
                    'like',
                    '%' . $filters->search . '%'
                );
            })
            ->with([
                'scheduleSlots' => function ($query) use ($today) {
                    $query
                        ->whereDate('date', '>=', $today)
                        ->orderBy('date')
                        ->orderBy('start_time');
                },
            ])
            ->orderBy('name');

        $clinics = $query->paginate(
            $filters->perPage,
            ['*'],
            'page',
            $filters->page
        );

        $clinics->getCollection()->transform(
            function (Clinic $clinic) {
                $slots = $clinic->scheduleSlots;
                $firstSlot = $slots->first();

                return [
                    'clinic_id' => $clinic->id,
                    'clinic_name' => $clinic->name,
                    'open_days_count' => $slots
                        ->pluck('date')
                        ->unique()
                        ->count(),
                    'open_slots_count' => $slots->count(),
                    'next_open_day' => optional($firstSlot)->date,
                    'next_start_time' => optional($firstSlot)->start_time,
                    'next_end_time' => optional($firstSlot)->end_time,
                ];
            }
        );

        return $clinics;
    }

    public function getOpenClinicsForStudentPeriod(int $universityId, int $periodId, int $studentId, OpenClinicsSchedulesEnrollmentFiltersData $filters,): LengthAwarePaginator
    {
        $clinics = Clinic::query()
            ->where('university_id', $universityId)
            ->where('active', true)

            ->when($filters->search, function ($query) use ($filters) {
                $query->where(
                    'name',
                    'like',
                    '%' . $filters->search . '%'
                );
            })

            ->whereHas('scheduleSlots', function ($query) use ($periodId) {
                $query
                    ->where('period_id', $periodId)
                    ->whereDate(
                        'date',
                        '>=',
                        now()->toDateString()
                    );
            })

            ->with([
                'scheduleSlots' => fn($query) => $query
                    ->where('period_id', $periodId)
                    ->whereDate(
                        'date',
                        '>=',
                        now()->toDateString()
                    )
                    ->orderBy('date')
                    ->orderBy('start_time'),
            ])

            ->withCount([
                'scheduleSlots as enrolled_slots_count' => function ($query) use (
                    $periodId,
                    $studentId
                ) {
                    $query
                        ->where('period_id', $periodId)
                        ->whereDate(
                            'date',
                            '>=',
                            now()->toDateString()
                        )
                        ->whereHas('enrollments', function ($q) use ($studentId) {
                            $q->where('student_id', $studentId);
                        });
                },
            ])

            ->orderBy('name')

            ->paginate(
                $filters->perPage,
                ['*'],
                'page',
                $filters->page
            );

        $clinics->getCollection()->transform(function (Clinic $clinic) {
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
                'open_days_count' => $slots
                    ->pluck('date')
                    ->unique()
                    ->count(),
                'open_slots_count' => $totalSlots,
                'enrolled_slots_count' => $enrolledSlots,
                'enrollment_status' => $status,
            ];
        });

        return $clinics;
    }

    public function listForUniversity(int $universityId): array
    {
        return ScheduleSlot::query()
            ->where('university_id', $universityId)
            ->with([
                'clinic:id,name',
                'responsibles'
            ])
            ->orderBy('date')
            ->orderBy('start_time')
            ->get()
            ->map(fn(ScheduleSlot $slot) => [
                'id' => $slot->id,
                'university_id' => $slot->university_id,
                'period_id' => $slot->period_id,
                'clinic_id' => $slot->clinic_id,
                'clinic_name' => $slot->clinic?->name,
                'responsible_ids' => $slot->responsibles->pluck('id'),
                'date' => $slot->date->format('Y-m-d'),
                'start_time' => $slot->start_time,
                'end_time' => $slot->end_time,
                'available_slots' => $slot->available_slots,
            ])
            ->toArray();
    }

    /**
     * @param array{
     *   clinic_id: int,
     *   available_slots?: int|null,
     *   period_id: int,
     *   responsible_ids: array<int>,
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

        $days = collect($data['days'])
            ->unique()
            ->sort()
            ->values()
            ->all();

        return DB::transaction(function () use ($data, $days, $universityId, $clinic) {

            $created = [];

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

                $slot = ScheduleSlot::create([
                    'university_id' => $universityId,
                    'period_id' => $data['period_id'],
                    'clinic_id' => $data['clinic_id'],
                    'date' => $day,
                    'start_time' => $data['start_time'],
                    'end_time' => $data['end_time'],
                    'available_slots' => $data['available_slots'] ?? 0,
                    'allow_student_booking' => $data['allow_student_booking'] ?? false,
                    'allow_student_enrollment' => $data['allow_student_enrollment'] ?? false,
                    'allow_procedure_booking' => $data['allow_procedure_booking'] ?? false,
                ]);

                if (array_key_exists('responsible_ids', $data)) {
                    $slot->responsibles()->sync($data['responsible_ids'] ?? []);
                }

                $changes = ActivityLogService::getCreatedChanges($slot);

                ActivityLogService::trackRelationChanges(
                    $changes,
                    ActivityLogPrefixes::CLINIC,
                    [],
                    [$clinic->name],
                );

                ActivityLogService::trackBelongsToChange(
                    $changes,
                    'period_id',
                    ActivityLogPrefixes::PERIOD,
                    Period::class,
                    null,
                    $data['period_id'],
                    fn(Period $period) =>
                    "{$period->academic_year}º ano {$period->semester}º semestre de {$period->calendar_year}",
                );

                if (!empty($data['responsible_ids'])) {
                    ActivityLogService::trackRelationChanges(
                        $changes,
                        ActivityLogPrefixes::RESPONSIBLE,
                        [],
                        ActivityLogService::getRelationValues(
                            User::class,
                            $data['responsible_ids'],
                            fn(User $user) => $user->person->name,
                        ),
                    );
                }

                ActivityLogService::created(
                    ActivityModules::SCHEDULES,
                    "Abriu agenda para a clínica '{$clinic->name}' no dia {$formattedDay}.",
                    $slot,
                    $changes,
                );

                $created[] = $slot;
            }

            ProcessOpenScheduleJob::dispatch($created, $data)->afterCommit();

            return collect($created)
                ->sortBy(fn(ScheduleSlot $slot) => sprintf('%s %s', $slot->date, $slot->start_time))
                ->values()
                ->all();
        });
    }

    /**
     * @param array{
     *   period_id: int,
     *   responsible_ids?: array<int>,
     *   date: string,
     *   start_time: string,
     *   end_time: string,
     *   available_slots: int
     * } $data
     */
    public function updateSlot(ScheduleSlot $slot, array $data, int $universityId, int|array|null $exceptIds = null): ScheduleSlot
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
            $exceptIds ?? $slot->id
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

        return DB::transaction(function () use ($slot, $data, $date, $startTime, $endTime) {
            $oldResponsibleIds = $slot->responsibles()->pluck('users.id')->toArray();

            $slot->update([
                'period_id' => $data['period_id'] ?? $slot->period_id,
                'date' => $date,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'available_slots' => $data['available_slots'] ?? $slot->available_slots,
                'allow_student_booking' => $data['allow_student_booking'] ?? $slot->allow_student_booking,
                'allow_student_enrollment' => $data['allow_student_enrollment'] ?? $slot->allow_student_enrollment,
                'allow_procedure_booking' => $data['allow_procedure_booking'] ?? $slot->allow_procedure_booking,
            ]);

            $changes = ActivityLogService::getChanges($slot);

            if ($slot->wasChanged('clinic_id')) {
                $oldClinic = Clinic::find($slot->getOriginal('clinic_id'));
                $newClinic = Clinic::find($slot->clinic_id);

                ActivityLogService::trackRelationChanges(
                    $changes,
                    'clínica',
                    $oldClinic ? [$oldClinic->name] : [],
                    $newClinic ? [$newClinic->name] : [],
                );
            }

            if ($slot->wasChanged('period_id')) {
                ActivityLogService::trackBelongsToChange(
                    $changes,
                    'period_id',
                    'período',
                    Period::class,
                    $slot->getOriginal('period_id'),
                    $slot->period_id,
                    fn(Period $period) => "{$period->academic_year}º ano {$period->semester}º semestre de {$period->calendar_year}",
                );
            }

            if (array_key_exists('responsible_ids', $data)) {
                $newResponsibleIds = $data['responsible_ids'] ?? [];

                $oldResponsibleNames = !empty($oldResponsibleIds)
                    ? ActivityLogService::getRelationValues(
                        User::class,
                        $oldResponsibleIds,
                        fn(User $user) => $user->person->name,
                    )
                    : [];

                $newResponsibleNames = !empty($newResponsibleIds)
                    ? ActivityLogService::getRelationValues(
                        User::class,
                        $newResponsibleIds,
                        fn(User $user) => $user->person->name,
                    )
                    : [];

                ActivityLogService::trackRelationChanges(
                    $changes,
                    'responsables',
                    $oldResponsibleNames,
                    $newResponsibleNames,
                );

                $slot->responsibles()->sync($newResponsibleIds);
            }

            if (!empty($changes)) {
                $formattedDate = \Carbon\Carbon::parse($slot->date)->format('d/m/Y');
                $clinicName = $slot->clinic?->name ?? 'Clínica não encontrada';

                ActivityLogService::updated(
                    ActivityModules::SCHEDULES,
                    "Atualizou agenda para a clínica '{$clinicName}' no dia {$formattedDate}.",
                    $slot,
                    $changes,
                );
            }

            return $slot->fresh([
                'period',
                'responsibles.person',
            ]);
        });
    }

    public function updateMultipleSlots(array $data, int $universityId): void
    {
        DB::transaction(function () use ($data, $universityId) {

            $ids = collect($data['ids'])
                ->map(fn($id) => (int) $id)
                ->toArray();

            $slots = ScheduleSlot::whereIn('id', $ids)->get();

            foreach ($slots as $slot) {
                $this->updateSlot(
                    $slot,
                    $data,
                    $universityId,
                    $ids
                );
            }
        });
    }

    public function deleteSlot(ScheduleSlot $slot, int $universityId): void
    {
        DB::transaction(function () use ($slot) {
            $slot->loadMissing([
                'clinic',
                'period',
                'responsibles.person',
            ]);

            $changes = ActivityLogService::getCreatedChanges($slot);

            ActivityLogService::trackRelationChanges(
                $changes,
                ActivityLogPrefixes::CLINIC,
                [],
                [$slot->clinic->name],
            );

            ActivityLogService::trackRelationChanges(
                $changes,
                ActivityLogPrefixes::PERIOD,
                [],
                [
                    "{$slot->period->academic_year}º ano {$slot->period->semester}º semestre de {$slot->period->calendar_year}",
                ],
            );

            ActivityLogService::trackRelationChanges(
                $changes,
                ActivityLogPrefixes::RESPONSIBLE,
                [],
                $slot->responsibles
                    ->map(fn($responsible) => $responsible->person->name)
                    ->sort()
                    ->values()
                    ->toArray(),
            );

            ActivityLogService::deleted(
                ActivityModules::SCHEDULES,
                "Removeu a agenda da clínica '{$slot->clinic->name}' do dia {$slot->date->format('d/m/Y')}.",
                $slot,
                $changes,
            );

            ProcessDeleteScheduleSlotsJob::dispatch([$slot->id])
                ->afterCommit();
        });
    }

    public function deleteMultipleSlots(array $ids, int $universityId): void
    {
        DB::transaction(function () use ($ids, $universityId) {
            $slots = ScheduleSlot::query()
                ->with([
                    'clinic',
                    'period',
                    'responsibles.person',
                ])
                ->whereIn('id', $ids)
                ->where('university_id', $universityId)
                ->get();

            if ($slots->isEmpty()) {
                return;
            }

            foreach ($slots as $slot) {
                $changes = ActivityLogService::getCreatedChanges($slot);

                ActivityLogService::trackRelationChanges(
                    $changes,
                    ActivityLogPrefixes::CLINIC,
                    [],
                    [$slot->clinic->name],
                );

                ActivityLogService::trackRelationChanges(
                    $changes,
                    ActivityLogPrefixes::PERIOD,
                    [],
                    [[
                        "{$slot->period->academic_year}º ano {$slot->period->semester}º semestre de {$slot->period->calendar_year}",
                    ]],
                );

                ActivityLogService::trackRelationChanges(
                    $changes,
                    ActivityLogPrefixes::RESPONSIBLE,
                    [],
                    $slot->responsibles
                        ->map(fn($responsible) => $responsible->person->name)
                        ->sort()
                        ->values()
                        ->toArray(),
                );

                ActivityLogService::deleted(
                    ActivityModules::SCHEDULES,
                    "Removeu a agenda da clínica '{$slot->clinic->name}' do dia {$slot->date->format('d/m/Y')}.",
                    $slot,
                    $changes,
                );
            }

            ProcessDeleteScheduleSlotsJob::dispatch(
                $slots->pluck('id')->all()
            )->afterCommit();
        });
    }

    private function findConflict(
        int $universityId,
        int $clinicId,
        string $date,
        string $startTime,
        string $endTime,
        int|array|null $exceptIds = null
    ): ?ScheduleSlot {
        $query = ScheduleSlot::query()
            ->with('clinic:id,name')
            ->where('university_id', $universityId)
            ->where('clinic_id', $clinicId)
            ->whereDate('date', $date)
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->orderBy('start_time');

        if (is_int($exceptIds)) {
            $query->where('id', '!=', $exceptIds);
        }

        if (is_array($exceptIds) && !empty($exceptIds)) {
            $query->whereNotIn('id', $exceptIds);
        }

        return $query->first();
    }
}
