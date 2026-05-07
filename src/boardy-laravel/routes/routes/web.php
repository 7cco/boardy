<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\GitHubController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

// 🔹 Главная → посты
Route::get('/', function () {
    return redirect()->route('posts.index');
});

// 🔹 Дашборд (из Breeze)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// 🔹 Профиль
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// 🔹 Подключаем маршруты аутентификации от Breeze
require __DIR__.'/auth.php';

// 🔹 GitHub OAuth
Route::get('/auth/github', [GitHubController::class, 'redirect'])->name('auth.github');
Route::get('/auth/github/callback', [GitHubController::class, 'callback'])->name('auth.github.callback');

// 🔹 Посты (7 маршрутов)
Route::resource('posts', PostController::class);

// 🔹 Комментарии (только для авторизованных)
Route::middleware('auth')->group(function () {
    Route::post('comments', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
});
