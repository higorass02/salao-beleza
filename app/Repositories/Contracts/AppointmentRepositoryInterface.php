<?php

namespace App\Repositories\Contracts;

use App\Models\Appointment;
use App\Models\Service;

interface AppointmentRepositoryInterface
{
    public function getUpcoming();

    public function hasConflict(int $employeeId, int $serviceId, string $startsAt, string $endsAt): bool;

    public function findService(int $id): Service;

    public function createAppointment(array $data): Appointment;
}
