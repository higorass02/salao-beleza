<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppointmentRequest;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Employee;
use App\Models\Service;
use App\Services\AppointmentService;
use Inertia\Inertia;

class AppointmentController extends Controller
{
    public function dashboard()
    {
        return Inertia::render('Dashboard', [
            'stats' => [
                'clients'   => Client::count(),
                'employees' => Employee::where('active', true)->count(),
                'services'  => Service::where('active', true)->count(),
                'upcoming'  => Appointment::where('starts_at', '>=', now())->count(),
            ],
            'upcoming' => Appointment::with(['client', 'employee', 'service'])
                ->where('starts_at', '>=', now())
                ->orderBy('starts_at')
                ->limit(5)
                ->get(),
        ]);
    }

    public function index(AppointmentService $service)
    {
        $appointments = $service->listUpcoming();

        return Inertia::render('Calendar/Index', [
            'appointments'        => $appointments,
            'services'            => Service::where('active', true)->get(['id', 'name', 'price', 'duration_minutes']),
            'employees'           => Employee::where('active', true)->get(['id', 'name', 'role']),
            'businessHoursStart'  => \App\Models\Setting::get('business_hours_start', '08:00'),
            'businessHoursEnd'    => \App\Models\Setting::get('business_hours_end', '20:00'),
        ]);
    }

    public function store(StoreAppointmentRequest $request, AppointmentService $service)
    {
        $appointment = $service->create($request->validated());

        return redirect()->route('dashboard')->with('success', 'Agendamento criado com sucesso.');
    }

    public function destroy(Appointment $appointment)
    {
        $appointment->delete();

        return redirect()->route('dashboard')->with('success', 'Agendamento removido com sucesso.');
    }
}
