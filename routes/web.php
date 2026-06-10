<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\Auth\ChangePasswordController;
use App\Http\Controllers\CashClosingController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\WeeklyClosingController;
use Illuminate\Support\Facades\Route;

require __DIR__.'/auth.php';

// Página pública (landing page)
Route::get('/', function () {
    return inertia('Landing');
})->name('landing');

// -------------------------------------------------------
// -------------------------------------------------------
// Rotas compartilhadas — qualquer usuário autenticado
// (não exigem must_change_password = false)
// -------------------------------------------------------
Route::middleware(['auth'])->group(function () {
    Route::get('/change-password', [ChangePasswordController::class, 'edit'])->name('password.change');
    Route::put('/change-password', [ChangePasswordController::class, 'update'])->name('password.update');
});

// -------------------------------------------------------
// Clientes — acessível por admin e colaborador
// -------------------------------------------------------
Route::middleware(['auth', 'password.changed'])->group(function () {
    Route::get('clients/search', [ClientController::class, 'search'])->name('clients.search');
    Route::resource('clients', ClientController::class)->except(['show']);
    Route::put('clients/{client}/activate', [ClientController::class, 'activate'])->name('clients.activate');
});

// -------------------------------------------------------
// Rotas de Colaborador (prestador de serviço logado)
// -------------------------------------------------------
Route::middleware(['auth', 'collaborator', 'password.changed'])->prefix('collaborator')->name('collaborator.')->group(function () {
    Route::get('/', \App\Http\Controllers\Collaborator\DashboardController::class)->name('dashboard');
    Route::get('/calendar', [\App\Http\Controllers\Collaborator\CalendarController::class, 'index'])->name('calendar');

    Route::resource('appointments', \App\Http\Controllers\Collaborator\AppointmentController::class)
        ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

    Route::get('/settings', [\App\Http\Controllers\Collaborator\SettingsController::class, 'edit'])->name('settings');
    Route::put('/settings', [\App\Http\Controllers\Collaborator\SettingsController::class, 'update'])->name('settings.update');
});

// -------------------------------------------------------
// Rotas Admin
// -------------------------------------------------------
Route::middleware(['auth', 'admin', 'password.changed'])->group(function () {
    Route::get('/dashboard', [AppointmentController::class, 'dashboard'])->name('dashboard');

    Route::resource('employees', EmployeeController::class)->except(['show']);
    Route::resource('services', ServiceController::class)->except(['show']);
    Route::resource('appointments', AppointmentController::class)->only(['index', 'store', 'edit', 'update', 'destroy']);

    // Fechamento de caixa diário
    Route::prefix('cash')->name('cash.')->group(function () {
        Route::get('/daily', [CashClosingController::class, 'index'])->name('daily.index');
        Route::get('/daily/{date}', [CashClosingController::class, 'show'])->name('daily.show');
        Route::post('/daily/{date}/entries', [CashClosingController::class, 'storeEntry'])->name('daily.entries.store');
        Route::patch('/daily/{date}/appointments/{appointment}', [CashClosingController::class, 'updateAppointmentPaidTo'])->name('daily.appointments.update');
        Route::patch('/daily/{date}/entries/{entry}', [CashClosingController::class, 'updateEntryPaidTo'])->name('daily.entries.update');
        Route::delete('/daily/{date}/entries/{entry}', [CashClosingController::class, 'destroyEntry'])->name('daily.entries.destroy');
        Route::post('/daily/{date}/providers/{employee}/close', [CashClosingController::class, 'closeProvider'])->name('daily.providers.close');
        Route::post('/daily/{date}/providers/{employee}/reopen', [CashClosingController::class, 'reopenProvider'])->name('daily.providers.reopen');
        Route::post('/daily/{date}/close', [CashClosingController::class, 'closeDay'])->name('daily.close');
        Route::post('/daily/{date}/reopen', [CashClosingController::class, 'reopen'])->name('daily.reopen');
        Route::post('/daily/{date}/recalculate', [CashClosingController::class, 'recalculate'])->name('daily.recalculate');
    });

    // Fechamento semanal
    Route::get('/weekly', [WeeklyClosingController::class, 'index'])->name('weekly.index');
    Route::post('/weekly/close', [WeeklyClosingController::class, 'close'])->name('weekly.close');
    Route::post('/weekly/recalculate', [WeeklyClosingController::class, 'recalculate'])->name('weekly.recalculate');

    // Configurações
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
});
