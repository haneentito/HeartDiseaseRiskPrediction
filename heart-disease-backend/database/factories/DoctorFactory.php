<?php

namespace Database\Factories;

use App\Models\Doctor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class DoctorFactory extends Factory
{
    protected $model = Doctor::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => Hash::make('password123'),
            'phone' => $this->faker->phoneNumber,
            'address' => $this->faker->address,
            'specialty' => 'Cardiology',
            'rating' => $this->faker->randomFloat(1, 1, 5),
            'views' => $this->faker->numberBetween(0, 1000),
        ];
    }
}
