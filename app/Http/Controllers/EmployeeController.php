<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Employee;
use App\Services\EmployeeService;
use Inertia\Inertia;

class EmployeeController extends Controller
{
    public function index(EmployeeService $service)
    {
        return Inertia::render('Employees/Index', [
            'employees' => $service->list(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Employees/Create');
    }

    public function store(StoreEmployeeRequest $request, EmployeeService $service)
    {
        $service->create($request->validated());

        return redirect()->route('employees.index');
    }

    public function edit(Employee $employee)
    {
        return Inertia::render('Employees/Edit', [
            'employee' => $employee,
        ]);
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee, EmployeeService $service)
    {
        $service->update($employee, $request->validated());

        return redirect()->route('employees.index');
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();

        return redirect()->route('employees.index');
    }
}
