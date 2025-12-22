<?php

namespace App\Services;

use App\Models\Employee;
use Illuminate\Support\Facades\DB;

class EmployeeService
{
        public function store(array $data): Employee
        {
                return DB::transaction(function () use ($data) {
                        return Employee::create($data);
                });
        }

        public function update(Employee $employee, array $data): Employee
        {
                return DB::transaction(function () use ($data, $employee) {
                        $employee->update($data);
                        return $employee;
                });
        }

        public function delete(Employee $employee): void
        {
                $employee->delete();
        }
}
