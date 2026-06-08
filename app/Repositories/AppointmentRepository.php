<?php

namespace App\Repositories;

use App\Models\Appointment;
use App\Models\Service;
use App\Repositories\Contracts\AppointmentRepositoryInterface;

class AppointmentRepository implements AppointmentRepositoryInterface
{
    public function getUpcoming()
    {
        return Appointment::with(['client', 'employee', 'service'])
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at')
            ->get();
    }

    public function getForCalendar(string $from, string $to)
    {
        return Appointment::with(['client', 'employee', 'service'])
            ->where('starts_at', '>=', $from)
            ->where('starts_at', '<=', $to)
            ->where('status', '!=', 'canceled')
            ->orderBy('starts_at')
            ->get();
    }

    public function hasConflict(int $employeeId, int $serviceId, string $startsAt, string $endsAt, ?int $excludeId = null): bool
    {
        return Appointment::where('employee_id', $employeeId)
            ->where('service_id', $serviceId)
            ->where('status', '!=', 'canceled')
            ->where('starts_at', '<', $endsAt)
            ->where('ends_at', '>', $startsAt)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->exists();
    }

    public function findService(int $id): Service
    {
        return Service::findOrFail($id);
    }

    public function createAppointment(array $data): Appointment
    {
        return Appointment::create($data);
    }
}
