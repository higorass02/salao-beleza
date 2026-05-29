<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $clients = [
            ['name' => 'Juliana Souza', 'email' => 'juliana@exemplo.com', 'phone' => '11999990002', 'notes' => 'Prefere esmalte neutro.'],
            ['name' => 'Rosana Lima',   'email' => 'rosana@exemplo.com',  'phone' => '11999990003', 'notes' => 'Alergia a perfume forte.'],
            ['name' => 'Fernanda Dias', 'email' => 'fernanda@exemplo.com','phone' => '11999990005', 'notes' => null],
        ];

        foreach ($clients as $data) {
            Client::firstOrCreate(['email' => $data['email']], $data);
        }
    }
}
