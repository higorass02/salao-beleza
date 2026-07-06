<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\User;
use App\Notifications\ClientDeactivatedNotification;

class ClientService
{
    public function list(?string $query = null, int $perPage = 10)
    {
        return Client::orderByRaw('active DESC, name ASC')
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

    public function deactivate(Client $client): void
    {
        $client->update(['active' => false]);

        // Cancela todos os agendamentos futuros deste cliente
        $cancelled = Appointment::with(['service', 'employee'])
            ->where('client_id', $client->id)
            ->where('starts_at', '>', now())
            ->whereIn('status', ['scheduled'])
            ->get();

        if ($cancelled->isNotEmpty()) {
            Appointment::whereIn('id', $cancelled->pluck('id'))->update(['status' => 'canceled']);
        }

        // Prepara lista formatada para o e-mail
        $list = $cancelled->map(fn ($a) => [
            'starts_at' => $a->starts_at->format('d/m/Y H:i'),
            'service'   => $a->service?->name ?? '—',
            'employee'  => $a->employee?->name ?? '—',
        ]);

        // Envia e-mail apenas para os admins
        User::where('is_admin', true)->each(
            fn ($admin) => $admin->notify(new ClientDeactivatedNotification($client, $list))
        );
    }

    public function activate(Client $client): void
    {
        $client->update(['active' => true]);
    }

    public function findOrCreateByPhone(string $name, string $phone): Client
    {
        return Client::firstOrCreate(
            ['phone' => $phone],
            ['name' => $name, 'active' => true],
        );
    }

    public function findOrCreateByGoogle(array $googleData): Client
    {
        // Já tem google_id cadastrado
        $existing = Client::where('google_id', $googleData['google_id'])->first();
        if ($existing) {
            return $existing;
        }

        // Tem e-mail igual → vincula o google_id ao cliente existente
        if (! empty($googleData['email'])) {
            $byEmail = Client::where('email', $googleData['email'])->whereNull('google_id')->first();
            if ($byEmail) {
                $byEmail->update([
                    'google_id'    => $googleData['google_id'],
                    'google_avatar' => $googleData['google_avatar'] ?? null,
                ]);

                return $byEmail->fresh();
            }
        }

        // Cria novo cliente
        return Client::create([
            'name'         => $googleData['name'],
            'email'        => $googleData['email'] ?? null,
            'google_id'    => $googleData['google_id'],
            'google_avatar' => $googleData['google_avatar'] ?? null,
            'active'       => true,
        ]);
    }
}
