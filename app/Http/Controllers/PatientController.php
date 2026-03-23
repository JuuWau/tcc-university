<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientStudentDataRequest;
use App\Http\Requests\UpdatePatientStudentRequest;
use App\Http\Requests\UpdatePatientRequest;
use App\Models\Student;
use App\Services\PatientService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PatientController extends Controller
{
    public function __construct(
        protected PatientService $patientService
    ) {}

    public function index(Request $request)
    {
        $universityId = $request->user()?->university_id;
        $students = Student::when($universityId, fn($q) => $q->where('university_id', $universityId))
            ->with('person:id,name')
            ->orderBy('id')
            ->get()
            ->map(fn($s) => ['id' => $s->id, 'name' => $s->person?->name ?? '—']);

        return Inertia::render('patients/PatientsIndex', [
            'students' => $students,
        ]);
    }

    public function table(Request $request)
    {
        $validated = $request->validate([
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:5', 'max:100'],
            'sort_field' => ['sometimes', 'string', 'in:name,email,created_at'],
            'sort_dir' => ['sometimes', 'string', 'in:asc,desc'],
            'status' => ['sometimes', 'string', 'in:all,ativo,inativo,tratamento,pausa_tratamento,abandono,concluido,transferencia'],
        ]);

        $page = $validated['page'] ?? 1;
        $perPage = $validated['per_page'] ?? 15;
        $sortField = $validated['sort_field'] ?? 'created_at';
        $sortDir = $validated['sort_dir'] ?? 'desc';
        $status = $validated['status'] ?? 'all';

        $paginator = $this->patientService->paginate(
            $page,
            $perPage,
            $sortField,
            $sortDir,
            $status,
            $request->user()?->university_id
        );

        $data = $this->patientService->formatPaginatedItems($paginator);

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ]);
    }

    public function show(Request $request, int $patient)
    {
        $universityId = $request->user()?->university_id;
        $patientData = $this->patientService->find($patient, $universityId);

        $students = Student::when($universityId, fn($q) => $q->where('university_id', $universityId))
            ->with('person:id,name')
            ->orderBy('id')
            ->get()
            ->map(fn($s) => ['id' => $s->id, 'name' => $s->person?->name ?? '—']);

        return Inertia::render('patients/PatientTab', [
            'patient' => $patientData,
            'students' => $students,
        ]);
    }

    public function store(StorePatientRequest $request)
    {
        $validated = $request->validated();

        $universityId = $request->user()?->university_id;
        if (! $universityId) {
            return response()->json(['message' => 'Universidade não encontrada'], 422);
        }

        $patient = $this->patientService->create($validated, $universityId);

        return response()->json([
            'message' => 'Paciente cadastrado com sucesso',
            'patient' => $this->patientService->formatForTab($patient),
        ]);
    }

    public function update(UpdatePatientRequest $request, int $patient)
    {
        $validated = $request->validated();

        $patientData = $this->patientService->update(
            $patient,
            $validated,
            $request->user()?->university_id
        );

        return response()->json([
            'message' => 'Dados atualizados com sucesso',
            'patient' => $patientData,
        ]);
    }

    public function updateStudent(UpdatePatientStudentRequest $request, int $patient)
    {
        $validated = $request->validated();

        $patientData = $this->patientService->updateStudent(
            $patient,
            isset($validated['student_id']) ? (int) $validated['student_id'] : null,
            $request->user()?->university_id
        );

        return response()->json([
            'message' => 'Estudante atualizado com sucesso',
            'patient' => $patientData,
        ]);
    }

    public function updateStudentData(UpdatePatientStudentDataRequest $request, int $patient)
    {
        $validated = $request->validated();

        $patientData = $this->patientService->updateStudentData(
            $patient,
            isset($validated['student_id']) ? (int) $validated['student_id'] : null,
            $validated['status'],
            $request->user()?->university_id
        );

        return response()->json([
            'message' => 'Estudante e status atualizados com sucesso',
            'patient' => $patientData,
        ]);
    }

    public function deactivate(Request $request, int $patient)
    {
        $this->patientService->deactivate($patient, $request->user()?->university_id);

        return response()->json([
            'message' => 'Paciente inativado com sucesso',
        ]);
    }

    public function activate(Request $request, int $patient)
    {
        $this->patientService->activate($patient, $request->user()?->university_id);

        return response()->json([
            'message' => 'Paciente ativado com sucesso',
        ]);
    }

    public function destroy(Request $request, int $patient)
    {
        $this->patientService->destroy($patient, $request->user()?->university_id);

        return response()->json([
            'message' => 'Paciente excluído com sucesso',
        ]);
    }
}
