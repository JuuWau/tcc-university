<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSpecialtyRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\SpecialtyService;
use App\Http\Requests\UpdateSpecialtyRequest;
use App\Models\Specialty;

class SpecialtiesController extends Controller
{
    protected $specialtyService;

    public function __construct(SpecialtyService $specialtyService)
    {
        $this->specialtyService = $specialtyService;
    }

    /**
     * Lista especialidades de uma universidade
     */
    public function index()
    {
        return Inertia::render('specialties/SpecialtiesIndex', [
            'specialties' => $this->specialtyService->all(),
        ]);
    }

    public function update(UpdateSpecialtyRequest $request, Specialty $specialty)
    {
        $this->specialtyService->update($specialty, $request->validated());

        return response()->json([
            'message' => 'Especialidade atualizada com sucesso',
        ]);
    }

    public function store(StoreSpecialtyRequest $request)
    {
        $specialty = $this->specialtyService->create(
            $request->validated(),
            $request->user()->university_id
        );

        return response()->json([
            'message' => 'Especialidade criada com sucesso',
            'specialty' => $specialty,
        ]);
    }

    public function destroy(Specialty $specialty)
    {
        /** @var \App\Models\User|null $user */

        abort_if(
            $specialty->university_id !== request()->user()->university_id,
            403
        );

        $this->specialtyService->delete($specialty);

        return response()->json([
            'message' => 'Especialidade removida com sucesso',
        ]);
    }
}
