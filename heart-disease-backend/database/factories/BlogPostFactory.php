<?php

namespace Database\Factories;

use App\Models\BlogPost;
use App\Models\Doctor;
use Illuminate\Database\Eloquent\Factories\Factory;

class BlogPostFactory extends Factory
{
    protected $model = BlogPost::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence,
            'content' => $this->faker->paragraphs(3, true),
            'author_id' => Doctor::factory(),
            'author_type' => 'doctor',
            'image' => $this->faker->imageUrl(),
        ];
    }
}
