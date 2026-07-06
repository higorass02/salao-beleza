<?php

namespace App\Actions\Appointment;

use App\Exceptions\SlotUnavailableException;
use App\Models\Appointment;
use App\Repositories\Contracts\AppointmentRepositoryInterface;
use App\Services\ClientService;
use Carbon\Carbon;

class PublicBookingAction
{
    public function __construct(
        private AppointmentRepositoryInterface $repository,
        private ClientService $clientService,
        private CreateAppointmentAction $createAction,
    ) {}

    public function execute(array $data): Appointment
    {
        // Resolve o cliente
        if (! empty($data['google_id'])) {
            $client = $this->clientService->findOrCreateByGoogle([
                'google_id'    => $data['google_id'],
                'name'         => $data['google_name'] ?? 'Usuário Google',
                'email'        => $data['google_email'] ?? null,
                'google_avatar' => $data['google_avatar'] ?? null,
            ]);
        } else {
            $client = $this->clientService->findOrCreateByPhone(
                $data['guest_name'],
                $data['guest_phone'],
            );
        }

        // Verifica se o slot ainda está disponível
        $service  = $this->repository->findService($data['service_id']);
        $startsAt = $data['starts_at'];
        $endsAt   = Carbon::parse($startsAt)->addMinutes($service->duration_minutes)->toDateTimeString();

        if ($this->repository->hasEmployeeConflict($data['employee_id'], $startsAt, $endsAt)) {
            throw new SlotUnavailableException('Horário indisponível. Por favor, escolha outro horário.');
        }

        return $this->createAction->execute([
            'client_id'   => $client->id,
            'employee_id' => $data['employee_id'],
            'service_id'  => $data['service_id'],
            'starts_at'   => $startsAt,
            'notes'       => $data['notes'] ?? null,
        ]);
    }
}
