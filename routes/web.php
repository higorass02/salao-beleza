<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\CashClosingController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\WeeklyClosingController;
use Illuminate\Support\Facades\Route;

require __DIR__.'/auth.php';

Route::middleware(['auth'])->group(function () {
    Route::get('/', [AppointmentController::class, 'dashboard'])->name('dashboard');

    Route::resource('employees', EmployeeController::class)->except(['show']);
    Route::resource('services', ServiceController::class)->except(['show']);
    Route::get('clients/search', [ClientController::class, 'search'])->name('clients.search');
    Route::resource('clients', ClientController::class)->except(['show']);
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
