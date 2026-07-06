<?php

namespace App\Http\Controllers\PublicBooking;

use App\Actions\Appointment\PublicBookingAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePublicBookingRequest;
use App\Models\Appointment;
use App\Models\Employee;
use App\Models\Service;
use App\Models\Setting;
use App\Services\AvailableSlotsService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BookingController extends Controller
{
    public function __construct(
        private AvailableSlotsService $slotsService,
        private PublicBookingAction   $bookingAction,
    ) {}

    public function servicesIndex()
    {
        $services = Service::where('active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'description', 'price', 'duration_minutes']);

        return Inertia::render('Booking/Step1Service', compact('services'));
    }

    public function employeesByService(Service $service)
    {
        $employees = Employee::where('active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'role']);

        return Inertia::render('Booking/Step2Employee', [
            'service'   => $service->only('id', 'name', 'price', 'duration_minutes'),
            'employees' => $employees,
        ]);
    }

    public function availableSlots(Request $request)
    {
        $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'service_id'  => ['required', 'exists:services,id'],
            'date'        => ['required', 'date_format:Y-m-d'],
        ]);

        $slots = $this->slotsService->getSlots(
            (int) $request->employee_id,
            (int) $request->service_id,
            $request->date,
            Setting::get('business_hours_start', '09:00'),
            Setting::get('business_hours_end', '18:00'),
        );

        return response()->json($slots);
    }

    public function showConfirm(Request $request)
    {
        return Inertia::render('Booking/Step4Confirm', [
            'employee_id' => (int) $request->employee_id,
            'service_id'  => (int) $request->service_id,
            'starts_at'   => $request->starts_at,
        ]);
    }

    public function store(StorePublicBookingRequest $request)
    {
        $appointment = $this->bookingAction->execute($request->validated());

        return redirect()->route('booking.confirmed', $appointment->id)
            ->with('success', 'Agendamento realizado com sucesso!');
    }

    public function confirmed(Appointment $appointment)
    {
        $appointment->load(['client', 'employee', 'service']);

        return Inertia::render('Booking/Confirmed', [
            'appointment' => [
                'id'          => $appointment->id,
                'starts_at'   => $appointment->starts_at,
                'ends_at'     => $appointment->ends_at,
                'client_name' => $appointment->client?->name,
                'employee'    => $appointment->employee?->name,
                'service'     => $appointment->service?->name,
            ],
        ]);
    }
}
