<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Laravel\Passport\ClientRepository;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        //Тестовый пользователь
        User::factory()->create([
            'name' => 'Тест',
            'email' => 'test@boardy.local',
            'password' => bcrypt('password'),
        ]);

        //4 случайных пользователя
        $users = User::factory()->count(4)->create();

        //10 постов от случайных пользователей (включая тестового)
        $allUsers = User::all();

        Post::factory()->count(10)->create([
            //Lazy-resolution: функция выполнится при создании каждой записи
            'user_id' => fn() => $allUsers->random()->id,
        ]);

        //25 комментариев к случайным постам от случайных авторов
        $posts = Post::all();
        $commentAuthors = User::all();

        Comment::factory()->count(25)->create([
            'post_id' => fn() => $posts->random()->id,
            'user_id' => fn() => $commentAuthors->random()->id,
        ]);
    }
}
