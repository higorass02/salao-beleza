<?php

namespace App\Services;

use App\Actions\Appointment\CreateAppointmentAction;
use App\Repositories\Contracts\AppointmentRepositoryInterface;

class AppointmentService
{
    public function __construct(
        protected AppointmentRepositoryInterface $repository,
        protected CreateAppointmentAction $action
    ) {
    }

    public function create(array $data)
    {
        return $this->action->execute($data);
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
