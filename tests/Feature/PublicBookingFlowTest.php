<?php

namespace Tests\Feature;

use App\Http\Controllers\PublicBooking\BookingController;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Employee;
use App\Models\Service;
use App\Models\Setting;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Tests\TestCase;

/**
 * Testa o fluxo público de agendamento (sem autenticação).
 * Cobre: listagem de serviços, funcionários, slots disponíveis,
 * criação de agendamento (guest e google), validações e bloqueios.
 */
class PublicBookingFlowTest extends TestCase
{
    protected function defineRoutes($router): void
    {
        $router->get('/booking', [BookingController::class, 'servicesIndex'])
               ->name('booking.services');

        $router->get('/booking/service/{service}/employees', [BookingController::class, 'employeesByService'])
               ->name('booking.employees');

        $router->get('/booking/slots', [BookingController::class, 'availableSlots'])
               ->name('booking.slots');

        $router->get('/booking/confirm', [BookingController::class, 'showConfirm'])
               ->name('booking.confirm');

        $router->post('/booking', [BookingController::class, 'store'])
               ->name('booking.store');

        $router->get('/booking/confirmed/{appointment}', [BookingController::class, 'confirmed'])
               ->name('booking.confirmed');
    }

    protected function resolveApplicationExceptionHandler($app): void
    {
        $app->bind(ExceptionHandler::class, \App\Exceptions\Handler::class);
    }

    protected function setUp(): void
    {
        parent::setUp();
        Setting::set('business_hours_start', '09:00');
        Setting::set('business_hours_end', '18:00');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function makeService(array $attrs = []): Service
    {
        return Service::factory()->create(array_merge(['active' => true, 'duration_minutes' => 60], $attrs));
    }

    private function makeEmployee(array $attrs = []): Employee
    {
        return Employee::factory()->create(array_merge(['active' => true], $attrs));
    }

    private function guestPayload(array $overrides = []): array
    {
        $service  = $this->makeService();
        $employee = $this->makeEmployee();

        return array_merge([
            'service_id'  => $service->id,
            'employee_id' => $employee->id,
            'starts_at'   => '2099-12-01 09:00:00',
            'guest_name'  => 'Maria Silva',
            'guest_phone' => '11999990000',
        ], $overrides);
    }

    // X-Inertia faz o Inertia retornar JSON sem renderizar a view Blade (sem Vite)
    private function inertiaGet(string $url): \Illuminate\Testing\TestResponse
    {
        return $this->get($url, ['X-Inertia' => 'true', 'X-Inertia-Version' => '1']);
    }

    // ── GET /booking — listagem de serviços ───────────────────────────────────

    public function test_services_index_returns_active_services(): void
    {
        Service::factory()->create(['name' => 'Corte', 'active' => true]);
        Service::factory()->create(['name' => 'Inativo', 'active' => false]);

        $this->inertiaGet('/booking')
             ->assertOk()
             ->assertJson(['component' => 'Booking/Step1Service'])
             ->assertJsonCount(1, 'props.services')
             ->assertJsonPath('props.services.0.name', 'Corte');
    }

    // ── GET /booking/service/{id}/employees ───────────────────────────────────

    public function test_employees_by_service_returns_active_employees(): void
    {
        $service  = $this->makeService();
        $this->makeEmployee(['name' => 'Ana']);
        Employee::factory()->create(['name' => 'Inativo', 'active' => false]);

        $this->inertiaGet("/booking/service/{$service->id}/employees")
             ->assertOk()
             ->assertJson(['component' => 'Booking/Step2Employee'])
             ->assertJsonCount(1, 'props.employees')
             ->assertJsonPath('props.employees.0.name', 'Ana');
    }

    // ── GET /booking/slots ────────────────────────────────────────────────────

    public function test_available_slots_returns_json_array(): void
    {
        $service  = $this->makeService(['duration_minutes' => 60]);
        $employee = $this->makeEmployee();

        $response = $this->getJson("/booking/slots?employee_id={$employee->id}&service_id={$service->id}&date=2099-12-01");

        $response->assertOk();
        $response->assertJsonIsArray();
        $this->assertNotEmpty($response->json());
        $this->assertContains('09:00', $response->json());
    }

    public function test_slots_excludes_booked_times(): void
    {
        $service  = $this->makeService(['duration_minutes' => 60]);
        $employee = $this->makeEmployee();

        Appointment::factory()->create([
            'employee_id' => $employee->id,
            'starts_at'   => '2099-12-01 09:00:00',
            'ends_at'     => '2099-12-01 10:00:00',
            'status'      => 'scheduled',
        ]);

        $response = $this->getJson("/booking/slots?employee_id={$employee->id}&service_id={$service->id}&date=2099-12-01");

        $this->assertNotContains('09:00', $response->json());
    }

    public function test_slots_excludes_blocked_times(): void
    {
        $service  = $this->makeService(['duration_minutes' => 60]);
        $employee = $this->makeEmployee();

        Appointment::factory()->create([
            'employee_id' => $employee->id,
            'client_id'   => null,
            'service_id'  => null,
            'starts_at'   => '2099-12-01 09:00:00',
            'ends_at'     => '2099-12-01 11:00:00',
            'status'      => 'blocked',
        ]);

        $response = $this->getJson("/booking/slots?employee_id={$employee->id}&service_id={$service->id}&date=2099-12-01");

        $this->assertNotContains('09:00', $response->json());
        $this->assertNotContains('09:30', $response->json());
        $this->assertNotContains('10:00', $response->json());
        $this->assertNotContains('10:30', $response->json());
    }

    // ── POST /booking (guest) ─────────────────────────────────────────────────

    public function test_guest_booking_creates_client_and_appointment(): void
    {
        $payload = $this->guestPayload();

        $this->post('/booking', $payload)
             ->assertRedirect();

        $this->assertDatabaseHas('clients', [
            'name'  => 'Maria Silva',
            'phone' => '11999990000',
        ]);

        $client = Client::where('phone', '11999990000')->first();

        $this->assertDatabaseHas('appointments', [
            'client_id'   => $client->id,
            'employee_id' => $payload['employee_id'],
            'service_id'  => $payload['service_id'],
            'starts_at'   => '2099-12-01 09:00:00',
            'status'      => 'scheduled',
        ]);
    }

    public function test_guest_booking_reuses_existing_client_by_phone(): void
    {
        $existing = Client::factory()->create(['phone' => '11999990000', 'name' => 'Existente']);
        $payload  = $this->guestPayload(['guest_phone' => '11999990000']);

        $this->post('/booking', $payload);

        $this->assertEquals(1, Client::where('phone', '11999990000')->count());
        $this->assertDatabaseHas('appointments', ['client_id' => $existing->id]);
    }

    // ── POST /booking (google) ────────────────────────────────────────────────

    public function test_google_booking_creates_client_with_google_id(): void
    {
        $service  = $this->makeService();
        $employee = $this->makeEmployee();

        $this->post('/booking', [
            'service_id'   => $service->id,
            'employee_id'  => $employee->id,
            'starts_at'    => '2099-12-01 09:00:00',
            'google_id'    => 'google-uid-123',
            'google_name'  => 'João Google',
            'google_email' => 'joao@gmail.com',
        ])->assertRedirect();

        $this->assertDatabaseHas('clients', ['google_id' => 'google-uid-123']);
        $client = Client::where('google_id', 'google-uid-123')->first();
        $this->assertDatabaseHas('appointments', ['client_id' => $client->id, 'status' => 'scheduled']);
    }

    // ── Validações ────────────────────────────────────────────────────────────

    public function test_booking_fails_without_guest_name_and_no_google_id(): void
    {
        $service  = $this->makeService();
        $employee = $this->makeEmployee();

        $this->from('/booking/confirm')
             ->post('/booking', [
                 'service_id'  => $service->id,
                 'employee_id' => $employee->id,
                 'starts_at'   => '2099-12-01 09:00:00',
                 // sem guest_name/phone e sem google_id
             ])
             ->assertSessionHasErrors(['guest_name', 'guest_phone']);
    }

    public function test_booking_fails_with_inactive_service(): void
    {
        $inactiveService = Service::factory()->create(['active' => false]);
        $payload         = $this->guestPayload(['service_id' => $inactiveService->id]);

        $this->from('/booking/confirm')
             ->post('/booking', $payload)
             ->assertSessionHasErrors(['service_id']);
    }

    public function test_booking_fails_with_inactive_employee(): void
    {
        $inactiveEmployee = Employee::factory()->create(['active' => false]);
        $payload          = $this->guestPayload(['employee_id' => $inactiveEmployee->id]);

        $this->from('/booking/confirm')
             ->post('/booking', $payload)
             ->assertSessionHasErrors(['employee_id']);
    }

    public function test_booking_fails_when_slot_unavailable(): void
    {
        $payload = $this->guestPayload();

        Appointment::factory()->create([
            'employee_id' => $payload['employee_id'],
            'starts_at'   => '2099-12-01 09:00:00',
            'ends_at'     => '2099-12-01 10:00:00',
            'status'      => 'scheduled',
        ]);

        $this->from('/booking/confirm')
             ->post('/booking', $payload)
             ->assertSessionHasErrors(['starts_at']);
    }

    public function test_booking_fails_when_slot_is_blocked(): void
    {
        $payload = $this->guestPayload();

        Appointment::factory()->create([
            'employee_id' => $payload['employee_id'],
            'client_id'   => null,
            'service_id'  => null,
            'starts_at'   => '2099-12-01 09:00:00',
            'ends_at'     => '2099-12-01 18:00:00',
            'status'      => 'blocked',
        ]);

        $this->from('/booking/confirm')
             ->post('/booking', $payload)
             ->assertSessionHasErrors(['starts_at']);
    }

    // ── GET /booking/confirmed/{appointment} ──────────────────────────────────

    public function test_confirmed_page_renders_for_valid_appointment(): void
    {
        $appointment = Appointment::factory()->create(['status' => 'scheduled']);

        $this->inertiaGet("/booking/confirmed/{$appointment->id}")
             ->assertOk()
             ->assertJson(['component' => 'Booking/Confirmed']);
    }
}
