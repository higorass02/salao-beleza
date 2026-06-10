<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Notificação diária de agendamentos — dispara às 09h todos os dias
// O próprio comando valida se 09h está dentro do horário de funcionamento configurado
Schedule::command('appointments:notify-daily')
    ->dailyAt('09:00')
    ->withoutOverlapping()
    ->runInBackground();
