@extends('layouts.app')

@section('title', $post->title)

@section('content')
    {{-- Пост (без изменений) --}}
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

    {{-- Контейнер для React-комментариев --}}
    <div class="comments-section">
        <h3>Комментарии</h3>
        <div id="comments-root" 
             data-post-id="{{ $post->id }}" 
             data-user-name="{{ auth()->check() ? auth()->user()->name : '' }}">
        </div>
    </div>

    <p style="margin-top:2rem;">
        <a href="{{ route('posts.index') }}" style="color:#007bff;">← Назад к списку постов</a>
    </p>
@endsection

{{-- Подключение React через Vite --}}
@vite(['resources/js/comments.jsx'])