<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class DailyAppointmentsNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string     $employeeName,
        public string     $date,
        public Collection $appointments
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('[' . config('app.name') . '] Sua agenda de hoje — ' . $this->date)
            ->greeting('Bom dia, ' . $this->employeeName . '!')
            ->line('Você tem **' . $this->appointments->count() . ' agendamento(s)** hoje, ' . $this->date . ':');

        foreach ($this->appointments as $appt) {
            $mail->line('• **' . $appt['time'] . '** — ' . $appt['client'] . ' (' . $appt['service'] . ')');
        }

        return $mail
            ->action('Ver minha agenda', url('/collaborator/calendar'))
            ->salutation('Atenciosamente, ' . config('app.name'));
    }
}
