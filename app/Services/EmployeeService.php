<?php

namespace App\Services;

use App\Models\Employee;

class EmployeeService
{
    public function list()
    {
        return Employee::orderBy('name')->get();
    }

    public function create(array $data)
    {
        return Employee::create($data);
    }

    public function update(Employee $employee, array $data)
    {
        $employee->update($data);

        return $employee;
    }
}
