<?php

namespace App\Services;

use App\Constants\ActivityActions;
use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

class ActivityLogService
{
        private const IGNORED_FIELDS = [
                'id',
                'created_at',
                'updated_at',
                'deleted_at',
        ];

        public static function log(string $module, string $action, string $description, ?Model $model = null, ?array $changes = null): void
        {
                ActivityLog::create([
                        'user_id' => auth()->id(),
                        'module' => $module,
                        'action' => $action,
                        'description' => $description,
                        'model_type' => $model?->getMorphClass(),
                        'model_id' => $model?->getKey(),
                        'changes' => empty($changes) ? null : $changes,
                        'ip' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                ]);
        }

        public static function getChanges(Model $model, ?string $prefix = null, array $ignoredFields = []): array
        {
                $changes = [];

                $ignoredFields = array_merge(
                        self::IGNORED_FIELDS,
                        $ignoredFields,
                );

                foreach ($model->getDirty() as $field => $newValue) {
                        if (in_array($field, $ignoredFields, true)) {
                                continue;
                        }

                        $changes[self::formatField($field, $prefix)] = [
                                'old' => $model->getOriginal($field),
                                'new' => $newValue,
                        ];
                }

                return $changes;
        }

        public static function trackRelationChanges(array &$changes, string $relation, ?array $oldValues, ?array $newValues,): void
        {
                $oldValues ??= [];
                $newValues ??= [];

                sort($oldValues);
                sort($newValues);

                if ($oldValues === $newValues) {
                        return;
                }

                $changes[$relation] = [
                        'old' => $oldValues,
                        'new' => $newValues,
                ];
        }

        public static function getRelationValues(string $modelClass, array $ids, string|callable $display = 'name', ?string $orderColumn = null, ?callable $formatter = null,): array
        {
                if (empty($ids)) {
                        return [];
                }

                if (is_callable($display)) {
                        return $modelClass::query()
                                ->whereIn('id', $ids)
                                ->get()
                                ->map($display)
                                ->sort()
                                ->values()
                                ->toArray();
                }

                $orderColumn ??= $display;

                return $modelClass::query()
                        ->whereIn('id', $ids)
                        ->orderBy($orderColumn)
                        ->pluck($display)
                        ->toArray();
        }

        public static function getCreatedChanges(Model $model, ?string $prefix = null): array
        {
                $changes = [];

                foreach ($model->getAttributes() as $field => $value) {
                        if (in_array($field, self::IGNORED_FIELDS, true)) {
                                continue;
                        }

                        if ($value === null) {
                                continue;
                        }

                        $key = $prefix
                                ? "{$prefix}.{$field}"
                                : $field;

                        $changes[$key] = [
                                'old' => null,
                                'new' => $value,
                        ];
                }

                return $changes;
        }

        public static function getModelRelationValues(Model $model, string $relation, string $displayColumn = 'name'): array
        {
                return $model->{$relation}()
                        ->orderBy($displayColumn)
                        ->pluck($displayColumn)
                        ->toArray();
        }

        public static function replaceForeignKeyChange(array &$changes, string $foreignKey, string $field, ?array $oldValues, ?array $newValues,): void
        {
                unset($changes[$foreignKey]);

                self::trackRelationChanges(
                        $changes,
                        $field,
                        $oldValues,
                        $newValues,
                );
        }

        public static function trackBelongsToChange(array &$changes, string $foreignKey, string $relation, string $modelClass, ?int $oldId, ?int $newId, string|callable $display = 'name', ?string $orderColumn = null,): void
        {
                unset($changes[$foreignKey]);

                $oldValues = $oldId
                        ? self::getRelationValues(
                                $modelClass,
                                [$oldId],
                                $display,
                                $orderColumn,
                        )
                        : [];

                $newValues = $newId
                        ? self::getRelationValues(
                                $modelClass,
                                [$newId],
                                $display,
                                $orderColumn,
                        )
                        : [];

                self::trackRelationChanges(
                        $changes,
                        $relation,
                        $oldValues,
                        $newValues,
                );
        }

        private static function formatField(string $field, ?string $prefix): string
        {
                return $prefix
                        ? "{$prefix}.{$field}"
                        : $field;
        }

        private static function write(string $module, string $action, string $description, ?Model $model = null, ?array $changes = null,): void
        {
                self::log($module, $action, $description, $model, $changes);
        }

        public static function created(string $module, string $description, ?Model $model = null, ?array $changes = null): void
        {
                self::write($module, ActivityActions::CREATE, $description, $model, $changes);
        }

        public static function updated(string $module, string $description, ?Model $model = null, ?array $changes = null): void
        {
                self::write($module, ActivityActions::UPDATE, $description, $model, $changes);
        }

        public static function deleted(string $module, string $description, ?Model $model = null, ?array $changes = null): void
        {
                self::write($module, ActivityActions::DELETE, $description, $model, $changes);
        }
}
