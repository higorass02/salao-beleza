<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\User;
use App\Notifications\WelcomeEmployeeNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EmployeeService
{
    public function list()
    {
        return Employee::with('user:id,employee_id,email')
            ->orderBy('name')
            ->get()
            ->map(fn ($e) => array_merge($e->toArray(), [
                'has_user' => $e->user !== null,
            ]));
    }

    public function create(array $data): Employee
    {
        $employee = Employee::create($data);
        $this->createUserForEmployee($employee);
        return $employee;
    }

    public function update(Employee $employee, array $data): Employee
    {
        $wasActive = (bool) $employee->active;
        $employee->update($data);

        // Se o funcionário foi desativado, invalida o token de sessão do usuário vinculado
        // para que ele seja desconectado no próximo request (mesmo que ainda esteja logado)
        if ($wasActive && isset($data['active']) && ! $data['active']) {
            $employee->user?->update(['remember_token' => null]);
        }

        return $employee;
    }

    public function createUserForEmployee(Employee $employee): User
    {
        $temporaryPassword = Str::password(12, symbols: false);

        $user = User::create([
            'name'                 => $employee->name,
            'email'                => $employee->email,
            'password'             => Hash::make($temporaryPassword),
            'is_admin'             => false,
            'employee_id'          => $employee->id,
            'must_change_password' => true,
        ]);

        $user->notify(new WelcomeEmployeeNotification($temporaryPassword));

        return $user;
    }
}
