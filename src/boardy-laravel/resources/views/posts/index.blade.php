@extends('layouts.app')

@section('title', 'Все посты')

@section('content')
    <h1>Все посты</h1>
<div id="posts-feed"> 
    @foreach ($posts as $post)
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
     @endforeach
</div> 
    @if ($posts->hasPages())
        <div style="margin-top:1rem;">
            {{ $posts->links() }}
        </div>
    @endif
@endsection
<script> 

const wsUrl = 'wss://api.{{ config("app.domain") }}/ws'; 

 

function connect() { 

    const ws = new WebSocket(wsUrl); 
    ws.onopen = () => { 

        console.log('WS connected'); 

    }; 

    ws.onmessage = (event) => { 
        const msg = JSON.parse(event.data); 
        if (msg.type === 'new_post') { 
            prependPost(msg.post); 
        } 
    }; 

    ws.onclose = () => { 
        console.log('WS closed, reconnecting...'); 
        setTimeout(connect, 3000); 
    }; 

} 

 

function prependPost(post) { 

    const feed = document.getElementById('posts-feed'); 

    if (!feed) return; 

    const div = document.createElement('div'); 

    div.className = 'post-card'; 

    div.innerHTML = ` 

        <h3>${escapeHtml(post.title)}</h3> 

        <p>${escapeHtml(post.body)}</p> 

        <small>${escapeHtml(post.author)} � ������ ���</small> 

    `; 
    feed.prepend(div); 
} 

 

function escapeHtml(str) { 
    const d = document.createElement('div'); 
    d.textContent = str; 
    return d.innerHTML; 

} 

connect(); 
</script> 
