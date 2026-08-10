<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProcedureRequest;
use App\Http\Requests\UpdateProcedureRequest;
use App\Http\Resources\ProcedureListResource;
use App\Models\Procedure;
use App\Services\ProcedureService;
use App\Services\SpecialtyService;
use Inertia\Inertia;

class ProceduresController extends Controller
{
    protected ProcedureService $procedureService;
    protected SpecialtyService $specialtyService;

    public function __construct(ProcedureService $procedureService, SpecialtyService $specialtyService)
    {
        $this->procedureService = $procedureService;
        $this->specialtyService = $specialtyService;
    }

    public function index()
    {
        $universityId = request()->user()?->university_id;
        
        return Inertia::render('procedures/ProceduresIndex', [
            'procedures' => $this->procedureService->all($universityId),
            'specialties' => $this->specialtyService->all($universityId),
        ]);
    }

    public function store(StoreProcedureRequest $request)
    {
        $procedure = $this->procedureService->create(
            $request->validated(),
            $request->user()->university_id
        );

        return response()->json([
            'message' => 'Procedimento criado com sucesso',
            'procedure' => $procedure,
        ]);
    }

    public function update(UpdateProcedureRequest $request, Procedure $procedure)
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();
        abort_if(
            $procedure->university_id !== $user?->university_id,
            403
        );

        $this->procedureService->update($procedure, $request->validated());

        return response()->json([
            'message' => 'Procedimento atualizado com sucesso',
        ]);
    }

    public function destroy(Procedure $procedure)
    {
        /** @var \App\Models\User|null $user */
        $user = request()->user();
        abort_if(
            $procedure->university_id !== $user?->university_id,
            403
        );

        $this->procedureService->delete($procedure);

        return response()->json([
            'message' => 'Procedimento removido com sucesso',
        ]);
    }

    public function list()
    {
        $universityId = request()->user()?->university_id;
        $clinicId = request()->integer('clinic_id');

        return response()->json([
            'procedures' => ProcedureListResource::collection(
                $this->procedureService->all(
                    $universityId,
                    $clinicId
                )
            ),
        ]);
    }
}
