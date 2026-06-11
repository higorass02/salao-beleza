<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Employee;
use App\Models\Service;
use App\Services\AppointmentService;
use Illuminate\Support\Carbon;
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
        $from = Carbon::now()->startOfWeek()->subWeeks(4)->toDateTimeString();
        $to   = Carbon::now()->endOfWeek()->addWeeks(8)->toDateTimeString();

        $appointments = $service->listForCalendar($from, $to);

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

        return redirect()->back()->with('success', 'Agendamento criado com sucesso.');
    }

    public function edit(Appointment $appointment)
    {
        return Inertia::render('Appointments/Edit', [
            'appointment' => $appointment->load(['client', 'employee', 'service']),
            'services'    => Service::where('active', true)->get(['id', 'name', 'price', 'duration_minutes']),
            'employees'   => Employee::where('active', true)->get(['id', 'name', 'role']),
        ]);
    }

    public function update(UpdateAppointmentRequest $request, Appointment $appointment, AppointmentService $service)
    {
        $service->update($appointment, $request->validated());

        return redirect()->route('appointments.index')->with('success', 'Agendamento atualizado com sucesso.');
    }

    public function destroy(Appointment $appointment)
    {
        $appointment->delete();

        return redirect()->back()->with('success', 'Agendamento removido com sucesso.');
    }
}
