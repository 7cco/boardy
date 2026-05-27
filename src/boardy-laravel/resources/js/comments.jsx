import React, { useState, useEffect, useRef } from 'react';
import { createRoot } from 'react-dom/client';
import { startLogin, handleCallback, refreshToken, getCurrentToken } from './auth.js';

const API_BASE = 'https://api.terramorf.ai-info.ru';

function Comments({ postId, userName }) {
    const [token, setToken] = useState(null);
    const [comments, setComments] = useState([]);
    const [body, setBody] = useState('');
    const [isLoading, setIsLoading] = useState(true);
    const wsRef = useRef(null);

     useEffect(() => {
        const savedToken = getCurrentToken();
        if (savedToken) {
            console.log('✅ [Comments] Токен найден в sessionStorage');
            setToken(savedToken);
        }
    }, []);

    useEffect(() => {
        fetchComments();
    }, [postId]);

    // Проверяем callback (если мы на /oauth/callback?code=...)
    useEffect(() => {
        if (window.location.search.includes('code=')) {
            console.log('🔍 [Comments] Обнаружен callback URL');
            handleCallback().then(t => {
                if (t) {
                    console.log('✅ [Comments] Токен получен из callback');
                    setToken(t);
                    // Убираем code/state из URL без перезагрузки
                    window.history.replaceState({}, document.title, window.location.pathname);
                }
            }).catch(err => {
                console.error('❌ [Comments] Ошибка callback:', err);
            });
        }
    }, []);

    // WebSocket только если есть токен
    useEffect(() => {
        if (token) {
            connectWebSocket();
        }
    }, [token]);

    async function fetchComments() {
        console.log('🔍 [fetchComments] Загрузка для postId:', postId);
        try {
            const res = await fetch(`${API_BASE}/api/posts/${postId}/comments`);
            console.log('🔍 [fetchComments] Ответ:', res.status);
            
            if (res.ok) {
                const data = await res.json();
                console.log('✅ [fetchComments] Получено комментариев:', data.length);
                setComments(data);
            } else {
                console.error('❌ [fetchComments] Ошибка:', await res.text());
            }
        } catch (e) {
            console.error('❌ [fetchComments] Исключение:', e);
        } finally {
            // ← ✅ Сбрасываем isLoading ТОЛЬКО после завершения запроса
            setIsLoading(false);
            console.log('✅ [fetchComments] isLoading = false');
        }
    }

    function connectWebSocket() {
        const ws = new WebSocket(`wss://api.terramorf.ai-info.ru/ws`);
        ws.onopen = () => console.log('✅ [WS] Connected');
        ws.onmessage = (event) => {
            const msg = JSON.parse(event.data);
            if (msg.type === 'new_comment' && msg.comment.post_id === postId) {
                setComments(prev => [...prev, msg.comment]);
            } else if (msg.type === 'delete_comment') {
                setComments(prev => prev.filter(c => c.id !== msg.comment_id));
            }
        };
        wsRef.current = ws;
    }

    async function authedFetch(url, options = {}) {
        let response = await fetch(url, {
            ...options,
            headers: {
                ...options.headers,
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            }
        });

        if (response.status === 401) {
            console.log('⚠️ [authedFetch] 401, пробуем refresh');
            const newToken = await refreshToken();
            if (!newToken) return null;
            setToken(newToken);
            return fetch(url, {
                ...options,
                headers: {
                    ...options.headers,
                    'Authorization': `Bearer ${newToken}`,
                    'Content-Type': 'application/json'
                }
            });
        }
        return response;
    }

    async function addComment(e) {
        e.preventDefault();
        if (!body.trim() || !userName || !token) {
            alert('Ошибка авторизации');
            return;
        }

        const res = await authedFetch(`${API_BASE}/api/posts/${postId}/comments`, {
            method: 'POST',
            body: JSON.stringify({ body, author_name: userName })
        });

        if (res?.ok) {
            setBody('');
            // Комментарий придёт по WebSocket
        } else {
            alert('Не удалось отправить комментарий');
        }
    }

    useEffect(() => {
        return () => {
            if (wsRef.current?.readyState === WebSocket.OPEN) {
                wsRef.current.close();
            }
        };
    }, []);

    if (isLoading) return <p>Загрузка...</p>;

    return (
    <div style={{ marginTop: '2rem' }}>
        <h3>Комментарии ({comments.length})</h3>
        
        {/* Список комментариев — ВСЕГДА виден */}
        <div style={{ marginBottom: '1.5rem' }}>
            {comments.length === 0 ? (
                <p style={{ color: '#666' }}>Комментариев пока нет.</p>
            ) : (
                comments.map(c => (
                    <div key={c.id} style={{ padding: '1rem', background: '#f9f9f9', borderRadius: '4px', marginBottom: '0.75rem', border: '1px solid #eee' }}>
                        <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '0.5rem' }}>
                            <strong>{c.author_name}</strong>
                            <small style={{ color: '#666' }}>
                                {new Date(c.created_at).toLocaleString('ru-RU')}
                            </small>
                        </div>
                        <p style={{ margin: 0, whiteSpace: 'pre-wrap' }}>{c.body}</p>
                    </div>
                ))
            )}
        </div>

        {/* Форма или кнопка — в зависимости от токена */}
        {token ? (
            <form onSubmit={addComment}>
                <textarea 
                    value={body} 
                    onChange={e => setBody(e.target.value)} 
                    placeholder="Напишите комментарий..." 
                    required
                    style={{ width: '100%', padding: '0.5rem', border: '1px solid #ccc', borderRadius: '4px' }}
                />
                <button type="submit" style={{ marginTop: '0.5rem', padding: '0.5rem 1rem', background: '#28a745', color: 'white', border: 'none', borderRadius: '4px', cursor: 'pointer' }}>
                    Отправить
                </button>
            </form>
        ) : (
            <div style={{ marginTop: '1rem', padding: '1rem', background: '#f9f9f9', borderRadius: '4px' }}>
                <p>Чтобы оставить комментарий, необходимо войти.</p>
                <button 
                    onClick={startLogin}
                    style={{ padding: '0.5rem 1rem', background: '#007bff', color: 'white', border: 'none', borderRadius: '4px', cursor: 'pointer' }}
                >
                    Войти через OAuth
                </button>
            </div>
        )}
    </div>
);
}

document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('comments-root');
    if (root) {
        const postId = parseInt(root.dataset.postId);
        const userName = root.dataset.userName || null;
        createRoot(root).render(<Comments postId={postId} userName={userName} />);
    }
});

export default Comments;