<?php

namespace App\Http\Controllers;

use App\Data\ClinicsManagement\ClinicManagementTableFiltersData;
use App\Http\Requests\ClinicManagementIndexRequest;
use App\Http\Requests\ClinicManagementTableRequest;
use App\Http\Requests\EnrollPatientRequest;
use App\Http\Requests\StoreClinicWaitingListRequest;
use App\Http\Resources\ClinicManagementIndexResource;
use App\Http\Resources\ClinicManagementPatientResource;
use App\Models\Clinic;
use App\Models\Patient;
use App\Services\ClinicManagementService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClinicManagementController extends Controller
{
    public function __construct(
        private readonly ClinicManagementService $clinicManagementService,
    ) {}

    public function index(ClinicManagementIndexRequest $request) 
    {
        $universityId = auth()->user()->university_id;

        $clinics = $this->clinicManagementService->listClinics($universityId);

        return Inertia::render(
            'clinics-management/ClinicsManagementIndex',
            [
                'clinics' => ClinicManagementIndexResource::collection(
                    $clinics
                )->resolve(),
            ]
        );
    }

    public function show(Clinic $clinic): Response
    {
        return Inertia::render(
            'clinics-management/ClinicManagementShow',
            [
                'clinic' => [
                    'id' => $clinic->id,
                    'name' => $clinic->name,
                ],
            ]
        );
    }

    public function table(ClinicManagementTableRequest $request, Clinic $clinic)
    {
        $patients = $this->clinicManagementService->paginate(
            $clinic,
            ClinicManagementTableFiltersData::fromRequest($request)
        );
        
        return ClinicManagementPatientResource::collection(
            $patients
        );
    }

    public function enroll(EnrollPatientRequest $request,Clinic $clinic,) 
    {
        try {
            $patientClinic = $this->clinicManagementService->enrollPatient(
                $clinic,
                $request->validated()
            );

            return response()->json([
                'message' => 'Paciente inscrito com sucesso.',
                'data' => $patientClinic,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Erro ao inscrever paciente na clínica.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function removeEnrollment(Clinic $clinic, Patient $patient) 
    {
        try {
            $this->clinicManagementService->removeEnrollment(
                $clinic,
                $patient
            );

            return response()->json([
                'message' => 'Inscrição removida com sucesso.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Erro ao remover inscrição.',
            ], 500);
        }
    }

    public function storeWaitingList(StoreClinicWaitingListRequest $request, Clinic $clinic) 
    {
        try {
            $this->clinicManagementService->storeWaitingList(
                $clinic,
                $request->validated('patient_ids')
            );

            return response()->json([
                'message' => 'Pacientes adicionados à lista de espera com sucesso.',
            ]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'message' => 'Erro ao adicionar pacientes à lista de espera.',
            ], 500);

        }
    }
}
