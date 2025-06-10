<?php

namespace Database\Factories;

use App\Models\Notification;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition()
    {
        return [
            'appointment_id' => \App\Models\Appointment::factory(),
            'message' => $this->faker->sentence,
            'read' => $this->faker->boolean,
        ];
    }
}