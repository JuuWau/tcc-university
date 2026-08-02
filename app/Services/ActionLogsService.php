<?php

namespace App\Services;

use App\Data\ActionLogs\ActionLogTableFiltersData;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ActionLogsService
{
        public function paginate(string $modelType, int $modelId, ?int $performedBy, ActionLogTableFiltersData $filters,): LengthAwarePaginator 
        {
                $query = ActivityLog::query()->with('user.person');

                match ($filters->type) {
                        'performed' => $query->when(
                                $performedBy,
                                fn($q) => $q->where('user_id', $performedBy)
                        ),

                        'received' => $query
                                ->where('model_type', $modelType)
                                ->where('model_id', $modelId),

                        default => $query->where(function ($q) use (
                                $performedBy,
                                $modelType,
                                $modelId
                        ) {
                                if ($performedBy) {
                                        $q->where('user_id', $performedBy);
                                }

                                $q->orWhere(function ($q) use ($modelType, $modelId) {
                                        $q->where('model_type', $modelType)
                                                ->where('model_id', $modelId);
                                });
                        }),
                };

                $query->when($filters->search, function ($query) use ($filters) {
                        $query->where(function ($q) use ($filters) {
                                $q->where('description', 'ilike', "%{$filters->search}%")
                                        ->orWhere('module', 'ilike', "%{$filters->search}%")
                                        ->orWhereHas('user.person', function ($q) use ($filters) {
                                                $q->where('name', 'ilike', "%{$filters->search}%");
                                        });
                        });
                });

                $query->when(
                        $filters->module,
                        fn($query) => $query->where('module', $filters->module)
                );

                $query->when(
                        $filters->action,
                        fn($query) => $query->where('action', $filters->action)
                );

                $query->when(
                        $filters->date,
                        fn($query) => $query->whereDate('created_at', $filters->date)
                );

                return $query
                        ->latest()
                        ->paginate(
                                $filters->perPage,
                                ['*'],
                                'page',
                                $filters->page,
                        );
        }
}
