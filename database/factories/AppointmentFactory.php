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
        $defaultService = array_values(config('appointments.services'))[0];
        $defaultPaymentMethod = array_key_first(config('appointments.payment_methods'));

        return [
            'user_id' => User::factory(),
            'service' => $defaultService['name'],
            'appointment_date' => fake()->dateTimeBetween('tomorrow', '+1 month')->format('Y-m-d'),
            'fee' => $defaultService['fee'],
            'payment_method' => $defaultPaymentMethod,
            'payment_status' => 'Pending',
            'status' => 'Pending',
        ];
    }
}
