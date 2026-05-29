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
    public function index(Request $request)
    {
        $weekStart = $request->input('week')
            ? Carbon::parse($request->input('week'))->startOfWeek(Carbon::MONDAY)
            : Carbon::now()->startOfWeek(Carbon::MONDAY);

        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

        // Build list of 7 days with their daily closing data
        $days = [];
        for ($d = $weekStart->copy(); $d->lte($weekEnd); $d->addDay()) {
            $closing = DailyClosing::where('date', $d->toDateString())->first();
            $days[] = [
                'date'            => $d->toDateString(),
                'label'           => $d->translatedFormat('l, d/m'),
                'status'          => $closing?->status ?? 'pending',
                'total_value'     => (float) ($closing?->total_value ?? 0),
                'provider_total'  => (float) ($closing?->provider_total ?? 0),
                'store_total'     => (float) ($closing?->store_total ?? 0),
                'house_fee_total' => (float) ($closing?->house_fee_total ?? 0),
            ];
        }

        $existing = WeeklyClosing::where('week_start', $weekStart->toDateString())->first();

        return Inertia::render('Cash/Weekly', [
            'weekStart'     => $weekStart->toDateString(),
            'weekEnd'       => $weekEnd->toDateString(),
            'days'          => $days,
            'weeklyClosing' => $existing,
        ]);
    }

    public function close(Request $request, CashClosingService $service)
    {
        $request->validate(['week_start' => ['required', 'date']]);

        $weeklyClosing = $service->closeWeek($request->week_start);

        return redirect()->route('weekly.index', ['week' => $request->week_start])
            ->with('success', 'Fechamento semanal realizado com sucesso!');
    }
}
