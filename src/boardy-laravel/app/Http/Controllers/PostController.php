<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PostController extends Controller
{
    use AuthorizesRequests;
    // GET /posts — список всех постов
    public function index()
    {
        $posts = Post::with('author')->latest()->paginate(10);
        return view('posts.index', compact('posts'));
    }

    // GET /posts/create — форма создания
    public function create()
    {
        return view('posts.create');
    }

    // POST /posts — сохранение нового поста
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body'  => 'required|string',
        ]);

        // Автоматически привязываем текущего пользователя
        $request->user()->posts()->create($validated);

        return redirect()->route('posts.index')
            ->with('success', 'Пост создан!');
    }

    // GET /posts/{post} — показ одного поста
    public function show(Post $post)
    {
        // 🔗 Загружаем автора и комментарии с пагинацией
        $post->load(['author', 'comments.author']);
        return view('posts.show', compact('post'));
    }

    // GET /posts/{post}/edit — форма редактирования
    public function edit(Post $post)
    {
        // 🔐 Проверка прав: только автор может редактировать
        $this->authorize('update', $post);
        return view('posts.edit', compact('post'));
    }

    // PUT/PATCH /posts/{post} — обновление
    public function update(Request $request, Post $post)
    {
        $this->authorize('update', $post);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body'  => 'required|string',
        ]);

        $post->update($validated);

        return redirect()->route('posts.show', $post)
            ->with('success', 'Пост обновлён!');
    }

    // DELETE /posts/{post} — удаление
    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);

        $post->delete();

        return redirect()->route('posts.index')
            ->with('success', 'Пост удалён!');
    }
}
