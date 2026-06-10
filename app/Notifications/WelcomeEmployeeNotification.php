<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeEmployeeNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $temporaryPassword
    ) {}

    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Bem-vindo ao ' . config('app.name') . ' — Seus dados de acesso')
            ->greeting('Olá, ' . $notifiable->name . '!')
            ->line('Sua conta de colaborador foi criada no sistema ' . config('app.name') . '.')
            ->line('**Dados de acesso:**')
            ->line('E-mail: ' . $notifiable->email)
            ->line('Senha temporária: ' . $this->temporaryPassword)
            ->action('Acessar o sistema', url('/login'))
            ->line('Recomendamos que você altere sua senha após o primeiro acesso.')
            ->salutation('Atenciosamente, ' . config('app.name'));
    }
}
