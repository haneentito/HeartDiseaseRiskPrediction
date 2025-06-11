<?php

namespace Database\Factories;

use App\Models\Schedule;
use Illuminate\Database\Eloquent\Factories\Factory;

class ScheduleFactory extends Factory
{
    protected $model = Schedule::class;

    public function definition()
    {
        return [
            'doctor_id' => \App\Models\Doctor::factory(),
            'day' => $this->faker->dayOfWeek,
            'time' => $this->faker->randomElement(['3:4', '4:5', '5:6', '6:7', '7:8']),
            'available' => $this->faker->boolean,
        ];
    }
}