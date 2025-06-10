<?php

namespace Database\Factories;

use App\Models\AiLog;
use Illuminate\Database\Eloquent\Factories\Factory;

class AiLogFactory extends Factory
{
    protected $model = AiLog::class;

    public function definition()
    {
        return [
            'patient_id' => \App\Models\Patient::factory(),
            'health_data_id' => \App\Models\HealthData::factory(),
            'prediction_score' => $this->faker->randomFloat(2, 0, 100),
            'input_data' => json_encode(['age' => 45, 'cholesterol' => 200]),
            'model_version' => '1.0.0',
        ];
    }
}