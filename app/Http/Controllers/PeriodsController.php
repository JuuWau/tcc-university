<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePeriodRequest;
use App\Http\Requests\UpdatePeriodRequest;
use App\Models\Period;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\PeriodService;
use App\Services\SpecialtyService;
use Inertia\Inertia;

class PeriodsController extends Controller
{
    protected $periodService;
    protected $specialtyService;

    public function __construct(PeriodService $periodService, SpecialtyService $specialtyService)
    {
        $this->periodService = $periodService;
        $this->specialtyService = $specialtyService;
    }

    /**
     * Lista períodos de uma universidade
     */
    public function index()
    {
        $universityId = request()->user()?->university_id;
        
        return Inertia::render('periods/PeriodsIndex', [
            'periods' => $this->periodService->all($universityId),
            'specialties' => $this->specialtyService->all($universityId),
        ]);
    }

    public function update(UpdatePeriodRequest $request, Period $period): JsonResponse 
    {
        abort_if(
            $period->university_id !== request()->user()->university_id,
            403
        );

        try {
            $period = $this->periodService->update(
                $period,
                $request->validated()
            );

            return response()->json([
                'message' => 'Período atualizado com sucesso',
                'period' => $period,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function store(StorePeriodRequest $request): JsonResponse
    {
        try {
            $period = $this->periodService->create(
                $request->validated(),
                $request->user()->university_id
            );

            return response()->json([
                'message' => 'Período criado com sucesso',
                'period' => $period,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function destroy(Period $period): JsonResponse
    {
        abort_if(
            $period->university_id !== request()->user()->university_id,
            403
        );

        try {
            $this->periodService->delete($period);

            return response()->json([
                'message' => 'Período removido com sucesso',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
