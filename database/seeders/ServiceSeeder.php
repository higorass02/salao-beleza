<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            ['name' => 'Corte Feminino',   'description' => 'Corte e acabamento feminino.',      'price' => 120.00, 'duration_minutes' => 60, 'active' => true],
            ['name' => 'Corte Masculino',   'description' => 'Corte social ou degradê.',           'price' =>  60.00, 'duration_minutes' => 30, 'active' => true],
            ['name' => 'Manicure Básica',   'description' => 'Manicure com esmaltação simples.',   'price' =>  70.00, 'duration_minutes' => 45, 'active' => true],
            ['name' => 'Pedicure Básica',   'description' => 'Pedicure com esmaltação simples.',   'price' =>  80.00, 'duration_minutes' => 50, 'active' => true],
            ['name' => 'Escova Progressiva','description' => 'Alisamento com escova progressiva.', 'price' => 250.00, 'duration_minutes' => 120,'active' => true],
        ];

        foreach ($services as $data) {
            Service::firstOrCreate(['name' => $data['name']], $data);
        }
    }
}
