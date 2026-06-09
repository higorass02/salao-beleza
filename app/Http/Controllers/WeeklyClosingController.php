<?php

namespace App\Http\Controllers;

use App\Models\DailyClosing;
use App\Models\WeeklyClosing;
use App\Services\CashClosingService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class WeeklyClosingController extends Controller
{
    public function index(Request $request, CashClosingService $service)
    {
        $weekStart = $request->input('week')
            ? Carbon::parse($request->input('week'))->startOfWeek(Carbon::MONDAY)
            : Carbon::now()->startOfWeek(Carbon::MONDAY);

        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

        // Build list of 7 days — totals always computed live for accuracy
        $days = [];
        for ($d = $weekStart->copy(); $d->lte($weekEnd); $d->addDay()) {
            $closing  = DailyClosing::where('date', $d->toDateString())->first();
            $computed = $service->computeDay($d->toDateString());
            $fee      = $computed['house_fee_total'];
            $days[] = [
                'date'            => $d->toDateString(),
                'label'           => $d->translatedFormat('l, d/m'),
                'status'          => $closing?->status ?? 'pending',
                'total_value'     => $computed['total_value'],
                'provider_total'  => round($computed['total_value'] - $fee, 2),
                'store_total'     => $fee,
                'house_fee_total' => $fee,
            ];
        }

        $existing        = WeeklyClosing::where('week_start', $weekStart->toDateString())->first();
        $providerSummary = $service->computeWeekProviders($weekStart, $weekEnd);

        return Inertia::render('Cash/Weekly', [
            'weekStart'       => $weekStart->toDateString(),
            'weekEnd'         => $weekEnd->toDateString(),
            'days'            => $days,
            'weeklyClosing'   => $existing,
            'providerSummary' => $providerSummary,
        ]);
    }

    public function recalculate(Request $request, CashClosingService $service)
    {
        $request->validate(['week_start' => ['required', 'date']]);

        $weekStart = Carbon::parse($request->week_start)->startOfWeek(Carbon::MONDAY);
        $weekEnd   = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

        $closing = WeeklyClosing::where('week_start', $weekStart->toDateString())->firstOrFail();

        $total = 0.0; $provider = 0.0; $store = 0.0; $fee = 0.0;
        for ($d = $weekStart->copy(); $d->lte($weekEnd); $d->addDay()) {
            $day      = $service->computeDay($d->toDateString());
            $total   += $day['total_value'];
            $provider += $day['provider_total'];
            $store   += $day['store_total'];
            $fee     += $day['house_fee_total'];
        }

        $closing->update([
            'total_value'     => round($total, 2),
            'provider_total'  => round($provider, 2),
            'store_total'     => round($store, 2),
            'house_fee_total' => round($fee, 2),
        ]);

        return redirect()->route('weekly.index', ['week' => $request->week_start])
            ->with('success', 'Fechamento semanal recalculado com sucesso!');
    }

    public function close(Request $request, CashClosingService $service)
    {
        $request->validate(['week_start' => ['required', 'date']]);

        $weeklyClosing = $service->closeWeek($request->week_start);

        return redirect()->route('weekly.index', ['week' => $request->week_start])
            ->with('success', 'Fechamento semanal realizado com sucesso!');
    }
}
