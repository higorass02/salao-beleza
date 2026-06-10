<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\Employee;
use App\Models\Setting;
use App\Notifications\DailyAppointmentsNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;

class SendDailyAppointmentNotifications extends Command
{
    protected $signature   = "appointments:notify-daily";
    protected $description = "Envia e-mail para cada funcionario com seus agendamentos do dia (dispara as 09h)";

    public function handle(): int
    {
        $now   = Carbon::now();
        $today = $now->toDateString();

        $start = Setting::get("business_hours_start", "08:00");
        $end   = Setting::get("business_hours_end",   "20:00");

        if ($now->format("H:i") < $start || $now->format("H:i") > $end) {
            $this->info("Fora do horario de funcionamento ({$start}x{$end}). Nenhuma notificacao enviada.");
            return self::SUCCESS;
        }

        $employees = Employee::with("user")
            ->where("active", true)
            ->where("notify_appointments", true)
            ->get();

        $sent = 0;

        foreach ($employees as $employee) {
            if ($employee->user && $employee->user->notifications_enabled === false) {
                continue;
            }

            $appointments = Appointment::with(["client", "service"])
                ->where("employee_id", $employee->id)
                ->whereDate("starts_at", $today)
                ->where("status", "scheduled")
                ->orderBy("starts_at")
                ->get();

            if ($appointments->isEmpty()) {
                continue;
            }

            $list = $appointments->map(fn ($a) => [
                "time"    => $a->starts_at->format("H:i"),
                "client"  => $a->client?->name  ?? "—",
                "service" => $a->service?->name ?? "—",
            ]);

            $dateLabel = Carbon::today()->translatedFormat("d/m/Y");

            Notification::route("mail", $employee->email)
                ->notify(new DailyAppointmentsNotification(
                    employeeName:  $employee->name,
                    date:          $dateLabel,
                    appointments:  $list,
                ));

            $sent++;
            $this->line("  ok {$employee->name} — {$appointments->count()} agendamento(s)");
        }

        $this->info("Notificacoes enviadas: {$sent}");

        return self::SUCCESS;
    }
}
