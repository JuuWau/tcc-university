<?php

namespace App\Http\Controllers;

use App\Http\Requests\AppointmentCalendarRequest;
use App\Http\Requests\ListByStudentAppointmentRequest;
use App\Http\Requests\PatientScheduleAvailableDaysRequest;
use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use App\Http\Requests\UpdateAppointmentTimeRequest;
use App\Http\Resources\AppointmentResource;
use App\Http\Resources\StudentScheduleEventResource;
use App\Models\Appointment;
use App\Services\AppointmentService;
use App\Services\StudentService;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    protected $studentService;
    protected $appointmentService;

    public function __construct(StudentService $studentService, AppointmentService $appointmentService)
    {
        $this->studentService = $studentService;
        $this->appointmentService = $appointmentService;
    }

    public function listByStudent(ListByStudentAppointmentRequest $request, int $student)
    {
        $result = $this->appointmentService->listByStudent(
            $student,
            $request->integer('clinic_id'),
            $request->date('date'),
        );

        return response()->json([
            'slot' => new StudentScheduleEventResource(
                $result['slot']
            ),
            'schedule_enrollment_id' => $result['schedule_enrollment_id'],
            'appointments' => AppointmentResource::collection(
                $result['appointments']
            ),
        ]);
    }

    public function updateTime(UpdateAppointmentTimeRequest $request, Appointment $appointment,)
    {
        try {
            $appointment = $this->appointmentService->updateTime(
                $appointment,
                $request->date('scheduled_start_at'),
                $request->date('scheduled_end_at'),
            );

            return new AppointmentResource($appointment);
        } catch (\DomainException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }
    }

    public function updateAppointment(UpdateAppointmentRequest $request, Appointment $appointment)
    {
        try {
            $appointment = $this->appointmentService->updateAppointment(
                $appointment,
                $request->validated(),
            );

            return new AppointmentResource($appointment);
        } catch (\DomainException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }
    }

    public function store(StoreAppointmentRequest $request, int $student)
    {
        try {
            $appointment = $this->appointmentService->createAppointment(
                $student,
                $request->validated(),
            );

            return new AppointmentResource(
                $appointment
            );
        } catch (\DomainException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }
    }

    public function availableDays(PatientScheduleAvailableDaysRequest $request, int $student)
    {
        $result = $this->appointmentService->getAvailableDays(
            $request->validated(),
        );

        return response()->json([
            'available_days' => $result,
        ]);
    }

    public function availableTimes(AppointmentCalendarRequest $request)
    {
        $filters = $request->all();

        $availableTimes = $this->appointmentService->getAvailableTimes($filters);

        $appointments = $this->appointmentService->getCalendarAppointmentsByStudent($filters);

        return response()->json([
            'available_times' => $availableTimes,
            'appointments' => $appointments,
        ]);
    }

    public function storePatientCalendar(StoreAppointmentRequest $request, int $patient)
    {
        try {
            $appointment = $this->appointmentService->createPatientAppointment(
                $patient,
                $request->validated(),
            );

            return new AppointmentResource($appointment);
        } catch (\DomainException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }
    }

    public function updatePatientCalendar(UpdateAppointmentRequest $request, int $patient, Appointment $appointment,)
    {
        try {
            $appointment = $this->appointmentService->updatePatientAppointment(
                $patient,
                $appointment,
                $request->validated(),
            );

            return new AppointmentResource($appointment);
        } catch (\DomainException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }
    }

    public function updatePatientCalendarTime(UpdateAppointmentTimeRequest $request, int $patient,Appointment $appointment,) 
    {
        if ($appointment->patient_id !== $patient) {
            return response()->json([
                'message' => 'Este agendamento não pertence ao paciente informado.',
            ], 403);
        }

        try {
            $appointment = $this->appointmentService->updateTime(
                $appointment,
                $request->date('scheduled_start_at'),
                $request->date('scheduled_end_at'),
            );

            return new AppointmentResource($appointment);
        } catch (\DomainException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }
    }
}
