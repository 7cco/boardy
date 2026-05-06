@extends('layouts.app')

@section('title', 'Все посты')

@section('content')
    <h1>Все посты</h1>

    @forelse ($posts as $post)
        <article>
            <h3>
                <a href="{{ route('posts.show', $post) }}">{{ $post->title }}</a>
            </h3>
            <p>{{ Str::limit($post->body, 200) }}</p>
            <small>
                Автор: {{ $post->author->name }} · 
                {{ $post->created_at->format('d.m.Y H:i') }} · 
                Комментариев: {{ $post->comments->count() }}
            </small>
        </article>
    @empty
        <p>Постов пока нет. <a href="{{ route('posts.create') }}">Создайте первый!</a></p>
    @endforelse

    @if ($posts->hasPages())
        <div style="margin-top:1rem;">
            {{ $posts->links() }}
        </div>
    @endif
@endsection
