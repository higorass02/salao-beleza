<?php

namespace Tests\Feature;

use App\Http\Controllers\AppointmentController;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Employee;
use App\Models\Service;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Tests\TestCase;

/**
 * Testes E2E do fluxo de agendamento via HTTP (sem browser).
 * Cobre: validação do FormRequest, regras de negócio RN-01/04/05 e
 * o tratamento correto de erros pela exception handler.
 *
 * Middleware de autenticação é desabilitado — o foco é a lógica de negócio,
 * não o fluxo de login. Autenticação é testada via testes de browser/smoke
 * quando o ambiente Docker está em execução.
 */
class AppointmentBookingFlowTest extends TestCase
{
    protected function defineRoutes($router): void
    {
        $router->post('/appointments', [AppointmentController::class, 'store'])
               ->name('appointments.store');

        $router->get('/dashboard', fn () => response()->noContent())
               ->name('dashboard');

        $router->get('/calendar', fn () => response()->noContent())
               ->name('calendar');
    }

    protected function resolveApplicationExceptionHandler($app): void
    {
        $app->bind(ExceptionHandler::class, \App\Exceptions\Handler::class);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function validPayload(array $overrides = []): array
    {
        $client   = Client::factory()->create();
        $employee = Employee::factory()->create(['active' => true]);
        $service  = Service::factory()->create(['active' => true, 'duration_minutes' => 60]);

        return array_merge([
            'client_id'   => $client->id,
            'employee_id' => $employee->id,
            'service_id'  => $service->id,
            'starts_at'   => '2026-06-15 10:00:00',
        ], $overrides);
    }

    // ── Fluxo feliz ───────────────────────────────────────────────────────────

    public function test_booking_creates_appointment_and_redirects_to_dashboard(): void
    {
        $payload = $this->validPayload();

        $this->post('/appointments', $payload)
             ->assertRedirect('/dashboard');

        $this->assertDatabaseHas('appointments', [
            'client_id'   => $payload['client_id'],
            'employee_id' => $payload['employee_id'],
            'service_id'  => $payload['service_id'],
            'starts_at'   => '2026-06-15 10:00:00',
            'ends_at'     => '2026-06-15 11:00:00',
            'status'      => 'scheduled',
        ]);
    }

    public function test_booking_calculates_ends_at_from_service_duration(): void
    {
        $service = Service::factory()->create(['active' => true, 'duration_minutes' => 90]);
        $payload = $this->validPayload(['service_id' => $service->id]);

        $this->post('/appointments', $payload);

        $this->assertDatabaseHas('appointments', [
            'starts_at' => '2026-06-15 10:00:00',
            'ends_at'   => '2026-06-15 11:30:00',
        ]);
    }

    // ── RN-01: conflito de horário ────────────────────────────────────────────

    public function test_booking_fails_with_session_error_on_scheduling_conflict(): void
    {
        $payload = $this->validPayload();

        // Cria agendamento existente que vai colidir
        Appointment::factory()->create([
            'employee_id' => $payload['employee_id'],
            'service_id'  => $payload['service_id'],
            'starts_at'   => '2026-06-15 10:00:00',
            'ends_at'     => '2026-06-15 11:00:00',
            'status'      => 'scheduled',
        ]);

        // Tentativa de agendar no mesmo slot → conflito
        $this->from('/calendar')
             ->post('/appointments', array_merge($payload, ['starts_at' => '2026-06-15 10:30:00']))
             ->assertRedirect('/calendar')
             ->assertSessionHasErrors(['starts_at']);
    }

    public function test_conflicting_appointment_is_not_saved(): void
    {
        $payload = $this->validPayload();

        Appointment::factory()->create([
            'employee_id' => $payload['employee_id'],
            'service_id'  => $payload['service_id'],
            'starts_at'   => '2026-06-15 10:00:00',
            'ends_at'     => '2026-06-15 11:00:00',
            'status'      => 'scheduled',
        ]);

        $countBefore = Appointment::count();

        $this->from('/calendar')
             ->post('/appointments', array_merge($payload, ['starts_at' => '2026-06-15 10:30:00']));

        $this->assertEquals($countBefore, Appointment::count());
    }

    // ── RN-04: funcionário/serviço deve estar ativo ───────────────────────────

    public function test_booking_fails_with_inactive_employee(): void
    {
        $inactiveEmployee = Employee::factory()->create(['active' => false]);
        $payload          = $this->validPayload(['employee_id' => $inactiveEmployee->id]);

        $this->from('/calendar')
             ->post('/appointments', $payload)
             ->assertSessionHasErrors(['employee_id']);
    }

    public function test_booking_fails_with_inactive_service(): void
    {
        $inactiveService = Service::factory()->create(['active' => false]);
        $payload         = $this->validPayload(['service_id' => $inactiveService->id]);

        $this->from('/calendar')
             ->post('/appointments', $payload)
             ->assertSessionHasErrors(['service_id']);
    }

    // ── RN-05: não agendar no passado ────────────────────────────────────────

    public function test_booking_fails_for_past_date(): void
    {
        $payload = $this->validPayload(['starts_at' => '2020-01-01 10:00:00']);

        $this->from('/calendar')
             ->post('/appointments', $payload)
             ->assertSessionHasErrors(['starts_at']);
    }

    // ── RN-03: cliente deve existir ───────────────────────────────────────────

    public function test_booking_fails_when_client_does_not_exist(): void
    {
        $payload = $this->validPayload(['client_id' => 99999]);

        $this->from('/calendar')
             ->post('/appointments', $payload)
             ->assertSessionHasErrors(['client_id']);
    }

    // ── Campos obrigatórios ───────────────────────────────────────────────────

    public function test_booking_fails_when_required_fields_are_missing(): void
    {
        $this->from('/calendar')
             ->post('/appointments', [])
             ->assertSessionHasErrors(['client_id', 'employee_id', 'service_id', 'starts_at']);
    }
}
