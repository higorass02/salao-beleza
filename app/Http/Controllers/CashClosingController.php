<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCashEntryRequest;
use App\Models\Appointment;
use App\Models\CashEntry;
use App\Models\DailyClosing;
use App\Models\Employee;
use App\Models\ProviderDailyClosing;
use App\Models\Setting;
use App\Services\CashClosingService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CashClosingController extends Controller
{
    public function index()
    {
        return Inertia::render('Cash/Daily', [
            'date'          => null,
            'dailyClosing'  => null,
            'appointments'  => [],
            'entries'       => [],
            'preview'       => null,
            'settings'      => Setting::allAsArray(),
        ]);
    }

    public function show(string $date, CashClosingService $service)
    {
        $validated = $this->validateDate($date);

        $dailyClosing = DailyClosing::where('date', $validated)->first();

        $appointments = Appointment::with(['client', 'service', 'employee'])
            ->whereDate('starts_at', $validated)
            ->where('status', '!=', 'canceled')
            ->orderBy('starts_at')
            ->get()
            ->map(fn ($a) => [
                'id'               => $a->id,
                'starts_at'        => $a->starts_at->format('H:i'),
                'client_name'      => $a->client?->name,
                'service_name'     => $a->service?->name,
                'service_value'    => (float) ($a->service?->price ?? 0),
                'include_house_fee'=> (bool)  ($a->service?->include_house_fee ?? false),
                'paid_to'          => $a->paid_to,
                'employee_id'      => $a->employee_id,
                'employee_name'    => $a->employee?->name,
                'type'             => 'appointment',
            ]);

        $entries = CashEntry::with('employee')
            ->where('date', $validated)
            ->orderBy('performed_at')
            ->get()
            ->map(fn ($e) => [
                'id'               => $e->id,
                'starts_at'        => $e->performed_at,
                'client_name'      => $e->client_name,
                'service_name'     => $e->service_name,
                'service_value'    => (float) $e->service_value,
                'include_house_fee'=> (bool)  $e->include_house_fee,
                'paid_to'          => $e->paid_to,
                'employee_id'      => $e->employee_id,
                'employee_name'    => $e->employee?->name,
                'type'             => 'entry',
            ]);

        $preview = $service->computeDay($validated);

        $providerClosings = ProviderDailyClosing::where('date', $validated)
            ->get()
            ->keyBy('employee_id')
            ->map(fn ($c) => [
                'closed_at'            => $c->closed_at->format('d/m/Y H:i'),
                'total_services'       => (float) $c->total_services,
                'house_fee'            => (float) $c->house_fee,
                'provider_share'       => (float) $c->provider_share,
                'received_by_provider' => (float) $c->received_by_provider,
                'received_by_store'    => (float) $c->received_by_store,
                'net'                  => (float) $c->net,
            ]);

        return Inertia::render('Cash/Daily', [
            'date'             => $validated,
            'dailyClosing'     => $dailyClosing,
            'appointments'     => $appointments,
            'entries'          => $entries,
            'preview'          => $preview,
            'settings'         => Setting::allAsArray(),
            'providerClosings' => $providerClosings,
            'employees'        => Employee::where('active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function storeEntry(string $date, StoreCashEntryRequest $request)
    {
        $this->validateDate($date);
        $this->abortIfClosed($date);

        CashEntry::create(array_merge($request->validated(), ['date' => $date]));

        return redirect()->route('cash.daily.show', $date)
            ->with('success', 'Entrada adicionada.');
    }

    public function updateAppointmentPaidTo(string $date, Appointment $appointment, Request $request)
    {
        $this->validateDate($date);
        $request->validate(['paid_to' => ['nullable', 'in:provider,store']]);
        $appointment->update(['paid_to' => $request->paid_to]);
        return back();
    }

    public function updateEntryPaidTo(string $date, CashEntry $entry, Request $request)
    {
        $this->validateDate($date);
        $request->validate(['paid_to' => ['nullable', 'in:provider,store']]);
        $entry->update(['paid_to' => $request->paid_to]);
        return back();
    }

    public function destroyEntry(string $date, CashEntry $entry)
    {
        $this->abortIfClosed($date);
        $entry->delete();
        return redirect()->route('cash.daily.show', $date)->with('success', 'Entrada removida.');
    }

    public function closeProvider(string $date, Employee $employee, CashClosingService $service)
    {
        $this->validateDate($date);
        $this->abortIfClosed($date);

        abort_if(
            ProviderDailyClosing::where('date', $date)->where('employee_id', $employee->id)->exists(),
            422,
            'O caixa deste prestador já foi fechado para este dia.'
        );

        $service->closeProvider($date, $employee->id);

        return redirect()->route('cash.daily.show', $date)
            ->with('success', "Caixa de {$employee->name} fechado com sucesso!");
    }

    public function reopenProvider(string $date, Employee $employee)
    {
        $this->validateDate($date);

        ProviderDailyClosing::where('date', $date)
            ->where('employee_id', $employee->id)
            ->delete();

        return redirect()->route('cash.daily.show', $date)
            ->with('success', "Caixa de {$employee->name} reaberto para revisão.");
    }

    public function reopen(string $date)
    {
        $this->validateDate($date);

        DailyClosing::where('date', $date)
            ->update(['status' => 'open', 'closed_at' => null]);

        return redirect()->route('cash.daily.show', $date)
            ->with('success', 'Caixa reaberto para revisão.');
    }

    public function recalculate(string $date, CashClosingService $service)
    {
        $this->validateDate($date);

        $closing = DailyClosing::where('date', $date)->firstOrFail();

        $totals = $service->computeDay($date);
        $closing->update($totals);

        return redirect()->route('cash.daily.show', $date)
            ->with('success', 'Caixa recalculado com sucesso!');
    }

    public function closeDay(string $date, CashClosingService $service)
    {
        $this->validateDate($date);

        $service->closeDay($date);

        return redirect()->route('cash.daily.show', $date)
            ->with('success', 'Caixa do dia fechado com sucesso!');
    }

    private function validateDate(string $date): string
    {
        abort_if(! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date), 422, 'Data inválida.');
        return $date;
    }

    private function abortIfClosed(string $date): void
    {
        $closing = DailyClosing::where('date', $date)->first();
        abort_if($closing?->status === 'closed', 422, 'O caixa deste dia já foi fechado.');
    }
}
