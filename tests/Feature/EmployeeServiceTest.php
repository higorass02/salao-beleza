<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Services\EmployeeService;
use Tests\TestCase;

class EmployeeServiceTest extends TestCase
{
    private EmployeeService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = $this->app->make(EmployeeService::class);
    }

    public function test_list_returns_all_employees_ordered_by_name(): void
    {
        Employee::factory()->create(['name' => 'Zé']);
        Employee::factory()->create(['name' => 'Ana']);
        Employee::factory()->create(['name' => 'Maria']);

        $employees = $this->service->list();

        $this->assertCount(3, $employees);
        $this->assertEquals('Ana', $employees->first()->name);
        $this->assertEquals('Zé', $employees->last()->name);
    }

    public function test_create_persists_employee(): void
    {
        $employee = $this->service->create([
            'name'   => 'Carlos Barber',
            'email'  => 'carlos@salao.com',
            'phone'  => '11999990002',
            'role'   => 'Barbeiro',
            'active' => true,
        ]);

        $this->assertDatabaseHas('employees', [
            'id'     => $employee->id,
            'name'   => 'Carlos Barber',
            'active' => 1,
        ]);
    }

    public function test_update_changes_employee_data(): void
    {
        $employee = Employee::factory()->create(['role' => 'Cabeleireiro']);

        $this->service->update($employee, ['role' => 'Barbeiro']);

        $this->assertDatabaseHas('employees', ['id' => $employee->id, 'role' => 'Barbeiro']);
    }

    public function test_update_can_deactivate_employee(): void
    {
        $employee = Employee::factory()->create(['active' => true]);

        $updated = $this->service->update($employee, ['active' => false]);

        $this->assertFalse($updated->active);
        $this->assertDatabaseHas('employees', ['id' => $employee->id, 'active' => 0]);
    }
}
