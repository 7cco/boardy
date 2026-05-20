@extends('layouts.app')

@section('title', $post->title)

@section('content')
    {{-- Отображение поста --}}
    <article style="padding:1.5rem;border:1px solid #eee;border-radius:4px;margin-bottom:2rem;">
        <h1 style="margin:0 0 0.5rem;">{{ $post->title }}</h1>
        
        <div style="color:#666;font-size:0.9rem;margin-bottom:1rem;">
            Автор: <strong>{{ $post->author->name }}</strong> · 
            {{ $post->created_at->format('d.m.Y H:i') }}
            @if ($post->created_at != $post->updated_at)
                · обновлено {{ $post->updated_at->format('d.m.Y H:i') }}
            @endif
        </div>

        <div style="white-space:pre-wrap;line-height:1.6;">
            {{ $post->body }}
        </div>

        {{-- Кнопки редактирования/удаления — только для автора поста --}}
        @can('update', $post)
            <div style="margin-top:1rem;padding-top:1rem;border-top:1px solid #eee;">
                <a href="{{ route('posts.edit', $post) }}" 
                   style="display:inline-block;padding:0.5rem 1rem;background:#007bff;color:white;text-decoration:none;border-radius:4px;">
                    Редактировать
                </a>
                <form action="{{ route('posts.destroy', $post) }}" method="POST" 
                      style="display:inline;margin-left:0.5rem;"
                      onsubmit="return confirm('Удалить пост?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            style="padding:0.5rem 1rem;background:#dc3545;color:white;border:none;border-radius:4px;cursor:pointer;">
                        Удалить
                    </button>
                </form>
            </div>
        @endcan
    </article>

    {{-- Комментарии --}}
    <div class="comments">
        <h3>Комментарии ({{ $post->comments->count() }})</h3>

        @forelse ($post->comments as $comment)
            <div class="comment" style="padding:1rem;background:#f9f9f9;border-radius:4px;margin-bottom:0.75rem;">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <strong>{{ $comment->author->name }}</strong>
                    <small style="color:#666;">{{ $comment->created_at->format('d.m.Y H:i') }}</small>
                </div>
                <p style="margin:0.5rem 0 0;">{{ $comment->body }}</p>

                {{-- Кнопка удаления — только для автора комментария --}}
                @can('delete', $comment)
                    <form action="{{ route('comments.destroy', $comment) }}" method="POST"
                          onsubmit="return confirm('Удалить комментарий?')"
                          style="display:inline;margin-top:0.5rem;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                style="padding:0.25rem 0.5rem;font-size:0.875rem;background:#dc3545;color:white;border:none;border-radius:4px;cursor:pointer;">
                            Удалить
                        </button>
                    </form>
                @endcan
            </div>
        @empty
            <p style="color:#666;">Комментариев пока нет.</p>
        @endforelse

        {{-- Форма добавления комментария — только для авторизованных --}}
        @auth
            <form action="{{ route('comments.store') }}" method="POST" style="margin-top:1.5rem;">
                @csrf
                {{-- 🔗 Скрытое поле: к какому посту относится комментарий --}}
                <input type="hidden" name="post_id" value="{{ $post->id }}">

                <textarea name="body" rows="3"
                          placeholder="Напишите комментарий..."
                          required
                          maxlength="1000"
                          style="width:100%;padding:0.5rem;border:1px solid #ccc;border-radius:4px;font:inherit;">{{ old('body') }}</textarea>

                @error('body')
                    <small style="color:#dc3545;">{{ $message }}</small>
                @enderror

                <button type="submit" 
                        style="margin-top:0.5rem;padding:0.5rem 1rem;background:#28a745;color:white;border:none;border-radius:4px;cursor:pointer;">
                    Отправить
                </button>
            </form>
        @else
            <p style="margin-top:1rem;color:#666;">
                <a href="{{ route('login') }}" style="color:#007bff;">Войдите</a>, чтобы оставить комментарий.
            </p>
        @endauth
    </div>

    <p style="margin-top:2rem;">
        <a href="{{ route('posts.index') }}" style="color:#007bff;">← Назад к списку постов</a>
    </p>
@endsection
