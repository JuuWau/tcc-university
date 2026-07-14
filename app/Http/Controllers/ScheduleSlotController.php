<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOpenClinicDayRequest;
use App\Http\Requests\StoreOpenScheduleRequest;
use App\Http\Requests\UpdateMultipleScheduleSlotsRequest;
use App\Http\Requests\UpdateScheduleSlotRequest;
use App\Models\Clinic;
use App\Models\Period;
use App\Models\ScheduleSlot;
use App\Models\User;
use App\Services\ClinicService;
use App\Services\PeriodService;
use App\Services\ScheduleSlotService;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ScheduleSlotController extends Controller
{
    public function __construct(
        protected ScheduleSlotService $scheduleSlotService,
        protected ClinicService $clinicService,
        protected PeriodService $periodService,
        protected UserService $userService,
    ) {}

    public function openSchedule()
    {
        $universityId = request()->user()?->university_id;

        return Inertia::render('schedules/OpenSchedule', [
            'periods' => $this->periodService->getPeriods($universityId),
            'responsible' => $this->userService->getResponsible($universityId),
            'clinics' => $this->clinicService->getClinics($universityId),
            'existingSlots' => $universityId
                ? $this->scheduleSlotService->listForUniversity($universityId)
                : [],
        ]);
    }

    public function openClinicsManagement()
    {
        $universityId = request()->user()?->university_id;
        
        $clinics = $universityId
            ? $this->scheduleSlotService->listClinicsWithOpenDays($universityId)
            : [];

        return Inertia::render('schedules/OpenClinicsManagement', [
            'clinics' => $clinics,
        ]);
    }

    public function clinicOpenSchedules(Request $request, Clinic $clinic)
    {
        $universityId = $request->user()?->university_id;
        if (! $universityId || $clinic->university_id !== $universityId) {
            abort(404);
        }

        $periodId = $request->integer('period_id') ?: null;
        $date = $request->input('date');
        $payload = $this->scheduleSlotService->listOpenSchedulesForClinic(
            $universityId,
            $clinic->id,
            $periodId,
            $date ?: null
        );

        return Inertia::render('schedules/OpenClinicSchedules', [
            'clinic' => $payload['clinic'] ?? ['id' => $clinic->id, 'name' => $clinic->name],
            'periods' => $payload['periods'] ?? [],
            'slots' => $payload['slots'] ?? [],
            'responsible' => $this->userService->getResponsible($universityId),
            'filters' => [
                'period_id' => $periodId,
                'date' => $date,
            ],
        ]);
    }

    public function updateSlot(UpdateScheduleSlotRequest $request, ScheduleSlot $slot): JsonResponse
    {
        $universityId = $request->user()?->university_id;
        if (! $universityId || $slot->university_id !== $universityId) {
            return response()->json(['message' => 'Agenda não encontrada.'], 404);
        }

        try {
            $slot = $this->scheduleSlotService->updateSlot($slot, $request->validated(), $universityId);
        } catch (\DomainException $e) {
            $payload = json_decode($e->getMessage(), true);

            return response()->json([
                'message' => $payload['message'] ?? 'Erro ao atualizar agenda.',
                'conflict' => $payload['conflict'] ?? null,
            ], 422);
        }

        return response()->json([
            'message' => 'Agenda atualizada com sucesso.',
            'slots' => $slot,]);
        }

    public function updateMultipleSlots(UpdateMultipleScheduleSlotsRequest $request): JsonResponse
    {
        $data = $request->validated();
        $universityId = $request->user()?->university_id;

        if (! $universityId) {
            return response()->json(['message' => 'Agenda não encontrada.'], 404);
        }

        try {
            $this->scheduleSlotService->updateMultipleSlots($data, $universityId);
        } catch (\DomainException $e) {
            $payload = json_decode($e->getMessage(), true);

            return response()->json([
                'message' => $payload['message'] ?? 'Erro ao atualizar agenda.',
                'conflict' => $payload['conflict'] ?? null,
            ], 422);
        }

        return response()->json(['message' => 'Agenda atualizada com sucesso.']);
    }

    public function destroySlot(Request $request, ScheduleSlot $slot): JsonResponse
    {
        $universityId = $request->user()?->university_id;
        if (! $universityId || $slot->university_id !== $universityId) {
            return response()->json(['message' => 'Agenda não encontrada.'], 404);
        }

        try {
            $this->scheduleSlotService->deleteSlot($slot, $universityId);
        } catch (\DomainException $e) {
            $payload = json_decode($e->getMessage(), true);

            return response()->json([
                'message' => $payload['message'] ?? 'Erro ao excluir agenda.',
            ], 422);
        }

        return response()->json(['message' => 'Agenda excluída com sucesso.']);
    }

    public function destroyMultipleSlots(Request $request): JsonResponse
    {
        $universityId = $request->user()?->university_id;
        if (!$universityId) {
            return response()->json(['message' => 'Agenda não encontrada.'], 404);
        }

        try {
            $ids = $request->input('ids');

            $this->scheduleSlotService->deleteMultipleSlots($ids, $universityId);
        } catch (\DomainException $e) {
            $payload = json_decode($e->getMessage(), true);

            return response()->json([
                'message' => $payload['message'] ?? 'Erro ao excluir agenda.',
            ], 422);
        }

        return response()->json(['message' => 'Agenda excluída com sucesso.']);
    }

    public function storeOpenSchedule(StoreOpenScheduleRequest $request): JsonResponse
    {
        $universityId = $request->user()?->university_id;
        if (! $universityId) {
            return response()->json(['message' => 'Universidade não encontrada'], 422);
        }

        try {
            $slots = $this->scheduleSlotService->open($request->validated(), $universityId);
        } catch (\DomainException $e) {
            $payload = json_decode($e->getMessage(), true);
            return response()->json([
                'message' => $payload['message'] ?? 'Conflito de agenda.',
                'conflict' => $payload['conflict'] ?? null,
            ], 422);
        }

        return response()->json([
            'message' => 'Agenda cadastrada com sucesso',
            'slots' => $slots,
        ]);
    }

    public function storeOpenClinicDay(StoreOpenClinicDayRequest $request, Clinic $clinic): JsonResponse
    {
        $universityId = $request->user()?->university_id;
        if (! $universityId || $clinic->university_id !== $universityId) {
            return response()->json(['message' => 'Clínica não encontrada.'], 404);
        }

        $data = $request->validated();

        try {
            $slots = $this->scheduleSlotService->open([
                'clinic_id' => $clinic->id,
                'available_slots' => $data['available_slots'] ?? 0,
                'period_id' => $data['period_id'],
                'responsible_ids' => $data['responsible_ids'],
                'days' => [$data['date']],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'allow_student_enrollment' => $data['allow_student_enrollment'],
                'allow_student_booking' => $data['allow_student_booking'],
                'allow_procedure_booking' => $data['allow_procedure_booking'],
            ], $universityId);
        } catch (\DomainException $e) {
            $payload = json_decode($e->getMessage(), true);
            return response()->json([
                'message' => $payload['message'] ?? 'Conflito de agenda.',
                'conflict' => $payload['conflict'] ?? null,
            ], 422);
        }

        return response()->json([
            'message' => 'Dia cadastrado com sucesso',
            'slots' => $slots,
        ]);
    }
}
