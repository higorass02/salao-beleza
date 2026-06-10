<?php

namespace App\Actions\Appointment;

use App\Exceptions\AppointmentConflictException;
use App\Models\Appointment;
use App\Repositories\Contracts\AppointmentRepositoryInterface;
use Illuminate\Support\Carbon;

class CreateAppointmentAction
{
    public function __construct(protected AppointmentRepositoryInterface $repository)
    {
    }

    public function execute(array $data): Appointment
    {
        $service  = $this->repository->findService($data['service_id']);
        $startsAt = $data['starts_at'];
        $endsAt   = Carbon::parse($startsAt)->addMinutes($service->duration_minutes)->toDateTimeString();

        if ($this->repository->hasConflict($data['employee_id'], $data['service_id'], $startsAt, $endsAt)) {
            throw new AppointmentConflictException('Conflito de horário detectado para este funcionário e serviço.');
        }

        $parent = $this->repository->createAppointment([
            'client_id'        => $data['client_id'],
            'employee_id'      => $data['employee_id'],
            'service_id'       => $data['service_id'],
            'starts_at'        => $startsAt,
            'ends_at'          => $endsAt,
            'status'           => 'scheduled',
            'notes'            => $data['notes'] ?? null,
            'is_recurring'     => ! empty($data['is_recurring']),
            'recurrence_type'  => $data['recurrence_type'] ?? null,
            'recurrence_until' => $data['recurrence_until'] ?? null,
            'parent_id'        => null,
        ]);

        if (! empty($data['is_recurring']) && ! empty($data['recurrence_type']) && ! empty($data['recurrence_until'])) {
            $this->generateRecurrences($parent, $data, $service->duration_minutes);
        }

        return $parent;
    }

    private function generateRecurrences(Appointment $parent, array $data, int $durationMinutes): void
    {
        $interval = $data['recurrence_type'] === 'weekly' ? 7 : 14;
        $until    = Carbon::parse($data['recurrence_until']);
        $maxUntil = Carbon::parse($data['starts_at'])->addMonths(3);

        if ($until->gt($maxUntil)) {
            $until = $maxUntil;
        }

        $current = Carbon::parse($data['starts_at'])->addDays($interval);

        while ($current->lte($until)) {
            $startsAt = $current->toDateTimeString();
            $endsAt   = $current->copy()->addMinutes($durationMinutes)->toDateTimeString();

            if (! $this->repository->hasConflict($data['employee_id'], $data['service_id'], $startsAt, $endsAt)) {
                $this->repository->createAppointment([
                    'client_id'        => $data['client_id'],
                    'employee_id'      => $data['employee_id'],
                    'service_id'       => $data['service_id'],
                    'starts_at'        => $startsAt,
                    'ends_at'          => $endsAt,
                    'status'           => 'scheduled',
                    'notes'            => $data['notes'] ?? null,
                    'is_recurring'     => true,
                    'recurrence_type'  => $data['recurrence_type'],
                    'recurrence_until' => $data['recurrence_until'],
                    'parent_id'        => $parent->id,
                ]);
            }

            $current->addDays($interval);
        }
    }
}
