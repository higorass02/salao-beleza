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

        return $this->repository->createAppointment([
            'client_id'   => $data['client_id'],
            'employee_id' => $data['employee_id'],
            'service_id'  => $data['service_id'],
            'starts_at'   => $startsAt,
            'ends_at'     => $endsAt,
            'status'      => 'scheduled',
            'notes'       => $data['notes'] ?? null,
        ]);
    }
}
