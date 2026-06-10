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

        // Calcula a data limite a partir dos meses informados (máx. 3 meses)
        $months       = isset($data['recurrence_months']) ? (int) $data['recurrence_months'] : null;
        $untilDate    = ($months && ! empty($data['is_recurring']))
            ? Carbon::parse($startsAt)->addMonths($months)->toDateString()
            : null;

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
            'recurrence_until' => $untilDate,
            'parent_id'        => null,
        ]);

        if (! empty($data['is_recurring']) && ! empty($data['recurrence_type']) && $untilDate) {
            $this->generateRecurrences($parent, $data, $service->duration_minutes, $untilDate);
        }

        return $parent;
    }

    private function generateRecurrences(Appointment $parent, array $data, int $durationMinutes, string $untilDate): void
    {
        $interval = $data['recurrence_type'] === 'weekly' ? 7 : 14;
        $until    = Carbon::parse($untilDate);
        $current  = Carbon::parse($data['starts_at'])->addDays($interval);

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
                    'recurrence_until' => $untilDate,
                    'parent_id'        => $parent->id,
                ]);
            }

            $current->addDays($interval);
        }
    }
}
