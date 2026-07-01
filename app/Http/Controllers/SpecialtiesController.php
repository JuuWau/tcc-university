<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSpecialtyRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\SpecialtyService;
use App\Http\Requests\UpdateSpecialtyRequest;
use App\Models\Specialty;
use Illuminate\Http\JsonResponse;

class SpecialtiesController extends Controller
{
    protected $specialtyService;

    public function __construct(SpecialtyService $specialtyService)
    {
        $this->specialtyService = $specialtyService;
    }

    public function index()
    {
        $universityId = request()->user()?->university_id;

        return Inertia::render('specialties/SpecialtiesIndex', [
            'specialties' => $this->specialtyService->all($universityId),
        ]);
    }

    public function update(UpdateSpecialtyRequest $request, Specialty $specialty): JsonResponse 
    {
        try {
            $this->specialtyService->update(
                $specialty,
                $request->validated()
            );

            return response()->json([
                'message' => 'Especialidade atualizada com sucesso',
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function store(StoreSpecialtyRequest $request): JsonResponse
    {
        try {
            $specialty = $this->specialtyService->create(
                $request->validated(),
                $request->user()->university_id
            );

            return response()->json([
                'message' => 'Especialidade criada com sucesso',
                'specialty' => $specialty,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function destroy(Specialty $specialty): JsonResponse
    {
        try {
            abort_if(
                $specialty->university_id !== request()->user()->university_id,
                403
            );

            $this->specialtyService->delete($specialty);

            return response()->json([
                'message' => 'Especialidade removida com sucesso',
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
