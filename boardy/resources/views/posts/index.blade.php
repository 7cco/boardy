@extends('layouts.app')

@section('title', 'Все посты')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h1>Все посты</h1>
        <a href="{{ route('posts.create') }}" id="create-post-btn" style="padding: 0.5rem 1rem; background: #10b981; color: white; text-decoration: none; border-radius: 0.25rem; font-weight: 500;">
            Создать пост
        </a>
    </div>
    <div id="posts-feed"> 
        @forelse ($posts as $post)
            <article>
                <h3>
                    <a href="{{ route('posts.show', $post) }}">{{ $post->title }}</a>
                </h3>
                <p>{{ Str::limit($post->body, 200) }}</p>
                <small>
                    Автор: {{ $post->author->name }} · 
                    {{ $post->created_at->format('d.m.Y H:i') }} · 
                    Комментариев: <span class="comments-count" data-post-id="{{ $post->id }}">загрузка...</span>
                </small>
            </article>
        @empty
            <p class="text-center text-gray-500">Постов пока нет.</p>
        @endforelse
    </div> 

    @if ($posts->hasPages())
        <div style="margin-top:1rem;">
            {{ $posts->links() }}
        </div>
    @endif
@endsection

@vite(['resources/js/auth.js'])
<button id="login-btn" style="margin: 1rem; padding: 0.5rem 1rem; background: #3b82f6; color: white; border: none; border-radius: 0.25rem; cursor: pointer;">
    Войти
</button>

<script>
document.getElementById('login-btn')?.addEventListener('click', () => {
    // startLogin теперь доступен глобально через window
    if (typeof window.startLogin === 'function') {
        window.startLogin();
    } else {
        console.error('startLogin не загружен');
    }
});
</script>

<script> 
const wsUrl = 'ws://api.{{ config("app.domain") }}/ws';
let ws = null;

async function loadCommentsCounts() {
    const countElements = document.querySelectorAll('.comments-count');
    
    for (const el of countElements) {
        const postId = el.dataset.postId;
        try {
            const response = await fetch(`/api/posts/${postId}/comments-count`);
            const data = await response.json();
            el.textContent = data.count;
        } catch (err) {
            console.error(`Ошибка загрузки счётчика для поста ${postId}:`, err);
            el.textContent = '0';
        }
    }
}

document.addEventListener('DOMContentLoaded', loadCommentsCounts);

function connect() { 
    ws = new WebSocket(wsUrl); 
    
    ws.onopen = () => { 
        console.log('WS connected'); 
    }; 

    ws.onmessage = (event) => { 
        const msg = JSON.parse(event.data); 
        if (msg.type === 'new_post') { 
            prependPost(msg.post); 
        }
        else if (msg.type === 'post_deleted') {
            removePost(msg.post_id);
        }
        else if (msg.type === 'post_updated') {
            updatePost(msg.post);
        }
        else if (msg.type === 'new_comment') {
            updateCommentsCount(msg.comment.post_id, 1);
        }
        else if (msg.type === 'delete_comment') {
            updateCommentsCount(msg.post_id, -1);
        }
    }; 

    ws.onclose = () => { 
        console.log('WS closed, reconnecting...'); 
        setTimeout(connect, 3000); 
    }; 
} 

function updateCommentsCount(postId, delta) {
    const countEl = document.querySelector(`.comments-count[data-post-id="${postId}"]`);
    if (!countEl) return;
    
    let current = parseInt(countEl.textContent) || 0;
    countEl.textContent = Math.max(0, current + delta);
    
    // Анимация (опционально)
    countEl.style.transition = 'color 0.3s';
    countEl.style.color = '#10b981';
    setTimeout(() => {
        countEl.style.color = '';
    }, 500);
}

function prependPost(post) { 
    const feed = document.getElementById('posts-feed'); 
    if (!feed) return; 
    
    const article = document.createElement('article');
    article.innerHTML = ` 
        <h3><a href="/posts/${post.id}">${escapeHtml(post.title)}</a></h3> 
        <p>${escapeHtml(post.body)}</p> 
        <small>
            Автор: ${escapeHtml(post.author)} · 
            только что
        </small> 
    `; 
    feed.prepend(article); 
} 

function removePost(postId) {
    const feed = document.getElementById('posts-feed');
    if (!feed) return;
    
    // Находим все посты и удаляем тот, у которого ссылка ведёт на /posts/{id}
    const articles = feed.querySelectorAll('article');
    articles.forEach(article => {
        const link = article.querySelector('a[href^="/posts/"]');
        if (link) {
            // Извлекаем ID из href="/posts/123"
            const href = link.getAttribute('href');
            const match = href.match(/\/posts\/(\d+)/);
            if (match && parseInt(match[1]) === postId) {
                // Анимация удаления (опционально)
                article.style.transition = 'opacity 0.3s, transform 0.3s';
                article.style.opacity = '0';
                article.style.transform = 'translateX(-20px)';
                setTimeout(() => article.remove(), 300);
            }
        }
    });
    
    console.log(`🗑️ Post ${postId} removed from feed`);
}

function updatePost(post) {
    const feed = document.getElementById('posts-feed');
    if (!feed) return;
    
    // Находим пост по ID
    const articles = feed.querySelectorAll('article');
    articles.forEach(article => {
        const link = article.querySelector('a[href^="/posts/"]');
        if (link) {
            const href = link.getAttribute('href');
            const match = href.match(/\/posts\/(\d+)/);
            if (match && parseInt(match[1]) === post.id) {
                // Обновляем заголовок
                const titleEl = article.querySelector('h3 a');
                if (titleEl) {
                    titleEl.textContent = escapeHtml(post.title);
                }
                
                // Обновляем тело (если оно есть в превью)
                const bodyEl = article.querySelector('p');
                if (bodyEl && post.body) {
                    bodyEl.textContent = escapeHtml(post.body).substring(0, 200) + 
                        (post.body.length > 200 ? '...' : '');
                }
                
                // Обновляем время (опционально)
                const smallEl = article.querySelector('small');
                if (smallEl && post.updated_at) {
                    // Можно добавить индикатор "обновлено"
                    const updatedBadge = document.createElement('span');
                    updatedBadge.textContent = ' ✏️ обновлено';
                    updatedBadge.style.color = '#666';
                    updatedBadge.style.fontSize = '0.875rem';
                    if (!smallEl.querySelector('.updated-badge')) {
                        updatedBadge.className = 'updated-badge';
                        smallEl.appendChild(updatedBadge);
                    }
                }
                
                // Анимация (опционально)
                article.style.transition = 'background-color 0.3s';
                article.style.backgroundColor = '#fef3c7';
                setTimeout(() => {
                    article.style.backgroundColor = '';
                }, 1000);
                
                console.log(`✏️ Post ${post.id} updated in feed`);
            }
        }
    });
}

function escapeHtml(str) { 
    const d = document.createElement('div'); 
    d.textContent = str; 
    return d.innerHTML; 
} 

connect(); 
</script>