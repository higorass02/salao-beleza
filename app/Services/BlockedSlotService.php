<?php

namespace App\Services;

use App\Models\Appointment;
use App\Repositories\Contracts\AppointmentRepositoryInterface;
use Illuminate\Support\Collection;

class BlockedSlotService
{
    public function __construct(
        private AppointmentRepositoryInterface $repository,
    ) {}

    /**
     * Cria um bloqueio de horário para um funcionário.
     *
     * @return array{ slot: Appointment, conflicts: Collection }
     */
    public function block(array $data): array
    {
        $conflicts = $this->repository->getConflictingForEmployee(
            $data['employee_id'],
            $data['starts_at'],
            $data['ends_at'],
        );

        $slot = $this->repository->createAppointment([
            'employee_id' => $data['employee_id'],
            'client_id'   => null,
            'service_id'  => null,
            'starts_at'   => $data['starts_at'],
            'ends_at'     => $data['ends_at'],
            'status'      => 'blocked',
            'notes'       => $data['notes'] ?? null,
        ]);

        return ['slot' => $slot, 'conflicts' => $conflicts];
    }

    public function destroy(Appointment $slot): void
    {
        $slot->delete();
    }

    public function getConflictingForEmployee(int $employeeId, string $startsAt, string $endsAt): Collection
    {
        return $this->repository->getConflictingForEmployee($employeeId, $startsAt, $endsAt);
    }
}
