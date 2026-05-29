<?php

namespace Tests\Unit;

use App\Actions\Appointment\CreateAppointmentAction;
use App\Exceptions\AppointmentConflictException;
use App\Models\Appointment;
use App\Models\Service;
use App\Repositories\Contracts\AppointmentRepositoryInterface;
use PHPUnit\Framework\TestCase;

/**
 * Testa a lógica de negócio da Action sem tocar o banco de dados.
 * O repositório é substituído por um mock do PHPUnit.
 */
class CreateAppointmentActionTest extends TestCase
{
    private function makeService(int $durationMinutes): Service
    {
        $service                    = new Service();
        $service->duration_minutes  = $durationMinutes;

        return $service;
    }

    private function makeRepo(
        bool $hasConflict = false,
        int  $durationMinutes = 60,
    ): AppointmentRepositoryInterface {
        $repo = $this->createMock(AppointmentRepositoryInterface::class);
        $repo->method('findService')->willReturn($this->makeService($durationMinutes));
        $repo->method('hasConflict')->willReturn($hasConflict);
        $repo->method('createAppointment')->willReturn(new Appointment());

        return $repo;
    }

    // ── RN-02: ends_at = starts_at + duration_minutes ────────────────────────

    public function test_ends_at_is_calculated_from_service_duration(): void
    {
        $repo = $this->createMock(AppointmentRepositoryInterface::class);
        $repo->method('findService')->willReturn($this->makeService(60));
        $repo->method('hasConflict')->willReturn(false);
        $repo->expects($this->once())
            ->method('createAppointment')
            ->with($this->callback(fn(array $d) =>
                $d['starts_at'] === '2026-06-01 10:00:00' &&
                $d['ends_at']   === '2026-06-01 11:00:00'
            ))
            ->willReturn(new Appointment());

        (new CreateAppointmentAction($repo))->execute([
            'client_id'   => 1,
            'employee_id' => 1,
            'service_id'  => 1,
            'starts_at'   => '2026-06-01 10:00:00',
        ]);
    }

    public function test_ends_at_respects_different_durations(): void
    {
        $repo = $this->createMock(AppointmentRepositoryInterface::class);
        $repo->method('findService')->willReturn($this->makeService(45));
        $repo->method('hasConflict')->willReturn(false);
        $repo->expects($this->once())
            ->method('createAppointment')
            ->with($this->callback(fn(array $d) => $d['ends_at'] === '2026-06-01 14:45:00'))
            ->willReturn(new Appointment());

        (new CreateAppointmentAction($repo))->execute([
            'client_id'   => 1,
            'employee_id' => 2,
            'service_id'  => 3,
            'starts_at'   => '2026-06-01 14:00:00',
        ]);
    }

    // ── RN-01: conflito de horário ────────────────────────────────────────────

    public function test_throws_conflict_exception_when_slot_is_taken(): void
    {
        $this->expectException(AppointmentConflictException::class);

        (new CreateAppointmentAction($this->makeRepo(hasConflict: true)))->execute([
            'client_id'   => 1,
            'employee_id' => 1,
            'service_id'  => 1,
            'starts_at'   => '2026-06-01 10:30:00',
        ]);
    }

    public function test_conflict_check_receives_correct_window(): void
    {
        $repo = $this->createMock(AppointmentRepositoryInterface::class);
        $repo->method('findService')->willReturn($this->makeService(45));
        $repo->expects($this->once())
            ->method('hasConflict')
            ->with(2, 3, '2026-06-01 14:00:00', '2026-06-01 14:45:00')
            ->willReturn(false);
        $repo->method('createAppointment')->willReturn(new Appointment());

        (new CreateAppointmentAction($repo))->execute([
            'client_id'   => 1,
            'employee_id' => 2,
            'service_id'  => 3,
            'starts_at'   => '2026-06-01 14:00:00',
        ]);
    }

    public function test_does_not_create_when_conflict_exists(): void
    {
        $repo = $this->createMock(AppointmentRepositoryInterface::class);
        $repo->method('findService')->willReturn($this->makeService(60));
        $repo->method('hasConflict')->willReturn(true);
        $repo->expects($this->never())->method('createAppointment');

        try {
            (new CreateAppointmentAction($repo))->execute([
                'client_id'   => 1,
                'employee_id' => 1,
                'service_id'  => 1,
                'starts_at'   => '2026-06-01 10:00:00',
            ]);
        } catch (AppointmentConflictException) {
        }
    }

    // ── Comportamentos auxiliares ─────────────────────────────────────────────

    public function test_notes_default_to_null_when_absent(): void
    {
        $repo = $this->createMock(AppointmentRepositoryInterface::class);
        $repo->method('findService')->willReturn($this->makeService(30));
        $repo->method('hasConflict')->willReturn(false);
        $repo->expects($this->once())
            ->method('createAppointment')
            ->with($this->callback(fn(array $d) => $d['notes'] === null))
            ->willReturn(new Appointment());

        (new CreateAppointmentAction($repo))->execute([
            'client_id'   => 1,
            'employee_id' => 1,
            'service_id'  => 1,
            'starts_at'   => '2026-06-01 10:00:00',
        ]);
    }

    public function test_status_is_scheduled_on_creation(): void
    {
        $repo = $this->createMock(AppointmentRepositoryInterface::class);
        $repo->method('findService')->willReturn($this->makeService(30));
        $repo->method('hasConflict')->willReturn(false);
        $repo->expects($this->once())
            ->method('createAppointment')
            ->with($this->callback(fn(array $d) => $d['status'] === 'scheduled'))
            ->willReturn(new Appointment());

        (new CreateAppointmentAction($repo))->execute([
            'client_id'   => 1,
            'employee_id' => 1,
            'service_id'  => 1,
            'starts_at'   => '2026-06-01 10:00:00',
        ]);
    }
}
