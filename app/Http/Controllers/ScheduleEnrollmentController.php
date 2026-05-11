<?php

namespace App\Http\Controllers;

use App\Http\Requests\RemoveStudentFromSlotRequest;
use App\Http\Requests\SlotStudentsRequest;
use App\Http\Requests\StoreStudentsToScheduleEnrollmentRequest;
use App\Models\Clinic;
use App\Models\ScheduleSlot;
use App\Models\Student;
use App\Models\User;
use App\Services\ClinicService;
use App\Services\PeriodService;
use App\Services\ScheduleSlotService;
use App\Services\UserService;
use App\Services\ScheduleEnrollmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ScheduleEnrollmentController extends Controller
{
    public function __construct(
        protected ScheduleSlotService $scheduleSlotService,
        protected ClinicService $clinicService,
        protected PeriodService $periodService,
        protected UserService $userService,
        protected ScheduleEnrollmentService $scheduleEnrollmentService,
    ) {}

    public function store(StoreStudentsToScheduleEnrollmentRequest $request): JsonResponse
    {
        $universityId = $request->user()?->university_id;

        if (! $universityId) {
            return response()->json([
                'message' => 'Universidade não encontrada.'
            ], 404);
        }

        $this->scheduleEnrollmentService->attachStudents(
            $request->schedule_slot_ids,
            $request->student_ids,
            $universityId
        );

        return response()->json([
            'message' => 'Estudantes adicionados com sucesso.',
        ]);
    }
    
    public function openClinicsSchedullesEnrollmentManagement()
    {
        $user = request()->user();

        $universityId = $user?->university_id;
        $periodId = $this->periodService->getIdByUserId($user?->id);
        $studentId = $user?->student?->id;

        $clinics = $universityId && $periodId
            ? $this->scheduleSlotService->getOpenClinicsForStudentPeriod($universityId, $periodId, $studentId)
            : [];

        return Inertia::render('schedules-enrollment/OpenClinicsSchedulesEnrollmentManagement', [
            'clinics' => $clinics,
        ]);
    }

    public function clinicOpenSchedulesEnrollment(Request $request, Clinic $clinic)
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

        return Inertia::render('schedules-enrollment/OpenClinicSchedulesEnrollment', [
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

    public function slotStudents(SlotStudentsRequest $request, ScheduleSlot $slot)
    {
        $students = $this->scheduleEnrollmentService
            ->getSlotStudents($slot->id);

        return response()->json($students);
    }

    public function removeStudentFromSlot(RemoveStudentFromSlotRequest $request, ScheduleSlot $slot, Student $student)
    {
        $this->scheduleEnrollmentService->removeStudentFromSlot(
            $slot->id,
            $student->id
        );

        return response()->json([
            'message' => 'Aluno removido com sucesso'
        ]);
    }
}
