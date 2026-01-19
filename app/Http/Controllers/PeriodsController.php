<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePeriodRequest;
use App\Http\Requests\UpdatePeriodRequest;
use App\Models\Period;
use Illuminate\Http\Request;
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
        return Inertia::render('periods/PeriodsIndex', [
            'periods' => $this->periodService->all(),
            'specialties' => $this->specialtyService->all(),
        ]);
    }

    public function update(UpdatePeriodRequest $request, Period $period)
    {
        $this->periodService->update($period, $request->validated());

        return response()->json([
            'message' => 'Período atualizado com sucesso',
        ]);
    }

    public function store(StorePeriodRequest $request)
    {
        $period = $this->periodService->create(
            $request->validated(),
            $request->user()->university_id
        );

        return response()->json([
            'message' => 'Período criado com sucesso',
            'period' => $period,
        ]);
    }

    public function destroy(Period $period)
    {
        /** @var \App\Models\User|null $user */

        abort_if(
            $period->university_id !== request()->user()->university_id,
            403
        );

        $this->periodService->delete($period);
        return response()->json([
            'message' => 'Período removido com sucesso',
        ]);
    }
}
