<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\CashEntry;
use App\Models\DailyClosing;
use App\Models\Setting;
use App\Models\WeeklyClosing;
use Illuminate\Support\Carbon;

class CashClosingService
{
    /**
     * Compute totals for a given date without persisting.
     */
    public function computeDay(string $date): array
    {
        $houseRate = (float) Setting::get('house_fee_rate', 0);

        $appointments = Appointment::with('service')
            ->whereDate('starts_at', $date)
            ->get();

        $entries = CashEntry::where('date', $date)->get();

        $total        = 0.0;
        $providerSum  = 0.0;
        $storeSum     = 0.0;
        $houseFeeSum  = 0.0;

        foreach ($appointments as $appt) {
            $service = $appt->service;
            if (! $service) continue;

            [$t, $p, $s, $h] = $this->calcItem(
                (float) $service->price,
                (float) $service->provider_percentage,
                (bool)  $service->include_house_fee,
                $houseRate
            );

            $total       += $t;
            $providerSum += $p;
            $storeSum    += $s;
            $houseFeeSum += $h;
        }

        foreach ($entries as $entry) {
            [$t, $p, $s, $h] = $this->calcItem(
                (float) $entry->service_value,
                (float) $entry->provider_percentage,
                (bool)  $entry->include_house_fee,
                $houseRate
            );

            $total       += $t;
            $providerSum += $p;
            $storeSum    += $s;
            $houseFeeSum += $h;
        }

        return [
            'total_value'     => round($total, 2),
            'provider_total'  => round($providerSum, 2),
            'store_total'     => round($storeSum, 2),
            'house_fee_total' => round($houseFeeSum, 2),
        ];
    }

    /**
     * Close a day: compute totals and persist DailyClosing.
     */
    public function closeDay(string $date): DailyClosing
    {
        $totals = $this->computeDay($date);

        return DailyClosing::updateOrCreate(
            ['date' => $date],
            array_merge($totals, [
                'status'    => 'closed',
                'closed_at' => now(),
            ])
        );
    }

    /**
     * Close a week: aggregate closed daily_closings and persist WeeklyClosing.
     */
    public function closeWeek(string $weekStart): WeeklyClosing
    {
        $start   = Carbon::parse($weekStart)->startOfWeek(Carbon::MONDAY);
        $end     = $start->copy()->endOfWeek(Carbon::SUNDAY);

        $dailies = DailyClosing::whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('date')
            ->get();

        $total       = 0.0;
        $providerSum = 0.0;
        $storeSum    = 0.0;
        $houseFeeSum = 0.0;

        $daysSummary = [];
        foreach ($dailies as $d) {
            $total       += (float) $d->total_value;
            $providerSum += (float) $d->provider_total;
            $storeSum    += (float) $d->store_total;
            $houseFeeSum += (float) $d->house_fee_total;

            $daysSummary[] = [
                'date'            => $d->date->toDateString(),
                'status'          => $d->status,
                'total_value'     => (float) $d->total_value,
                'provider_total'  => (float) $d->provider_total,
                'store_total'     => (float) $d->store_total,
                'house_fee_total' => (float) $d->house_fee_total,
            ];
        }

        return WeeklyClosing::updateOrCreate(
            ['week_start' => $start->toDateString()],
            [
                'week_end'        => $end->toDateString(),
                'total_value'     => round($total, 2),
                'provider_total'  => round($providerSum, 2),
                'store_total'     => round($storeSum, 2),
                'house_fee_total' => round($houseFeeSum, 2),
                'days_summary'    => $daysSummary,
                'closed_at'       => now(),
            ]
        );
    }

    /**
     * Calculate split for a single item.
     * Returns [total, provider_amount, store_amount, house_fee].
     */
    private function calcItem(
        float $baseValue,
        float $providerPct,
        bool  $includeHouseFee,
        float $houseRate
    ): array {
        $houseFee     = $includeHouseFee ? $baseValue * $houseRate / 100 : 0.0;
        $total        = $baseValue + $houseFee;
        $providerAmt  = $baseValue * $providerPct / 100;
        $storeAmt     = $total - $providerAmt;

        return [$total, $providerAmt, $storeAmt, $houseFee];
    }
}
