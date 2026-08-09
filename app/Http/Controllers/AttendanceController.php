<?php

namespace App\Http\Controllers;

use App\Data\Attendance\AttendanceClinicsTableFiltersData;
use App\Http\Requests\AttendanceClinicsTableRequest;
use App\Http\Resources\AttendanceClinicResource;
use App\Http\Resources\AttendanceDateResource;
use App\Http\Resources\AttendanceStudentResource;
use App\Http\Requests\UpdateAttendanceRequest;
use App\Models\Clinic;
use App\Models\ScheduleSlot;
use App\Services\ClinicService;
use App\Services\PeriodService;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Http\JsonResponse;

class AttendanceController extends Controller
{
    public function __construct(
        protected AttendanceService $attendanceService,
        protected ClinicService $clinicService,
        protected PeriodService $periodService,
    ) {}

    public function clinics()
    {
        return Inertia::render('attendance/AttendanceClinics');
    }

    public function clinicsTable(AttendanceClinicsTableRequest $request)
    {
        $user = request()->user();

        $clinics = $this->attendanceService->listAvailableClinics(
            $user,
            AttendanceClinicsTableFiltersData::fromRequest($request),
        );

        return AttendanceClinicResource::collection($clinics);
    }

    public function showClinic(Request $request, Clinic $clinic)
    {
        $user = $request->user();

        if ($clinic->university_id !== $user->university_id) {
            abort(404);
        }

        return Inertia::render('attendance/AttendanceClinic', [
            'clinic' => (new AttendanceClinicResource($clinic))->resolve(),
            'periods' => $this->periodService->getPeriodsByClinic(
                $clinic,
                $request->user()
            ),
        ]);
    }

    public function clinicAttendance(Request $request,Clinic $clinic) 
    {
        $universityId = $request->user()?->university_id;

        if (! $universityId || $clinic->university_id !== $universityId) {
            abort(404);
        }

        return Inertia::render('attendance/AttendanceClinic', [
            'clinic' => new AttendanceClinicResource($clinic),
            'periods' => $this->periodService->getPeriods($universityId),
        ]);
    }

    public function getDates(Request $request,Clinic $clinic): JsonResponse 
    {
        $periodId = $request->integer('period_id');

        return response()->json([
            'dates' => AttendanceDateResource::collection(
                $this->attendanceService->getAvailableDates(
                    $clinic,
                    $periodId,
                    $request->user()
                )
            )->resolve(),
        ]);
    }

    public function getStudents(Request $request, ScheduleSlot $slot): JsonResponse 
    {
        return response()->json([
            'students' => AttendanceStudentResource::collection(
                $this->attendanceService->getStudents($slot)
            )->resolve(),
        ]);
    }

    public function updateAttendance(UpdateAttendanceRequest $request, ScheduleSlot $slot): JsonResponse 
    {
        try {
            $this->attendanceService->updateAttendance(
                $slot,
                $request->validated()
            );

            return response()->json([
                'message' => 'Presença salva com sucesso.',
            ]);
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'message' => 'Erro ao salvar presença.',
            ], 422);
        }
    }
}
