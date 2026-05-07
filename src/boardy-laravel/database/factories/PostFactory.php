<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),

            //Генерация контента через Faker
            'title' => fake()->sentence(4),
            'body'  => fake()->paragraphs(3, true),   // 3 абзаца с <p> тегами
        ];
    }
}
