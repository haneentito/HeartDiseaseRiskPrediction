<?php

namespace Database\Factories;

use App\Models\Appointment;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition()
    {
        return [
            'doctor_id' => \App\Models\Doctor::factory(),
            'patient_id' => \App\Models\Patient::factory(),
            'schedule_id' => \App\Models\Schedule::factory(),
            'day' => $this->faker->dayOfWeek,
            'time' => $this->faker->randomElement(['3:4', '4:5', '5:6', '6:7', '7:8']),
            'status' => $this->faker->randomElement(['pending', 'accepted', 'declined', 'completed']),
        ];
    }
}
