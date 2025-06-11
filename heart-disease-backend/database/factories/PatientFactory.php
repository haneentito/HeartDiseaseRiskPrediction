<?php

namespace Database\Factories;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class PatientFactory extends Factory
{
    protected $model = Patient::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => Hash::make('password123'),
            'phone' => $this->faker->phoneNumber,
            'date_of_birth' => $this->faker->date('Y-m-d', '2000-01-01'),
            'gender' => $this->faker->randomElement(['male', 'female']),
        ];
    }
}
