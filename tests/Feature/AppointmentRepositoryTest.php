<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\Employee;
use App\Models\Service;
use App\Repositories\AppointmentRepository;
use Tests\TestCase;

/**
 * Testa as queries do AppointmentRepository contra SQLite in-memory.
 * Valida a lógica SQL de detecção de conflito (RN-01) e listagem de próximos.
 */
class AppointmentRepositoryTest extends TestCase
{
    private AppointmentRepository $repo;
    private Employee $employee;
    private Service $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repo     = new AppointmentRepository();
        $this->employee = Employee::factory()->create();
        $this->service  = Service::factory()->create(['duration_minutes' => 60]);
    }

    // ── hasConflict ───────────────────────────────────────────────────────────

    public function test_no_conflict_when_table_is_empty(): void
    {
        $result = $this->repo->hasConflict(
            $this->employee->id,
            $this->service->id,
            '2026-06-15 10:00:00',
            '2026-06-15 11:00:00',
        );

        $this->assertFalse($result);
    }

    public function test_conflict_detected_on_exact_overlap(): void
    {
        Appointment::factory()->create([
            'employee_id' => $this->employee->id,
            'service_id'  => $this->service->id,
            'starts_at'   => '2026-06-15 10:00:00',
            'ends_at'     => '2026-06-15 11:00:00',
            'status'      => 'scheduled',
        ]);

        $result = $this->repo->hasConflict(
            $this->employee->id,
            $this->service->id,
            '2026-06-15 10:00:00',
            '2026-06-15 11:00:00',
        );

        $this->assertTrue($result);
    }

    public function test_conflict_detected_on_partial_overlap_start(): void
    {
        Appointment::factory()->create([
            'employee_id' => $this->employee->id,
            'service_id'  => $this->service->id,
            'starts_at'   => '2026-06-15 10:00:00',
            'ends_at'     => '2026-06-15 11:00:00',
            'status'      => 'scheduled',
        ]);

        // Novo agendamento começa durante o existente
        $result = $this->repo->hasConflict(
            $this->employee->id,
            $this->service->id,
            '2026-06-15 10:30:00',
            '2026-06-15 11:30:00',
        );

        $this->assertTrue($result);
    }

    public function test_conflict_detected_when_new_contains_existing(): void
    {
        Appointment::factory()->create([
            'employee_id' => $this->employee->id,
            'service_id'  => $this->service->id,
            'starts_at'   => '2026-06-15 10:30:00',
            'ends_at'     => '2026-06-15 11:00:00',
            'status'      => 'scheduled',
        ]);

        // Novo agendamento engloba o existente
        $result = $this->repo->hasConflict(
            $this->employee->id,
            $this->service->id,
            '2026-06-15 10:00:00',
            '2026-06-15 11:30:00',
        );

        $this->assertTrue($result);
    }

    public function test_no_conflict_for_adjacent_appointments(): void
    {
        Appointment::factory()->create([
            'employee_id' => $this->employee->id,
            'service_id'  => $this->service->id,
            'starts_at'   => '2026-06-15 10:00:00',
            'ends_at'     => '2026-06-15 11:00:00',
            'status'      => 'scheduled',
        ]);

        // Começa exatamente quando o anterior termina → sem sobreposição
        $result = $this->repo->hasConflict(
            $this->employee->id,
            $this->service->id,
            '2026-06-15 11:00:00',
            '2026-06-15 12:00:00',
        );

        $this->assertFalse($result);
    }

    public function test_canceled_appointment_does_not_cause_conflict(): void
    {
        Appointment::factory()->canceled()->create([
            'employee_id' => $this->employee->id,
            'service_id'  => $this->service->id,
            'starts_at'   => '2026-06-15 10:00:00',
            'ends_at'     => '2026-06-15 11:00:00',
        ]);

        $result = $this->repo->hasConflict(
            $this->employee->id,
            $this->service->id,
            '2026-06-15 10:00:00',
            '2026-06-15 11:00:00',
        );

        $this->assertFalse($result);
    }

    public function test_no_conflict_for_different_employee(): void
    {
        $otherEmployee = Employee::factory()->create();

        Appointment::factory()->create([
            'employee_id' => $otherEmployee->id,
            'service_id'  => $this->service->id,
            'starts_at'   => '2026-06-15 10:00:00',
            'ends_at'     => '2026-06-15 11:00:00',
            'status'      => 'scheduled',
        ]);

        $result = $this->repo->hasConflict(
            $this->employee->id,
            $this->service->id,
            '2026-06-15 10:00:00',
            '2026-06-15 11:00:00',
        );

        $this->assertFalse($result);
    }

    public function test_no_conflict_for_different_service(): void
    {
        $otherService = Service::factory()->create();

        Appointment::factory()->create([
            'employee_id' => $this->employee->id,
            'service_id'  => $otherService->id,
            'starts_at'   => '2026-06-15 10:00:00',
            'ends_at'     => '2026-06-15 11:00:00',
            'status'      => 'scheduled',
        ]);

        $result = $this->repo->hasConflict(
            $this->employee->id,
            $this->service->id,
            '2026-06-15 10:00:00',
            '2026-06-15 11:00:00',
        );

        $this->assertFalse($result);
    }

    // ── getUpcoming ───────────────────────────────────────────────────────────

    public function test_get_upcoming_returns_only_future_appointments(): void
    {
        $future = Appointment::factory()->create([
            'starts_at' => now()->addDay()->toDateTimeString(),
            'ends_at'   => now()->addDay()->addHour()->toDateTimeString(),
        ]);

        Appointment::factory()->create([
            'starts_at' => now()->subDay()->toDateTimeString(),
            'ends_at'   => now()->subDay()->addHour()->toDateTimeString(),
        ]);

        $results = $this->repo->getUpcoming();

        $this->assertCount(1, $results);
        $this->assertEquals($future->id, $results->first()->id);
    }

    public function test_get_upcoming_orders_by_starts_at(): void
    {
        $later  = Appointment::factory()->create(['starts_at' => now()->addDays(3), 'ends_at' => now()->addDays(3)->addHour()]);
        $sooner = Appointment::factory()->create(['starts_at' => now()->addDay(),   'ends_at' => now()->addDay()->addHour()]);

        $results = $this->repo->getUpcoming();

        $this->assertEquals($sooner->id, $results->first()->id);
        $this->assertEquals($later->id, $results->last()->id);
    }

    // ── getForCalendar ────────────────────────────────────────────────────────

    public function test_get_for_calendar_returns_all_without_employee_filter(): void
    {
        $other = Employee::factory()->create();

        Appointment::factory()->create([
            'employee_id' => $this->employee->id,
            'starts_at'   => '2026-07-01 10:00:00',
            'ends_at'     => '2026-07-01 11:00:00',
            'status'      => 'scheduled',
        ]);
        Appointment::factory()->create([
            'employee_id' => $other->id,
            'starts_at'   => '2026-07-01 14:00:00',
            'ends_at'     => '2026-07-01 15:00:00',
            'status'      => 'scheduled',
        ]);

        $results = $this->repo->getForCalendar('2026-07-01 00:00:00', '2026-07-01 23:59:59');

        $this->assertCount(2, $results);
    }

    public function test_get_for_calendar_filters_by_employee_id(): void
    {
        $other = Employee::factory()->create();

        $mine = Appointment::factory()->create([
            'employee_id' => $this->employee->id,
            'starts_at'   => '2026-07-01 10:00:00',
            'ends_at'     => '2026-07-01 11:00:00',
            'status'      => 'scheduled',
        ]);
        Appointment::factory()->create([
            'employee_id' => $other->id,
            'starts_at'   => '2026-07-01 14:00:00',
            'ends_at'     => '2026-07-01 15:00:00',
            'status'      => 'scheduled',
        ]);

        $results = $this->repo->getForCalendar(
            '2026-07-01 00:00:00',
            '2026-07-01 23:59:59',
            $this->employee->id,
        );

        $this->assertCount(1, $results);
        $this->assertEquals($mine->id, $results->first()->id);
    }
}
