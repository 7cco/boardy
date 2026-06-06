<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\GitHubController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

Route::get('/health', fn () => response()->json(['ok' => true]));
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

Route::get('/oauth/callback', function () {
    return view('auth.oauth.callback');
})->name('oauth.callback')->middleware('web');

// 🔹 Passport OAuth маршруты (PKCE flow)
Route::post('/oauth/token', [\Laravel\Passport\Http\Controllers\AccessTokenController::class, 'issueToken'])
    ->middleware('throttle')
    ->name('passport.token');

Route::get('/oauth/authorize', [\Laravel\Passport\Http\Controllers\AuthorizationController::class, 'authorize'])
    ->middleware(['auth'])
    ->name('passport.authorize');

Route::post('/oauth/authorize', [\Laravel\Passport\Http\Controllers\ApproveAuthorizationController::class, 'approve'])
    ->middleware(['auth'])
    ->name('passport.authorizations.approve');

Route::delete('/oauth/authorize', [\Laravel\Passport\Http\Controllers\DenyAuthorizationController::class, 'deny'])
    ->middleware(['auth'])
    ->name('passport.authorizations.deny');

Route::post('/test-csrf', function () {
    return response()->json(['message' => 'CSRF check passed!']);
});

Route::get('/api/posts/{post}/comments-count', function ($postId) {
    try {
        $count = DB::connection('mysql_api')
            ->table('comments')
            ->where('post_id', $postId)
            ->count();
        
        return response()->json(['count' => $count]);
    } catch (\Exception $e) {
        \Log::error('Ошибка получения счётчика комментариев: ' . $e->getMessage());
        return response()->json(['count' => 0], 500);
    }
})->name('api.posts.comments-count');