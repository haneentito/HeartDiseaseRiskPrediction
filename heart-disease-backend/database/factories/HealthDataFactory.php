<?php

namespace Database\Factories;

use App\Models\HealthData;
use Illuminate\Database\Eloquent\Factories\Factory;

class HealthDataFactory extends Factory
{
    protected $model = HealthData::class;

    public function definition()
    {
        return [
            'patient_id' => \App\Models\Patient::factory(),
            'age' => $this->faker->numberBetween(18, 80),
            'gender' => $this->faker->randomElement(['male', 'female']),
            'cholesterol' => $this->faker->numberBetween(150, 300),
            'blood_pressure_systolic' => $this->faker->numberBetween(90, 180),
            'blood_pressure_diastolic' => $this->faker->numberBetween(60, 120),
            'smoking' => $this->faker->boolean,
            'diabetes' => $this->faker->boolean,
            'bmi' => $this->faker->randomFloat(2, 18, 40),
            'heart_rate' => $this->faker->numberBetween(60, 100),
            'family_history' => $this->faker->boolean,
            'prediction_score' => $this->faker->randomFloat(2, 0, 100),
        ];
    }
}