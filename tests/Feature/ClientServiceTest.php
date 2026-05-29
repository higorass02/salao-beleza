<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Services\ClientService;
use Tests\TestCase;

class ClientServiceTest extends TestCase
{
    private ClientService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = $this->app->make(ClientService::class);
    }

    public function test_list_returns_all_clients_ordered_by_name(): void
    {
        Client::factory()->create(['name' => 'Zara']);
        Client::factory()->create(['name' => 'Ana']);
        Client::factory()->create(['name' => 'Maria']);

        $clients = $this->service->list();

        $this->assertCount(3, $clients);
        $this->assertEquals('Ana', $clients->first()->name);
        $this->assertEquals('Zara', $clients->last()->name);
    }

    public function test_create_persists_client(): void
    {
        $client = $this->service->create([
            'name'  => 'Juliana Souza',
            'email' => 'juliana@example.com',
            'phone' => '11999990001',
        ]);

        $this->assertDatabaseHas('clients', [
            'id'    => $client->id,
            'name'  => 'Juliana Souza',
            'email' => 'juliana@example.com',
        ]);
    }

    public function test_create_allows_optional_fields_null(): void
    {
        $client = $this->service->create(['name' => 'Sem Email']);

        $this->assertDatabaseHas('clients', ['id' => $client->id, 'email' => null]);
    }

    public function test_update_changes_client_data(): void
    {
        $client = Client::factory()->create(['name' => 'Nome Antigo']);

        $this->service->update($client, ['name' => 'Nome Novo']);

        $this->assertDatabaseHas('clients', ['id' => $client->id, 'name' => 'Nome Novo']);
    }

    public function test_update_returns_updated_model(): void
    {
        $client  = Client::factory()->create();
        $updated = $this->service->update($client, ['name' => 'Atualizado']);

        $this->assertEquals('Atualizado', $updated->name);
    }
}
