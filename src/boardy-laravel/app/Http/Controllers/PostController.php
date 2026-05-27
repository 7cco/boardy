<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;


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

    public function store(Request $request)
    {
	
	    $validated = $request->validate([
            'title' => 'required|max:255',
            'body'  => 'required',
        ]);

        $post = Post::create([ 
	    ...$validated, 
        'user_id' => auth()->id(), 
    	]);	

        $payload = json_encode([
        'id'         => $post->id,
        'title'      => $post->title,
        'body'       => $post->body,
        'author'     => auth()->user()->name,
        'created_at' => $post->created_at->toISOString(),
        ]);

        \Log::info('Attempting Redis publish', [
        'channel' => 'new_post',
        'payload' => $payload
        ]);

        $result = Redis::publish('new_post', json_encode([
            'id'         => $post->id,
            'title'      => $post->title,
            'body'       => $post->body,
            'author'     => auth()->user()->name,
            'created_at' => $post->created_at->toISOString(),
        ]));

        \Log::info('Redis publish result', ['result' => $result]);
    
        if ($result === 0) {
            \Log::warning('Redis publish: no subscribers');
        }
        return redirect('/posts');
    }

    // GET /posts/{post} — показ одного поста
    public function show(Post $post)
    {
        //Загружаем автора и комментарии с пагинацией
        $post->load(['author', 'comments.author']);
        return view('posts.show', compact('post'));
    }

    // GET /posts/{post}/edit — форма редактирования
    public function edit(Post $post)
    {
        //Проверка прав: только автор может редактировать
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

        \Log::info('Publishing post_updated', ['post_id' => $post->id]);
    
        Redis::publish('post_updated', json_encode([
            'id' => $post->id,
            'title' => $post->title,
            'body' => $post->body,
            'author' => $post->author->name,
            'updated_at' => $post->updated_at->toISOString(),
        ]));

        return redirect()->route('posts.show', $post)
            ->with('success', 'Пост обновлён!');
    }

    // DELETE /posts/{post} — удаление
    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);

        $postId = $post->id; // Сохраняем ID до удаления
        $post->delete();

        \Log::info('Publishing post_deleted', ['post_id' => $postId]);
        Redis::publish('post_deleted', json_encode([
            'id' => $postId,
            'deleted_at' => now()->toISOString(),
        ]));

        return redirect()->route('posts.index')
            ->with('success', 'Пост удалён!');
    }
}
