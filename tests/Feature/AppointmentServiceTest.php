<?php

namespace Tests\Feature;

use App\Exceptions\AppointmentConflictException;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Employee;
use App\Models\Service;
use App\Services\AppointmentService;
use Tests\TestCase;

/**
 * Testes de integração do AppointmentService com SQLite in-memory.
 * Cobre todas as Regras de Negócio do fluxo de agendamento.
 */
class AppointmentServiceTest extends TestCase
{
    private AppointmentService $service;
    private Client $client;
    private Employee $employee;
    private Service $svc;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service  = $this->app->make(AppointmentService::class);
        $this->client   = Client::factory()->create();
        $this->employee = Employee::factory()->create(['active' => true]);
        $this->svc      = Service::factory()->create(['duration_minutes' => 60, 'active' => true]);
    }

    // ── RN-02: ends_at calculado automaticamente ──────────────────────────────

    public function test_ends_at_is_calculated_from_service_duration(): void
    {
        $appointment = $this->service->create([
            'client_id'   => $this->client->id,
            'employee_id' => $this->employee->id,
            'service_id'  => $this->svc->id,
            'starts_at'   => '2026-06-15 09:00:00',
        ]);

        $this->assertDatabaseHas('appointments', [
            'id'        => $appointment->id,
            'starts_at' => '2026-06-15 09:00:00',
            'ends_at'   => '2026-06-15 10:00:00',
        ]);
    }

    public function test_ends_at_varies_with_different_service_durations(): void
    {
        $longService = Service::factory()->create(['duration_minutes' => 90]);

        $appointment = $this->service->create([
            'client_id'   => $this->client->id,
            'employee_id' => $this->employee->id,
            'service_id'  => $longService->id,
            'starts_at'   => '2026-06-15 09:00:00',
        ]);

        $this->assertDatabaseHas('appointments', [
            'id'      => $appointment->id,
            'ends_at' => '2026-06-15 10:30:00',
        ]);
    }

    // ── RN-01: conflito de agendamento ────────────────────────────────────────

    public function test_throws_conflict_when_appointments_overlap(): void
    {
        $this->service->create([
            'client_id'   => $this->client->id,
            'employee_id' => $this->employee->id,
            'service_id'  => $this->svc->id,
            'starts_at'   => '2026-06-15 10:00:00',
        ]);

        $this->expectException(AppointmentConflictException::class);

        $this->service->create([
            'client_id'   => $this->client->id,
            'employee_id' => $this->employee->id,
            'service_id'  => $this->svc->id,
            'starts_at'   => '2026-06-15 10:30:00',
        ]);
    }

    public function test_allows_same_employee_with_different_service_at_same_time(): void
    {
        $otherService = Service::factory()->create(['duration_minutes' => 60]);

        $this->service->create([
            'client_id'   => $this->client->id,
            'employee_id' => $this->employee->id,
            'service_id'  => $this->svc->id,
            'starts_at'   => '2026-06-15 10:00:00',
        ]);

        // Não deve lançar exceção — serviço diferente não gera conflito (RN-01)
        $appointment = $this->service->create([
            'client_id'   => $this->client->id,
            'employee_id' => $this->employee->id,
            'service_id'  => $otherService->id,
            'starts_at'   => '2026-06-15 10:00:00',
        ]);

        $this->assertInstanceOf(Appointment::class, $appointment);
    }

    public function test_allows_non_overlapping_appointments_for_same_employee_and_service(): void
    {
        $this->service->create([
            'client_id'   => $this->client->id,
            'employee_id' => $this->employee->id,
            'service_id'  => $this->svc->id,
            'starts_at'   => '2026-06-15 10:00:00',  // ends 11:00
        ]);

        // Começa exatamente quando o anterior termina → sem sobreposição
        $appointment = $this->service->create([
            'client_id'   => $this->client->id,
            'employee_id' => $this->employee->id,
            'service_id'  => $this->svc->id,
            'starts_at'   => '2026-06-15 11:00:00',
        ]);

        $this->assertInstanceOf(Appointment::class, $appointment);
    }

    public function test_canceled_appointment_does_not_block_same_slot(): void
    {
        Appointment::factory()->canceled()->create([
            'client_id'   => $this->client->id,
            'employee_id' => $this->employee->id,
            'service_id'  => $this->svc->id,
            'starts_at'   => '2026-06-15 10:00:00',
            'ends_at'     => '2026-06-15 11:00:00',
        ]);

        // Slot cancelado deve liberar o horário para novo agendamento
        $appointment = $this->service->create([
            'client_id'   => $this->client->id,
            'employee_id' => $this->employee->id,
            'service_id'  => $this->svc->id,
            'starts_at'   => '2026-06-15 10:00:00',
        ]);

        $this->assertInstanceOf(Appointment::class, $appointment);
    }

    // ── Status padrão ─────────────────────────────────────────────────────────

    public function test_new_appointment_has_scheduled_status(): void
    {
        $appointment = $this->service->create([
            'client_id'   => $this->client->id,
            'employee_id' => $this->employee->id,
            'service_id'  => $this->svc->id,
            'starts_at'   => '2026-06-15 09:00:00',
        ]);

        $this->assertDatabaseHas('appointments', [
            'id'     => $appointment->id,
            'status' => 'scheduled',
        ]);
    }
}
