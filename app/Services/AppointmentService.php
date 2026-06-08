<?php

namespace App\Services;

use App\Actions\Appointment\CreateAppointmentAction;
use App\Actions\Appointment\UpdateAppointmentAction;
use App\Models\Appointment;
use App\Repositories\Contracts\AppointmentRepositoryInterface;

class AppointmentService
{
    public function __construct(
        protected AppointmentRepositoryInterface $repository,
        protected CreateAppointmentAction $createAction,
        protected UpdateAppointmentAction $updateAction,
    ) {
    }

    public function create(array $data)
    {
        return $this->createAction->execute($data);
    }

    public function update(Appointment $appointment, array $data): Appointment
    {
        return $this->updateAction->execute($appointment, $data);
    }

    public function listUpcoming()
    {
        return $this->repository->getUpcoming();
    }

    public function listForCalendar(string $from, string $to)
    {
        return $this->repository->getForCalendar($from, $to);
    }
}
