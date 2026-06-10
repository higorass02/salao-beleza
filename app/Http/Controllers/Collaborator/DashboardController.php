<?php

namespace App\Http\Controllers\Collaborator;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $employeeId = Auth::user()->employee_id;

        $upcoming = Appointment::with(['client', 'service'])
            ->where('employee_id', $employeeId)
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at')
            ->limit(10)
            ->get()
            ->map(fn ($a) => [
                'id'           => $a->id,
                'starts_at'    => $a->starts_at->format('d/m/Y H:i'),
                'client_name'  => $a->client?->name,
                'service_name' => $a->service?->name,
                'status'       => $a->status,
            ]);

        $todayCount = Appointment::where('employee_id', $employeeId)
            ->whereDate('starts_at', today())
            ->count();

        $weekCount = Appointment::where('employee_id', $employeeId)
            ->whereBetween('starts_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek(),
            ])
            ->count();

        return Inertia::render('Collaborator/Dashboard', [
            'upcoming'   => $upcoming,
            'todayCount' => $todayCount,
            'weekCount'  => $weekCount,
        ]);
    }
}
