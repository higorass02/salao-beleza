<?php

namespace App\Providers;

use App\Repositories\AppointmentRepository;
use App\Repositories\Contracts\AppointmentRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(AppointmentRepositoryInterface::class, AppointmentRepository::class);
    }

    public function boot()
    {
        //
    }
}
