<?php

namespace Tests\Unit;

use App\Actions\Appointment\CreateAppointmentAction;
use App\Models\Appointment;
use App\Repositories\Contracts\AppointmentRepositoryInterface;
use App\Services\AppointmentService;
use PHPUnit\Framework\TestCase;

/**
 * Verifica que o AppointmentService delega corretamente para Action e Repository.
 * Nenhum acesso ao banco de dados — tudo via mocks.
 */
class AppointmentServiceUnitTest extends TestCase
{
    public function test_create_delegates_to_action(): void
    {
        $appointment = new Appointment();
        $data        = ['client_id' => 1, 'employee_id' => 1, 'service_id' => 1, 'starts_at' => '2026-06-01 10:00:00'];

        $action = $this->createMock(CreateAppointmentAction::class);
        $action->expects($this->once())
            ->method('execute')
            ->with($data)
            ->willReturn($appointment);

        $repo    = $this->createMock(AppointmentRepositoryInterface::class);
        $service = new AppointmentService($repo, $action);

        $result = $service->create($data);

        $this->assertSame($appointment, $result);
    }

    public function test_list_upcoming_delegates_to_repository(): void
    {
        $collection = collect([new Appointment()]);

        $repo = $this->createMock(AppointmentRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('getUpcoming')
            ->willReturn($collection);

        $action  = $this->createMock(CreateAppointmentAction::class);
        $service = new AppointmentService($repo, $action);

        $result = $service->listUpcoming();

        $this->assertSame($collection, $result);
    }
}
