<?php

namespace App\Services;

use App\Repositories\Contracts\AppointmentRepositoryInterface;
use Carbon\Carbon;

class AvailableSlotsService
{
    private const SLOT_INTERVAL_MINUTES = 30;

    public function __construct(
        private AppointmentRepositoryInterface $repository,
    ) {}

    /**
     * Retorna horários disponíveis para um funcionário numa data.
     *
     * @return string[] ex: ['09:00', '09:30', '10:00']
     */
    public function getSlots(
        int    $employeeId,
        int    $serviceId,
        string $date,          // Y-m-d
        string $businessStart, // H:i
        string $businessEnd,   // H:i
    ): array {
        $service      = $this->repository->findService($serviceId);
        $duration     = $service->duration_minutes;
        $dayStart     = Carbon::parse("{$date} {$businessStart}");
        $dayEnd       = Carbon::parse("{$date} {$businessEnd}");
        $now          = Carbon::now();
        $isToday      = Carbon::parse($date)->isToday();

        $slots   = [];
        $current = $dayStart->copy();

        while ($current->lt($dayEnd)) {
            $slotStart = $current->copy();
            $slotEnd   = $current->copy()->addMinutes($duration);

            // Slot ultrapassa o fim do expediente
            if ($slotEnd->gt($dayEnd)) {
                $current->addMinutes(self::SLOT_INTERVAL_MINUTES);
                continue;
            }

            // Slot já passou (apenas para hoje)
            if ($isToday && $slotStart->lte($now)) {
                $current->addMinutes(self::SLOT_INTERVAL_MINUTES);
                continue;
            }

            // Slot conflita com algum appointment (real ou blocked) do funcionário
            if ($this->repository->hasEmployeeConflict($employeeId, $slotStart->toDateTimeString(), $slotEnd->toDateTimeString())) {
                $current->addMinutes(self::SLOT_INTERVAL_MINUTES);
                continue;
            }

            $slots[] = $slotStart->format('H:i');
            $current->addMinutes(self::SLOT_INTERVAL_MINUTES);
        }

        return $slots;
    }
}
