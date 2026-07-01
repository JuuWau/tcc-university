<?php

namespace App\Http\Controllers;

use App\Http\Requests\ActivateStudentRequest;
use App\Http\Requests\DeactivateStudentRequest;
use App\Http\Requests\OptionsStudentRequest;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\StudentScheduleRequest;
use App\Http\Requests\UpdateStudentAcademicDataRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Http\Resources\StudentClinicResource;
use App\Http\Resources\StudentScheduleEventResource;
use App\Models\Student;
use App\Services\StudentService;
use App\Services\PeriodService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StudentsController extends Controller
{
    protected $studentService;
    protected $periodService;

    public function __construct(StudentService $studentService, PeriodService $periodService)
    {
        $this->studentService = $studentService;
        $this->periodService = $periodService;
    }

    public function index()
    {
        $universityId = request()->user()?->university_id;

        return Inertia::render('students/StudentsIndex', [
            'periods' => $this->periodService->all($universityId),
        ]);
    }

    public function table(Request $request)
    {
        $validated = $request->validate([
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:5', 'max:100'],
            'sort_field' => ['sometimes', 'string', 'in:person.name,registration,created_at'],
            'sort_dir' => ['sometimes', 'string', 'in:asc,desc'],
            'status' => ['sometimes', 'string', 'in:all,pending,active,inactive'],
        ]);

        $page = $validated['page'] ?? 1;
        $perPage = $validated['per_page'] ?? 10;
        $sortField = $validated['sort_field'] ?? 'created_at';
        $sortDir = $validated['sort_dir'] ?? 'desc';
        $status = $validated['status'] ?? 'all';

        $paginator = $this->studentService->paginate(
            $page,
            $perPage,
            $sortField,
            $sortDir,
            $status,
            $request->user()?->university_id
        );

        return response()->json([
            'data' => $paginator->items(),
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

    public function store(StoreStudentRequest $request)
    {
        $student = $this->studentService->create(
            $request->validated(),
            $request->user()->university_id
        );

        return response()->json([
            'message' => 'Estudante criado com sucesso',
            'student' => $student,
        ]);
    }

    public function deactivate(DeactivateStudentRequest $request, int $student)
    {
        $student = $this->studentService->deactivate(
            $student,
            auth()->id(),
            $request->reason,
            $request->note
        );

        return response()->json([
            'message' => 'Aluno desativado com sucesso',
            'student' => $student,
        ]);
    }

    public function activate(ActivateStudentRequest $request, int $student)
    {
        $student = $this->studentService->activate(
            $student,
            auth()->id(),
            $request->reason,
            $request->note
        );

        return response()->json([
            'message' => 'Aluno ativado com sucesso',
            'student' => $student,
        ]);
    }

    public function show(int $student)
    {
        $universityId = request()->user()?->university_id;
        $student = $this->studentService->find($student);
        
        return Inertia::render('students/StudentTab', [
            'student' => $student,
            'periods' => $this->periodService->all($universityId),
        ]);
    }

    public function update(UpdateStudentRequest $request, int $student)
    {
        $student = $this->studentService->update(
            $student,
            $request->validated()
        );

        return response()->json([
            'message' => 'Dados pessoais atualizados com sucesso',
            'student' => $student,
        ]);
    }

    public function updateAcademicData(UpdateStudentAcademicDataRequest $request, int $student)
    {
        $student = $this->studentService->updateAcademicData(
            $student,
            $request->validated()
        );

        return response()->json([
            'message' => 'Dados acadêmicos atualizados com sucesso',
            'student' => $student,
        ]);
    }

    public function destroy(int $student)
    {
        $this->studentService->destroy($student);

        return response()->json([
            'message' => 'Aluno excluído com sucesso',
        ]);
    }

    public function resendInvite(int $student)
    {
        $this->studentService->resendInvite($student);

        return response()->json([
            'message' => 'Convite reenviado com sucesso',
        ]);
    }

    public function options(OptionsStudentRequest $request)
    {
        $students = $this->studentService->options(
            $request->period_id,
            $request->user()->university_id
        );

        return response()->json($students);
    }

    public function availableClinics(int $student)
    {
        $clinics = $this->studentService->availableClinics($student);

        return StudentClinicResource::collection($clinics);
    }

    public function schedule(StudentScheduleRequest $request, int $student) 
    {
        $schedule = $this->studentService->schedule(
            $student,
            $request->integer('clinic_id'),
        );

        return response()->json([
            'open_days' => $schedule['open_days'],
            'events' => StudentScheduleEventResource::collection(
                $schedule['events'],
            ),
        ]);
    }
}
