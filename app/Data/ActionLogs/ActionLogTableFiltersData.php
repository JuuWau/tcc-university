<?php

namespace App\Data\ActionLogs;

use App\Http\Requests\ActionLogTableRequest;

class ActionLogTableFiltersData
{
    public function __construct(
        public int $page = 1,
        public int $perPage = 15,
        public ?string $search = null,
        public ?string $module = null,
        public ?string $action = null,
        public ?string $date = null,
        public ?string $type = 'all'
    ) {}

    public static function fromRequest(ActionLogTableRequest $request): self
    {
        return new self(
            $request->integer('page', 1),
            $request->integer('per_page', 15),
            $request->input('search'),
            $request->input('module'),
            $request->input('action'),
            $request->input('date'),
            $request->input('type', 'all'),
        );
    }
}