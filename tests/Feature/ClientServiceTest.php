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

    // ── findOrCreateByPhone ───────────────────────────────────────────────────

    public function test_find_or_create_by_phone_creates_new_client(): void
    {
        $client = $this->service->findOrCreateByPhone('Maria Silva', '11999990000');

        $this->assertDatabaseHas('clients', [
            'name'  => 'Maria Silva',
            'phone' => '11999990000',
        ]);
        $this->assertInstanceOf(Client::class, $client);
    }

    public function test_find_or_create_by_phone_returns_existing_client(): void
    {
        $existing = Client::factory()->create(['phone' => '11999990001', 'name' => 'Existente']);

        $client = $this->service->findOrCreateByPhone('Outro Nome', '11999990001');

        $this->assertSame($existing->id, $client->id);
        $this->assertEquals(1, Client::where('phone', '11999990001')->count());
    }

    // ── findOrCreateByGoogle ──────────────────────────────────────────────────

    public function test_find_or_create_by_google_creates_new_client(): void
    {
        $client = $this->service->findOrCreateByGoogle([
            'google_id'    => 'google-abc-123',
            'name'         => 'João Google',
            'email'        => 'joao@gmail.com',
            'google_avatar' => 'https://example.com/avatar.jpg',
        ]);

        $this->assertDatabaseHas('clients', [
            'google_id' => 'google-abc-123',
            'name'      => 'João Google',
            'email'     => 'joao@gmail.com',
        ]);
        $this->assertInstanceOf(Client::class, $client);
    }

    public function test_find_or_create_by_google_returns_existing_by_google_id(): void
    {
        $existing = Client::factory()->create(['google_id' => 'google-xyz', 'name' => 'Existente']);

        $client = $this->service->findOrCreateByGoogle([
            'google_id' => 'google-xyz',
            'name'      => 'Outro Nome',
            'email'     => 'outro@gmail.com',
        ]);

        $this->assertSame($existing->id, $client->id);
        $this->assertEquals(1, Client::where('google_id', 'google-xyz')->count());
    }

    public function test_find_or_create_by_google_links_existing_client_by_email(): void
    {
        $existing = Client::factory()->create(['email' => 'linked@gmail.com', 'google_id' => null]);

        $client = $this->service->findOrCreateByGoogle([
            'google_id' => 'google-new-id',
            'name'      => $existing->name,
            'email'     => 'linked@gmail.com',
        ]);

        $this->assertSame($existing->id, $client->id);
        $this->assertDatabaseHas('clients', ['id' => $existing->id, 'google_id' => 'google-new-id']);
    }
}
