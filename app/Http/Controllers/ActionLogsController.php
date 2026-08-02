<?php

namespace App\Http\Controllers;

use App\Constants\ActivityActions;
use App\Constants\ActivityModules;
use App\Data\ActionLogs\ActionLogTableFiltersData;
use App\Http\Requests\ActionLogTableRequest;
use App\Http\Resources\ActionLogResource;
use App\Models\Patient;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\ActionLogsService;
use App\Services\UserService;

class ActionLogsController extends Controller
{
    public function __construct(
        protected ActionLogsService $actionLogsService,
        protected UserService $userService,
    ) {}

    private function table(string $modelType, int $modelId, ?int $performedBy, ActionLogTableRequest $request,) 
    {
        return ActionLogResource::collection(
            $this->actionLogsService->paginate(
                $modelType,
                $modelId,
                $performedBy,
                ActionLogTableFiltersData::fromRequest($request),
            )
        );
    }

    public function userTable(User $user, ActionLogTableRequest $request)
    {
        return $this->table(
            User::class,
            $user->id,
            $user->id,
            $request,
        );
    }

    public function studentTable(Student $student, ActionLogTableRequest $request)
    {
        return $this->table(
            Student::class,
            $student->id,
            $student->user_id,
            $request,
        );
    }

    public function patientTable(Patient $patient, ActionLogTableRequest $request)
    {
        return $this->table(
            Patient::class,
            $patient->id,
            null,
            $request,
        );
    }

    public function filters()
    {
        return response()->json([
            'modules' => ActivityModules::all(),
            'actions' => ActivityActions::all(),
        ]);
    }
}
