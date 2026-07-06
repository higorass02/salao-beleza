<?php

namespace App\Http\Controllers\Collaborator;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCollaboratorBlockedSlotRequest;
use App\Models\Appointment;
use App\Services\BlockedSlotService;
use Inertia\Inertia;

class BlockedSlotController extends Controller
{
    public function __construct(private BlockedSlotService $service) {}

    public function index()
    {
        $employeeId = auth()->user()->employee_id;

        $blockedSlots = Appointment::where('status', 'blocked')
            ->where('employee_id', $employeeId)
            ->orderBy('starts_at', 'desc')
            ->get();

        return Inertia::render('Collaborator/BlockedSlots/Index', [
            'blockedSlots' => $blockedSlots,
        ]);
    }

    public function store(StoreCollaboratorBlockedSlotRequest $request)
    {
        $employeeId = auth()->user()->employee_id;
        $data       = $request->validated();

        $result = $this->service->block([
            'employee_id' => $employeeId,
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

        return redirect()->route('collaborator.blocked-slots.index')
            ->with('success', 'Bloqueio criado com sucesso.');
    }

    public function destroy(Appointment $slot)
    {
        $employeeId = auth()->user()->employee_id;

        abort_unless($slot->status === 'blocked', 403, 'Não é um bloqueio.');
        abort_unless($slot->employee_id === $employeeId, 403, 'Sem permissão.');

        $this->service->destroy($slot);

        return redirect()->route('collaborator.blocked-slots.index')
            ->with('success', 'Bloqueio removido.');
    }
}
