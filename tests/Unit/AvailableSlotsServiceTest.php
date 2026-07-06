<?php

namespace Tests\Unit;

use App\Models\Service;
use App\Repositories\Contracts\AppointmentRepositoryInterface;
use App\Services\AvailableSlotsService;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

/**
 * Testa a lógica de geração de slots disponíveis sem tocar o banco.
 * O repositório e o horário comercial são passados como parâmetros/mocks.
 */
class AvailableSlotsServiceTest extends TestCase
{
    private const FUTURE_DATE = '2099-12-01'; // Data futura para evitar filtro de "passou"

    private function makeService(int $durationMinutes): Service
    {
        $svc                   = new Service();
        $svc->duration_minutes = $durationMinutes;

        return $svc;
    }

    private function makeRepo(
        int  $durationMinutes = 60,
        bool $alwaysConflict  = false,
        ?\Closure $conflictCallback = null,
    ): AppointmentRepositoryInterface {
        $repo = $this->createMock(AppointmentRepositoryInterface::class);
        $repo->method('findService')->willReturn($this->makeService($durationMinutes));

        if ($conflictCallback) {
            $repo->method('hasEmployeeConflict')->willReturnCallback($conflictCallback);
        } else {
            $repo->method('hasEmployeeConflict')->willReturn($alwaysConflict);
        }

        return $repo;
    }

    // ── Slots sem conflitos ───────────────────────────────────────────────────

    public function test_returns_30min_slots_within_business_hours(): void
    {
        $svc   = new AvailableSlotsService($this->makeRepo(durationMinutes: 30));
        $slots = $svc->getSlots(1, 1, self::FUTURE_DATE, '09:00', '10:00');

        // 09:00 (ends 09:30), 09:30 (ends 10:00) — ambos cabem
        $this->assertEquals(['09:00', '09:30'], $slots);
    }

    public function test_excludes_slot_where_service_would_exceed_end_time(): void
    {
        $svc   = new AvailableSlotsService($this->makeRepo(durationMinutes: 60));
        $slots = $svc->getSlots(1, 1, self::FUTURE_DATE, '09:00', '10:00');

        // 09:00 (ends 10:00) cabe; 09:30 (ends 10:30) excede
        $this->assertEquals(['09:00'], $slots);
    }

    public function test_returns_empty_when_service_longer_than_window(): void
    {
        $svc   = new AvailableSlotsService($this->makeRepo(durationMinutes: 120));
        $slots = $svc->getSlots(1, 1, self::FUTURE_DATE, '09:00', '10:00');

        $this->assertEmpty($slots);
    }

    // ── Conflitos e bloqueios ─────────────────────────────────────────────────

    public function test_excludes_slot_with_appointment_conflict(): void
    {
        // Conflito apenas no slot das 09:00-10:00
        $repo = $this->makeRepo(durationMinutes: 60, conflictCallback: function (int $emp, string $start, string $end): bool {
            return $start === '2099-12-01 09:00:00';
        });

        $svc   = new AvailableSlotsService($repo);
        $slots = $svc->getSlots(1, 1, self::FUTURE_DATE, '09:00', '11:00');

        $this->assertNotContains('09:00', $slots);
        $this->assertContains('10:00', $slots);
    }

    public function test_excludes_slot_blocked_by_blocked_appointment(): void
    {
        // Simula que o repositório retorna conflito (que inclui status='blocked')
        $repo  = $this->makeRepo(durationMinutes: 60, alwaysConflict: true);
        $svc   = new AvailableSlotsService($repo);
        $slots = $svc->getSlots(1, 1, self::FUTURE_DATE, '09:00', '18:00');

        $this->assertEmpty($slots);
    }

    public function test_returns_empty_when_all_slots_conflict(): void
    {
        $repo  = $this->makeRepo(durationMinutes: 60, alwaysConflict: true);
        $svc   = new AvailableSlotsService($repo);
        $slots = $svc->getSlots(1, 1, self::FUTURE_DATE, '09:00', '11:00');

        $this->assertEmpty($slots);
    }

    // ── Slots no passado ──────────────────────────────────────────────────────

    public function test_excludes_past_slots_for_today(): void
    {
        $today = Carbon::now()->format('Y-m-d');
        $now   = Carbon::now();

        // Garante que o horário de início já passou
        $pastStart = $now->copy()->subHours(2)->format('H:i');
        $futureEnd = $now->copy()->addHours(2)->format('H:i');

        $svc   = new AvailableSlotsService($this->makeRepo(durationMinutes: 30));
        $slots = $svc->getSlots(1, 1, $today, $pastStart, $futureEnd);

        // Nenhum slot anterior ao momento atual deve aparecer
        foreach ($slots as $slot) {
            $slotDt = Carbon::parse("{$today} {$slot}");
            $this->assertTrue($slotDt->isFuture(), "Slot {$slot} deveria estar no futuro");
        }
    }

    public function test_future_date_includes_all_valid_slots(): void
    {
        $svc   = new AvailableSlotsService($this->makeRepo(durationMinutes: 60));
        $slots = $svc->getSlots(1, 1, self::FUTURE_DATE, '09:00', '11:00');

        // 09:00 (ends 10:00), 09:30 (ends 10:30), 10:00 (ends 11:00 — exatamente no fim) são válidos
        $this->assertEquals(['09:00', '09:30', '10:00'], $slots);
    }
}
