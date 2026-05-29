<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $employees = [
            ['name' => 'Ana Silva',    'email' => 'ana@exemplo.com',    'phone' => '11999990000', 'role' => 'Cabeleireira', 'active' => true],
            ['name' => 'Marina Rocha', 'email' => 'marina@exemplo.com', 'phone' => '11999990001', 'role' => 'Manicure',     'active' => true],
            ['name' => 'Carlos Melo',  'email' => 'carlos@exemplo.com', 'phone' => '11999990004', 'role' => 'Barbeiro',     'active' => true],
        ];

        foreach ($employees as $data) {
            Employee::firstOrCreate(['email' => $data['email']], $data);
        }
    }
}
