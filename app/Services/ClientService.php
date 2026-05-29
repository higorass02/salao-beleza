<?php

namespace App\Services;

use App\Models\Client;

class ClientService
{
    public function list()
    {
        return Client::orderBy('name')->get();
    }

    public function create(array $data)
    {
        return Client::create($data);
    }

    public function update(Client $client, array $data)
    {
        $client->update($data);

        return $client;
    }
}
