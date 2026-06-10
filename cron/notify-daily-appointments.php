<?php

/**
 * Cron: Notificações diárias de agendamentos
 *
 * Hostinger: PHP — salao-beleza/cron/notify-daily-appointments.php
 * Schedule:  0 9 * * *  (todo dia às 09:00)
 */

define('LARAVEL_START', microtime(true));

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$status = $kernel->call('appointments:notify-daily');

$kernel->terminate(new Illuminate\Http\Request(), $status);
