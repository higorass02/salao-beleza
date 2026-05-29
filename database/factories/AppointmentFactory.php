<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\Employee;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition()
    {
        $startsAt        = Carbon::instance($this->faker->dateTimeBetween('+1 day', '+30 days'));
        $durationMinutes = $this->faker->numberBetween(30, 120);

        return [
            'client_id'   => Client::factory(),
            'employee_id' => Employee::factory(),
            'service_id'  => Service::factory(),
            'starts_at'   => $startsAt,
            'ends_at'     => $startsAt->copy()->addMinutes($durationMinutes),
            'status'      => 'scheduled',
            'notes'       => $this->faker->optional()->sentence,
        ];
    }

    public function canceled(): static
    {
        return $this->state(['status' => 'canceled']);
    }

    public function done(): static
    {
        return $this->state(['status' => 'done']);
    }
}
