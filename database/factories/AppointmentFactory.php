<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'service' => 'Blood donation screening',
            'appointment_date' => fake()->dateTimeBetween('tomorrow', '+1 month')->format('Y-m-d'),
            'fee' => 500,
            'payment_method' => 'Cash',
            'payment_status' => 'Pending',
            'status' => 'Pending',
        ];
    }
}
