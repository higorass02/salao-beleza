<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\CashEntry;
use App\Models\DailyClosing;
use App\Models\ProviderDailyClosing;
use App\Models\Setting;
use App\Models\WeeklyClosing;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CashClosingService
{
    public function computeDay(string $date): array
    {
        $houseRate = (float) Setting::get('house_fee_rate', 0) / 100;

        $appointments = Appointment::with('service')
            ->whereDate('starts_at', $date)
            ->get();

        $entries = CashEntry::where('date', $date)->get();

        $total       = 0.0;
        $providerSum = 0.0;
        $storeSum    = 0.0;

        foreach ($appointments as $appt) {
            $val    = (float) ($appt->service?->price ?? 0);
            $total += $val;
            if ($appt->paid_to === 'provider')      $providerSum += $val;
            elseif ($appt->paid_to === 'store')     $storeSum    += $val;
        }

        foreach ($entries as $entry) {
            $val    = (float) $entry->service_value;
            $total += $val;
            if ($entry->paid_to === 'provider')     $providerSum += $val;
            elseif ($entry->paid_to === 'store')    $storeSum    += $val;
        }

        return [
            'total_value'     => round($total, 2),
            'provider_total'  => round($providerSum, 2),
            'store_total'     => round($storeSum, 2),
            'house_fee_total' => round($total * $houseRate, 2),
        ];
    }

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

    public function closeProvider(string $date, int $employeeId): ProviderDailyClosing
    {
        $houseRate = (float) Setting::get('house_fee_rate', 0) / 100;

        $appointments = Appointment::with('service')
            ->whereDate('starts_at', $date)
            ->where('employee_id', $employeeId)
            ->get();

        $totalServices      = 0.0;
        $receivedByProvider = 0.0;
        $receivedByStore    = 0.0;
        $prestadorDeveCasa  = 0.0;
        $casaDevePrestador  = 0.0;

        foreach ($appointments as $appt) {
            $val  = (float) ($appt->service?->price ?? 0);
            $taxa = $val * $houseRate;

            $totalServices += $val;

            if ($appt->paid_to === 'provider') {
                $receivedByProvider += $val;
                $prestadorDeveCasa  += $taxa;
            } elseif ($appt->paid_to === 'store') {
                $receivedByStore   += $val;
                $casaDevePrestador += $val - $taxa;
            }
        }

        $houseFee      = round($totalServices * $houseRate, 2);
        $providerShare = round($totalServices - $houseFee, 2);
        $net           = round($casaDevePrestador - $prestadorDeveCasa, 2);

        return ProviderDailyClosing::create([
            'date'                 => $date,
            'employee_id'          => $employeeId,
            'total_services'       => round($totalServices, 2),
            'house_fee'            => $houseFee,
            'provider_share'       => $providerShare,
            'received_by_provider' => round($receivedByProvider, 2),
            'received_by_store'    => round($receivedByStore, 2),
            'net'                  => $net,
            'closed_at'            => now(),
        ]);
    }

    public function computeWeekProviders(Carbon $start, Carbon $end): array
    {
        $houseRate = (float) Setting::get('house_fee_rate', 0) / 100;

        $appointments = Appointment::with(['service', 'employee'])
            ->whereBetween(\DB::raw('DATE(starts_at)'), [$start->toDateString(), $end->toDateString()])
            ->get();

        $entries = CashEntry::whereBetween('date', [$start->toDateString(), $end->toDateString()])->get();

        $map = [];

        $addItem = function (string $empId, string $empName, float $val, ?string $paidTo) use (&$map, $houseRate) {
            if (!isset($map[$empId])) {
                $map[$empId] = [
                    'id'   => $empId, 'name' => $empName,
                    'total_services' => 0.0, 'house_fee' => 0.0, 'provider_share' => 0.0,
                    'received_by_provider' => 0.0, 'received_by_store' => 0.0, 'pending' => 0.0,
                    'prestador_deve_casa' => 0.0, 'casa_deve_prestador' => 0.0,
                ];
            }

            $taxa         = $val * $houseRate;
            $shareProvider = $val - $taxa;

            $map[$empId]['total_services']  += $val;
            $map[$empId]['house_fee']        += $taxa;
            $map[$empId]['provider_share']   += $shareProvider;

            if ($paidTo === 'provider') {
                $map[$empId]['received_by_provider']  += $val;
                $map[$empId]['prestador_deve_casa']   += $taxa;
            } elseif ($paidTo === 'store') {
                $map[$empId]['received_by_store']     += $val;
                $map[$empId]['casa_deve_prestador']   += $shareProvider;
            } else {
                $map[$empId]['pending'] += $val;
            }
        };

        foreach ($appointments as $appt) {
            $empId   = (string) ($appt->employee_id ?? 'manual');
            $empName = $appt->employee?->name ?? 'Entrada manual';
            $val     = (float) ($appt->service?->price ?? 0);
            $addItem($empId, $empName, $val, $appt->paid_to);
        }

        foreach ($entries as $entry) {
            $addItem('manual', 'Entrada manual', (float) $entry->service_value, $entry->paid_to);
        }

        return array_values(array_map(function ($p) {
            return [
                'id'                   => $p['id'],
                'name'                 => $p['name'],
                'total_services'       => round($p['total_services'], 2),
                'house_fee'            => round($p['house_fee'], 2),
                'provider_share'       => round($p['provider_share'], 2),
                'received_by_provider' => round($p['received_by_provider'], 2),
                'received_by_store'    => round($p['received_by_store'], 2),
                'pending'              => round($p['pending'], 2),
                'net'                  => round($p['casa_deve_prestador'] - $p['prestador_deve_casa'], 2),
            ];
        }, $map));
    }

    public function closeWeek(string $weekStart): WeeklyClosing
    {
        $start = Carbon::parse($weekStart)->startOfWeek(Carbon::MONDAY);
        $end   = $start->copy()->endOfWeek(Carbon::SUNDAY);

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

}
