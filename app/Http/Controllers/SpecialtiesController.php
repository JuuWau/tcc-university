<?php

namespace App\Http\Controllers;

use App\Data\Specialty\SpecialtyTableFiltersData;
use App\Http\Requests\StoreSpecialtyRequest;
use App\Http\Requests\TableSpecialtyRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\SpecialtyService;
use App\Http\Requests\UpdateSpecialtyRequest;
use App\Http\Resources\SpecialtyResource;
use App\Models\Specialty;
use Illuminate\Http\JsonResponse;
use Throwable;

class SpecialtiesController extends Controller
{
    protected $specialtyService;

    public function __construct(SpecialtyService $specialtyService)
    {
        $this->specialtyService = $specialtyService;
    }

    public function index()
    {
        return Inertia::render('specialties/SpecialtiesIndex');
    }

    public function table(TableSpecialtyRequest $request)
    {
        $specialties = $this->specialtyService->paginate(
            SpecialtyTableFiltersData::fromRequest($request)
        );

        return SpecialtyResource::collection($specialties);
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

    public function options(): JsonResponse
    {
        $universityId = request()->user()?->university_id;

        return response()->json([
            'specialties' => $this->specialtyService->all($universityId),
        ]);
    }
}
