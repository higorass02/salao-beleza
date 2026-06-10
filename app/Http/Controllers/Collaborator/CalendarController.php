<?php

namespace App\Http\Controllers\Collaborator;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Employee;
use App\Models\Service;
use App\Models\Setting;
use App\Services\AppointmentService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CalendarController extends Controller
{
    public function index(AppointmentService $service)
    {
        $employeeId = Auth::user()->employee_id;

        $from = Carbon::now()->startOfWeek()->subWeeks(4)->toDateTimeString();
        $to   = Carbon::now()->endOfWeek()->addWeeks(8)->toDateTimeString();

        $appointments = $service->listForCalendar($from, $to, $employeeId);

        return Inertia::render('Collaborator/Calendar', [
            'appointments'       => $appointments,
            'services'           => Service::where('active', true)->get(['id', 'name', 'price', 'duration_minutes']),
            'employees'          => Employee::where('active', true)->get(['id', 'name', 'role']),
            'myEmployeeId'       => $employeeId,
            'businessHoursStart' => Setting::get('business_hours_start', '08:00'),
            'businessHoursEnd'   => Setting::get('business_hours_end', '20:00'),
        ]);
    }
}
