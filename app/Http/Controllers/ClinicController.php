<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeactivateClinicRequest;
use App\Http\Requests\StoreClinicRequest;
use App\Http\Requests\UpdateClinicRequest;
use App\Models\Clinic;
use App\Services\ClinicService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;

class ClinicController extends Controller
{
    public function __construct(
        protected ClinicService $clinicService
    ) {}

    public function index()
    {
        $universityId = request()->user()?->university_id;

        return Inertia::render('clinics/ClinicsIndex', [
            'clinics' => $universityId
                ? $this->clinicService->all($universityId)
                : [],
        ]);
    }

    public function table(): JsonResponse
    {
        $universityId = request()->user()?->university_id;

        return response()->json([
            'data' => $universityId ? $this->clinicService->all($universityId) : [],
        ]);
    }

    public function store(StoreClinicRequest $request): JsonResponse
    {
        $clinic = $this->clinicService->create(
            $request->validated(),
            $request->user()->university_id
        );

        return response()->json([
            'message' => 'Clínica criada com sucesso',
            'clinic' => $clinic,
        ]);
    }

    public function update(UpdateClinicRequest $request, Clinic $clinic): JsonResponse
    {
        abort_if($clinic->university_id !== $request->user()->university_id, 403);

        $clinic = $this->clinicService->update($clinic, $request->validated());

        return response()->json([
            'message' => 'Clínica atualizada com sucesso',
            'clinic' => $clinic,
        ]);
    }

    public function deactivate(DeactivateClinicRequest $request, Clinic $clinic): JsonResponse
    {
        abort_if($clinic->university_id !== $request->user()->university_id, 403);

        $this->clinicService->deactivate($clinic);

        return response()->json([
            'message' => 'Clínica inativada com sucesso',
        ]);
    }

    public function activate(Clinic $clinic): JsonResponse
    {
        abort_if($clinic->university_id !== request()->user()->university_id, 403);

        $this->clinicService->activate($clinic);

        return response()->json([
            'message' => 'Clínica ativada com sucesso',
        ]);
    }

    public function destroy(Clinic $clinic): JsonResponse
    {
        abort_if($clinic->university_id !== request()->user()->university_id, 403);

        try {
            $this->clinicService->destroy($clinic);
        } catch (\DomainException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => 'Clínica removida com sucesso',
        ]);
    }
}
