<?php

namespace App\Services;

use App\Models\Client;

class ClientService
{
    public function list(?string $query = null, int $perPage = 20)
    {
        return Client::orderBy('name')
            ->when($query, fn ($q) => $q->where(fn ($sub) =>
                $sub->where('name', 'like', "%{$query}%")
                    ->orWhere('apelido', 'like', "%{$query}%")
                    ->orWhere('phone', 'like', "%{$query}%")
            ))
            ->paginate($perPage)
            ->withQueryString();
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
