<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Services\ServiceService;
use Tests\TestCase;

class ServiceServiceTest extends TestCase
{
    private ServiceService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = $this->app->make(ServiceService::class);
    }

    public function test_list_returns_all_services_ordered_by_name(): void
    {
        Service::factory()->create(['name' => 'Tintura']);
        Service::factory()->create(['name' => 'Corte']);
        Service::factory()->create(['name' => 'Manicure']);

        $services = $this->service->list();

        $this->assertCount(3, $services);
        $this->assertEquals('Corte', $services->first()->name);
        $this->assertEquals('Tintura', $services->last()->name);
    }

    public function test_create_persists_service_with_duration(): void
    {
        $service = $this->service->create([
            'name'             => 'Corte Feminino',
            'description'      => 'Corte e acabamento',
            'price'            => 120.00,
            'duration_minutes' => 60,
            'active'           => true,
        ]);

        $this->assertDatabaseHas('services', [
            'id'               => $service->id,
            'name'             => 'Corte Feminino',
            'duration_minutes' => 60,
        ]);
    }

    public function test_update_changes_duration_for_future_appointments(): void
    {
        $service = Service::factory()->create(['duration_minutes' => 30]);

        $updated = $this->service->update($service, ['duration_minutes' => 60]);

        $this->assertEquals(60, $updated->duration_minutes);
        $this->assertDatabaseHas('services', ['id' => $service->id, 'duration_minutes' => 60]);
    }

    public function test_update_can_deactivate_service(): void
    {
        $service = Service::factory()->create(['active' => true]);

        $updated = $this->service->update($service, ['active' => false]);

        $this->assertFalse($updated->active);
    }
}
