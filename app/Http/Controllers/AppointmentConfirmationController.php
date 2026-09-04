<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAppointmentStatusRequest;
use App\Http\Resources\AppointmentConfirmationResource;
use App\Models\Appointment;
use App\Services\AppointmentConfirmationService;
use App\Services\ClinicService;
use App\Services\PeriodService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AppointmentConfirmationController extends Controller
{
    public function __construct(
        private AppointmentConfirmationService $appointmentConfirmationService,
    ) {}

    public function index(Request $request)
    {
        $universityId = $request->user()?->university_id;

        $user = $request->user();
        return Inertia::render(
            'appointments-confirmation/AppointmentsConfirmationIndex',
            [
                'appointments' => AppointmentConfirmationResource::collection(
                    $this->appointmentConfirmationService->list($user,
                    [
                        'date' => now()->toDateString(),
                    ])
                )->resolve(),
                'clinics' => $this->appointmentConfirmationService->getAvailableClinics($user, $universityId),
                'periods' => $this->appointmentConfirmationService->getAvailablePeriods($user, $universityId),
                'filters' => [
                    'date' => now()->toDateString(),
                    'clinic_id' => null,
                    'period_id' => null,
                    'status' => null,
                ],
            ]
        );
    }

    public function list(Request $request)
    {
        try {
            $appointments = $this->appointmentConfirmationService->list(
                $request->user(),
                $request->all()
            );

            return response()->json([
                'appointments' => AppointmentConfirmationResource::collection(
                    $appointments
                )
            ]);

        } catch (Throwable $e) {

            report($e);

            return response()->json([
                'message' => 'Erro ao buscar agendamentos.'
            ], 500);
        }
    }

    public function updateStatus(UpdateAppointmentStatusRequest $request, Appointment $appointment) 
    {
        try {
            $this->appointmentConfirmationService->updateStatus(
                $appointment,
                $request->status
            );

            return response()->json([
                'message' => 'Status atualizado com sucesso.',
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Erro ao atualizar status do agendamento.',
            ], 422);
        }
    }
}
