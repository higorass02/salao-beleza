<?php

namespace App\Notifications;

use App\Models\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class ClientDeactivatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Client     $client,
        public Collection $cancelledAppointments
    ) {}

    public function via(object $notifiable): array
    {
        return ["mail"];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject("[" . config("app.name") . "] Cliente desativado — " . $this->client->name)
            ->greeting("Olá, " . $notifiable->name . "!")
            ->line("O cliente **" . $this->client->name . "** foi desativado no sistema.");

        if ($this->cancelledAppointments->isNotEmpty()) {
            $mail->line("**" . $this->cancelledAppointments->count() . " agendamento(s) futuro(s) foram cancelados automaticamente:**");
            foreach ($this->cancelledAppointments as $appt) {
                $mail->line("• " . $appt["starts_at"] . " — " . $appt["service"] . " com " . $appt["employee"]);
            }
        } else {
            $mail->line("Não havia agendamentos futuros para este cliente.");
        }

        return $mail
            ->action("Ver agendamentos", url("/appointments"))
            ->salutation("Atenciosamente, " . config("app.name"));
    }
}
