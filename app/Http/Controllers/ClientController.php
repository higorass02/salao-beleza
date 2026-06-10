<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use App\Services\ClientService;
use Inertia\Inertia;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request, ClientService $service)
    {
        return Inertia::render('Clients/Index', [
            'clients' => $service->list($request->q),
            'filters' => ['q' => $request->q ?? ''],
        ]);
    }

    public function create()
    {
        return Inertia::render('Clients/Create');
    }

    public function store(StoreClientRequest $request, ClientService $service)
    {
        $service->create($request->validated());

        if ($request->user()->isCollaborator()) {
            return redirect()->route('collaborator.appointments.create')
                ->with('success', 'Cliente cadastrado com sucesso.');
        }

        return redirect()->route('clients.index');
    }

    public function edit(Client $client)
    {
        return Inertia::render('Clients/Edit', [
            'client' => $client,
        ]);
    }

    public function update(UpdateClientRequest $request, Client $client, ClientService $service)
    {
        $service->update($client, $request->validated());

        return redirect()->route('clients.index');
    }

    public function destroy(Client $client, ClientService $service)
    {
        $service->deactivate($client);

        return redirect()->route('clients.index')
            ->with('success', 'Cliente desativado. Agendamentos futuros cancelados e admin notificado.');
    }

    public function activate(Client $client, ClientService $service)
    {
        $service->activate($client);

        return redirect()->route('clients.index')
            ->with('success', 'Cliente reativado com sucesso.');
    }

    public function search(Request $request)
    {
        return Client::query()
            ->where('active', true)
            ->when($request->q, fn ($query, $q) => $query->where(fn ($sub) =>
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('apelido', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%")
            ))
            ->limit(10)
            ->get(['id', 'name', 'apelido', 'phone']);
    }
}
