<?php

namespace App\Actions\Appointment;

use App\Exceptions\AppointmentConflictException;
use App\Models\Appointment;
use App\Repositories\Contracts\AppointmentRepositoryInterface;
use Illuminate\Support\Carbon;

class UpdateAppointmentAction
{
    public function __construct(protected AppointmentRepositoryInterface $repository)
    {
    }

    public function execute(Appointment $appointment, array $data): Appointment
    {
        $service  = $this->repository->findService($data['service_id']);
        $startsAt = $data['starts_at'];
        $endsAt   = Carbon::parse($startsAt)->addMinutes($service->duration_minutes)->toDateTimeString();

        if ($this->repository->hasConflict($data['employee_id'], $data['service_id'], $startsAt, $endsAt, $appointment->id)) {
            throw new AppointmentConflictException('Conflito de horário detectado para este funcionário e serviço.');
        }

        $appointment->update([
            'client_id'   => $data['client_id'],
            'employee_id' => $data['employee_id'],
            'service_id'  => $data['service_id'],
            'starts_at'   => $startsAt,
            'ends_at'     => $endsAt,
            'status'      => $data['status'],
            'notes'       => $data['notes'] ?? null,
        ]);

        return $appointment;
    }
}
