<?php

namespace App\Http\Controllers;

use App\Data\PrePatients\PrePatientTableFiltersData;
use App\Http\Requests\PrePatientConvertRequest;
use App\Http\Requests\PrePatientCreateRequest;
use App\Http\Requests\PrePatientTableRequest;
use App\Http\Requests\PrePatientUpdateRequest;
use App\Http\Resources\PrePatientOptionResource;
use App\Http\Resources\PrePatientTableResource;
use App\Models\Clinic;
use App\Models\PrePatient;
use App\Services\PrePatientService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PrePatientController extends Controller
{
    public function __construct(
        private readonly PrePatientService $prePatientService,
    ) {}

    public function index()
    {
        return Inertia::render('pre-patients/PrePatientIndex');
    }

    public function table(PrePatientTableRequest $request)
    {
        $prePatients = $this->prePatientService->paginate(
            PrePatientTableFiltersData::fromRequest($request)
        );

        return PrePatientTableResource::collection($prePatients);
    }

    public function store(PrePatientCreateRequest $request)
    {
        $prePatient = $this->prePatientService->create(
            $request->validated()
        );

        return response()->json([
            'message' => 'Pré-paciente cadastrado com sucesso.',
            'data' => new PrePatientTableResource($prePatient),
        ], 201);
    }

    public function update(PrePatientUpdateRequest $request, PrePatient $prePatient,)
    {
        $prePatient = $this->prePatientService->update(
            $prePatient,
            $request->validated(),
        );

        return response()->json([
            'message' => 'Pré-paciente atualizado com sucesso.',
            'data' => new PrePatientTableResource($prePatient),
        ]);
    }

    public function destroy(PrePatient $prePatient)
    {
        $this->prePatientService->delete($prePatient);

        return response()->json([
            'message' => 'Pré-paciente removido com sucesso.',
        ]);
    }


    public function convert(PrePatientConvertRequest $request, PrePatient $prePatient) {
        $patient = $this->prePatientService->convert(
            $prePatient,
            $request->validated('code'),
        );

        return response()->json([
            'message' => 'Pré-paciente convertido em paciente com sucesso.',
            'data' => $patient,
        ]);
    }

    public function availablePrePatients(Clinic $clinic)
    {
        return PrePatientOptionResource::collection(
            $this->prePatientService->availableForClinic($clinic)
        )->resolve();
    }
}
