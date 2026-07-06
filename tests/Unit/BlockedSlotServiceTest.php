<?php

namespace Tests\Unit;

use App\Models\Appointment;
use App\Repositories\Contracts\AppointmentRepositoryInterface;
use App\Services\BlockedSlotService;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

/**
 * Testa a lógica de criação de bloqueios sem tocar o banco.
 */
class BlockedSlotServiceTest extends TestCase
{
    private function makeRepo(
        Collection $conflicts = new Collection(),
    ): AppointmentRepositoryInterface {
        $repo = $this->createMock(AppointmentRepositoryInterface::class);
        $repo->method('getConflictingForEmployee')->willReturn($conflicts);
        $repo->method('createAppointment')->willReturnCallback(function (array $data): Appointment {
            $a             = new Appointment();
            $a->employee_id = $data['employee_id'];
            $a->starts_at  = $data['starts_at'];
            $a->ends_at    = $data['ends_at'];
            $a->status     = $data['status'];
            $a->notes      = $data['notes'] ?? null;

            return $a;
        });

        return $repo;
    }

    // ── Criação de bloqueio sem conflitos ─────────────────────────────────────

    public function test_creates_blocked_appointment_with_correct_status(): void
    {
        $service = new BlockedSlotService($this->makeRepo());

        $result = $service->block([
            'employee_id' => 1,
            'starts_at'   => '2099-12-01 10:00:00',
            'ends_at'     => '2099-12-01 12:00:00',
            'notes'       => 'Folga',
        ]);

        $this->assertEquals('blocked', $result['slot']->status);
        $this->assertEmpty($result['conflicts']);
    }

    public function test_blocked_appointment_uses_correct_employee_and_times(): void
    {
        $repo = $this->createMock(AppointmentRepositoryInterface::class);
        $repo->method('getConflictingForEmployee')->willReturn(new Collection());
        $repo->expects($this->once())
            ->method('createAppointment')
            ->with($this->callback(function (array $data): bool {
                return $data['employee_id'] === 5
                    && $data['starts_at'] === '2099-12-01 14:00:00'
                    && $data['ends_at']   === '2099-12-01 16:00:00';
            }))
            ->willReturn(new Appointment());

        $service = new BlockedSlotService($repo);
        $service->block([
            'employee_id' => 5,
            'starts_at'   => '2099-12-01 14:00:00',
            'ends_at'     => '2099-12-01 16:00:00',
        ]);
    }

    public function test_returns_empty_conflicts_when_none_exist(): void
    {
        $service = new BlockedSlotService($this->makeRepo(new Collection()));
        $result  = $service->block([
            'employee_id' => 1,
            'starts_at'   => '2099-12-01 10:00:00',
            'ends_at'     => '2099-12-01 12:00:00',
        ]);

        $this->assertEmpty($result['conflicts']);
    }

    // ── Criação com conflitos ─────────────────────────────────────────────────

    public function test_returns_conflicting_appointments_when_range_occupied(): void
    {
        $existing = new Appointment();
        $existing->id = 42;

        $service = new BlockedSlotService($this->makeRepo(new Collection([$existing])));

        $result = $service->block([
            'employee_id' => 1,
            'starts_at'   => '2099-12-01 10:00:00',
            'ends_at'     => '2099-12-01 12:00:00',
        ]);

        $this->assertCount(1, $result['conflicts']);
        $this->assertNotEmpty($result['slot']->status); // bloqueio criado mesmo assim
    }

    public function test_creates_block_even_when_appointments_conflict(): void
    {
        $existing = new Appointment();

        $service = new BlockedSlotService($this->makeRepo(new Collection([$existing])));

        $result = $service->block([
            'employee_id' => 1,
            'starts_at'   => '2099-12-01 10:00:00',
            'ends_at'     => '2099-12-01 12:00:00',
        ]);

        // O bloqueio é criado independentemente dos conflitos
        $this->assertEquals('blocked', $result['slot']->status);
    }

    // ── Validação do payload para o repositório ───────────────────────────────

    public function test_repository_receives_blocked_status_and_null_client_service(): void
    {
        $repo = $this->createMock(AppointmentRepositoryInterface::class);
        $repo->method('getConflictingForEmployee')->willReturn(new Collection());
        $repo->expects($this->once())
            ->method('createAppointment')
            ->with($this->callback(function (array $data): bool {
                return $data['status'] === 'blocked'
                    && $data['client_id'] === null
                    && $data['service_id'] === null;
            }))
            ->willReturn(new Appointment());

        $service = new BlockedSlotService($repo);
        $service->block([
            'employee_id' => 1,
            'starts_at'   => '2099-12-01 10:00:00',
            'ends_at'     => '2099-12-01 12:00:00',
        ]);
    }
}
