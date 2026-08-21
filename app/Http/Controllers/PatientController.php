<?php

namespace App\Http\Controllers;

use App\Data\Patients\PatientClinicsTableFiltersData;
use App\Data\Patients\PatientTableFiltersData;
use App\Http\Requests\AddPatientToWaitingListRequest;
use App\Http\Requests\EnrollPatientClinicRequest;
use App\Http\Requests\PatientClinicsTableRequest;
use App\Http\Requests\PatientScheduleAvailableDaysRequest;
use App\Http\Requests\PatientScheduleAvailableTimesRequest;
use App\Http\Requests\PatientScheduleStudentsRequest;
use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\TablePatientRequest;
use App\Http\Requests\UpdatePatientStudentDataRequest;
use App\Http\Requests\UpdatePatientStudentRequest;
use App\Http\Requests\UpdatePatientRequest;
use App\Http\Resources\PatientClinicResource;
use App\Http\Resources\PeriodResource;
use App\Http\Resources\PatientCollection;
use App\Http\Resources\PatientOptionResource;
use App\Http\Resources\PatientResource;
use App\Http\Resources\PatientScheduleResource;
use App\Http\Resources\StudentOptionResource;
use App\Http\Resources\ClinicResource;
use App\Models\PatientImport;
use App\Imports\PatientsImport;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\Student;
use App\Services\PatientService;
use App\Services\StudentService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class PatientController extends Controller
{
    public function __construct(
        protected PatientService $patientService,
        protected StudentService $studentService
    ) {}

    public function index(Request $request)
    {
        $universityId = $request->user()?->university_id;
        $students = $this->studentService->getOptionsByUniversity($universityId);

        return Inertia::render('patients/PatientsIndex', [
            'students' => StudentOptionResource::collection($students)->resolve(),
        ]);
    }

    public function table(TablePatientRequest $request)
    {
        $patients = $this->patientService->paginate(
            PatientTableFiltersData::fromRequest($request),
            $request->user()
        );

        return PatientResource::collection($patients);
    }

    public function show(Request $request, int $patient)
    {
        $universityId = $request->user()?->university_id;
        $patient = $this->patientService->find($patient, $universityId);
        $students = $this->studentService->getOptionsByUniversity($universityId);

        return Inertia::render('patients/PatientTab', [
            'patient' => PatientResource::make($patient)->resolve(),
            'students' => StudentOptionResource::collection($students)->resolve(),
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

        return PatientResource::make($patient)->additional([
            'message' => 'Paciente cadastrado com sucesso',
        ]);
    }

    public function update(UpdatePatientRequest $request, int $patient)
    {
        $validated = $request->validated();

        $patient = $this->patientService->update(
            $patient,
            $validated,
            $request->user()?->university_id
        );

        return PatientResource::make($patient)->additional([
            'message' => 'Dados atualizados com sucesso',
        ]);
    }

    public function updateStudent(UpdatePatientStudentRequest $request, int $patient)
    {
        $validated = $request->validated();

        $patient = $this->patientService->updateStudent(
            $patient,
            isset($validated['student_id']) ? (int) $validated['student_id'] : null,
            $request->user()?->university_id
        );

        return PatientResource::make($patient)->additional([
            'message' => 'Estudante atualizado com sucesso',
        ]);
    }

    public function updateStudentData(UpdatePatientStudentDataRequest $request, int $patient)
    {
        $validated = $request->validated();

        $patient = $this->patientService->updateStudentData(
            $patient,
            $validated['student_ids'] ?? [],
            $validated['status'],
            $validated['code'],
            $request->user()?->university_id
        );

        return PatientResource::make($patient)->additional([
            'message' => 'Estudantes e status atualizados com sucesso',
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

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        $path = $request->file('file')->store('imports');

        $import = PatientImport::create([
            'user_id' => auth()->id(),
            'file' => $path,
            'status' => 'processing',
        ]);

        $fullPath = storage_path('app/private/' . $path);

        $reader = IOFactory::createReaderForFile($fullPath);

        $spreadsheet = $reader->load($fullPath);

        $sheetNames = $spreadsheet->getSheetNames();

        $spreadsheet->disconnectWorksheets();

        unset($spreadsheet);

        Excel::queueImport(
            new PatientsImport(
                $import,
                $request->user()?->university_id,
                $sheetNames,
            ),
            $fullPath
        );

        return response()->json([
            'message' => 'Importação iniciada',
            'import_id' => $import->id,
        ]);
    }

    public function importStatus(PatientImport $import)
    {
        return response()->json([
            'status' => $import->status,
            'imported' => $import->imported,
            'failed' => $import->failed,
            'errors' => $import->errors ?? [],
        ]);
    }

    public function availablePatients(Clinic $clinic)
    {
        return PatientOptionResource::collection(
            $this->patientService->availableForClinic($clinic)
        )->resolve();
    }

    public function clinicsTable(PatientClinicsTableRequest $request, Patient $patient)
    {
        $clinics = $this->patientService->paginateClinics(
            $patient,
            PatientClinicsTableFiltersData::fromRequest(
                $request
            )
        );

        return PatientClinicResource::collection(
            $clinics
        );
    }

    public function removeEnrollment(Patient $patient, Clinic $clinic): JsonResponse
    {
        try {
            $this->patientService->removeEnrollment(
                $patient,
                $clinic
            );

            return response()->json([
                'message' => 'Inscrição removida com sucesso.',
            ]);
        } catch (\Throwable $exception) {
            report($exception);
            return response()->json([
                'message' => 'Erro ao remover inscrição.',
            ], 500);
        }
    }

    public function enrollClinic(EnrollPatientClinicRequest $request, Clinic $clinic)
    {
        try {
            $patientClinic = $this->patientService->enrollClinic(
                $clinic,
                $request->integer('patient_id')
            );

            return response()->json([
                'message' => 'Paciente inscrito com sucesso.',
                'data' => new PatientClinicResource($patientClinic),
            ]);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }
    }

    public function addToWaitingList(Clinic $clinic, AddPatientToWaitingListRequest $request)
    {
        try {
            $this->patientService->addToWaitingList(
                $clinic,
                $request->validated()
            );

            return response()->json([
                'message' => 'Paciente adicionado à lista de espera.',
            ]);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }
    }

    public function availableClinics(Patient $patient)
    {
        return response()->json(
            $this->patientService->availableClinics($patient)
        );
    }

    public function schedules(Patient $patient)
    {
        $schedules = $this->patientService->list($patient);

        return response()->json([
            'upcoming' => PatientScheduleResource::collection(
                $schedules['upcoming']
            ),
            'completed' => PatientScheduleResource::collection(
                $schedules['completed']
            ),
        ]);
    }

    public function getEnrolledClinics(Patient $patient)
    {
        $clinics = $this->patientService->getEnrolledClinics($patient);

        return ClinicResource::collection($clinics);
    }

    public function getClinicPeriods(Clinic $clinic)
    {
        return PeriodResource::collection(
            $this->patientService->getClinicPeriods($clinic)
        );
    }

    public function getClinicStudents(Patient $patient, PatientScheduleStudentsRequest $request)
    {
        return StudentOptionResource::collection(
            $this->patientService->getClinicStudents($patient, $request->validated())
        );
    }

    public function getAvailableDays(Patient $patient, PatientScheduleAvailableDaysRequest $request) {
        return response()->json(
            $this->patientService->getAvailableDays($patient, $request->validated())
        );
    }

    public function getAvailableTimes(Patient $patient, PatientScheduleAvailableTimesRequest $request) 
    {
        return response()->json(
            $this->patientService->getAvailableTimes($patient, $request->validated())
        );
    }
}
