<?php

namespace App\Http\Controllers\Collaborator;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCollaboratorAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Employee;
use App\Models\Service;
use App\Services\AppointmentService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AppointmentController extends Controller
{
    private function myEmployeeId(): int
    {
        return Auth::user()->employee_id;
    }

    public function index()
    {
        $appointments = Appointment::with(['client', 'service'])
            ->where('employee_id', $this->myEmployeeId())
            ->orderBy('starts_at')
            ->get()
            ->map(fn ($a) => [
                'id'           => $a->id,
                'starts_at'    => $a->starts_at->format('d/m/Y H:i'),
                'client_name'  => $a->client?->name,
                'service_name' => $a->service?->name,
                'status'       => $a->status,
                'notes'        => $a->notes,
            ]);

        return Inertia::render('Collaborator/Appointments/Index', [
            'appointments' => $appointments,
        ]);
    }

    public function create()
    {
        return Inertia::render('Collaborator/Appointments/Create', [
            'services'    => Service::where('active', true)->get(['id', 'name', 'price', 'duration_minutes']),
            'myEmployeeId' => $this->myEmployeeId(),
        ]);
    }

    public function store(StoreCollaboratorAppointmentRequest $request, AppointmentService $service)
    {
        $data = $request->validated();
        $data['employee_id'] = $this->myEmployeeId();

        $service->create($data);

        return redirect()->route('collaborator.appointments.index')
            ->with('success', 'Agendamento criado com sucesso.');
    }

    public function edit(Appointment $appointment)
    {
        abort_if($appointment->employee_id !== $this->myEmployeeId(), 403);

        return Inertia::render('Collaborator/Appointments/Edit', [
            'appointment'  => $appointment->load(['client', 'service']),
            'services'     => Service::where('active', true)->get(['id', 'name', 'price', 'duration_minutes']),
            'myEmployeeId' => $this->myEmployeeId(),
        ]);
    }

    public function update(UpdateAppointmentRequest $request, Appointment $appointment, AppointmentService $service)
    {
        abort_if($appointment->employee_id !== $this->myEmployeeId(), 403);

        $data = $request->validated();
        $data['employee_id'] = $this->myEmployeeId();

        $service->update($appointment, $data);

        return redirect()->route('collaborator.appointments.index')
            ->with('success', 'Agendamento atualizado com sucesso.');
    }

    public function destroy(Appointment $appointment)
    {
        abort_if($appointment->employee_id !== $this->myEmployeeId(), 403);

        $appointment->delete();

        return redirect()->route('collaborator.appointments.index')
            ->with('success', 'Agendamento removido.');
    }
}
