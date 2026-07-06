<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBlockedSlotRequest;
use App\Models\Appointment;
use App\Services\BlockedSlotService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BlockedSlotController extends Controller
{
    public function __construct(private BlockedSlotService $service) {}

    public function index(Request $request)
    {
        $query = Appointment::with('employee')
            ->where('status', 'blocked')
            ->orderBy('starts_at', 'desc');

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        return Inertia::render('Admin/BlockedSlots/Index', [
            'blockedSlots' => $query->get(),
            'employees'    => \App\Models\Employee::where('active', true)->orderBy('name')->get(['id', 'name']),
            'filters'      => $request->only('employee_id'),
        ]);
    }

    public function store(StoreBlockedSlotRequest $request)
    {
        $data   = $request->validated();
        $result = $this->service->block([
            'employee_id' => $data['employee_id'],
            'starts_at'   => $data['starts_at'] . ':00',
            'ends_at'     => $data['ends_at'] . ':00',
            'notes'       => $data['notes'] ?? null,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'slot'      => $result['slot'],
                'conflicts' => $result['conflicts']->values(),
            ]);
        }

        return redirect()->route('admin.blocked-slots.index')
            ->with('success', 'Bloqueio criado com sucesso.');
    }

    public function destroy(Appointment $slot)
    {
        abort_unless($slot->status === 'blocked', 403, 'Não é um bloqueio.');
        $this->service->destroy($slot);

        return redirect()->route('admin.blocked-slots.index')
            ->with('success', 'Bloqueio removido.');
    }

    public function appointmentsInRange(Request $request)
    {
        $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'starts_at'   => ['required', 'date'],
            'ends_at'     => ['required', 'date', 'after:starts_at'],
        ]);

        $conflicts = $this->service->getConflictingForEmployee(
            (int) $request->employee_id,
            $request->starts_at,
            $request->ends_at,
        );

        return response()->json(['appointments' => $conflicts->values()]);
    }
}
