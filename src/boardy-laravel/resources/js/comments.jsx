import React, { useState, useEffect, useRef } from 'react';
import { createRoot } from 'react-dom/client';
import { startLogin, handleCallback, refreshToken } from './auth.js';

const API_BASE = 'https://api.terramorf.ai-info.ru';


function Comments({ postId, userName, currentUserId }) {
    const [token, setToken] = useState(null);
    const [comments, setComments] = useState([]);
    const [body, setBody] = useState('');
    const [editingId, setEditingId] = useState(null);
    const [editBody, setEditBody] = useState('');
    const wsRef = useRef(null);

    // 1. Обработать OAuth callback после редиректа
    useEffect(() => {
        handleCallback().then(t => {
            if (t) {
                sessionStorage.setItem('access_token', t);
                setToken(t);
            }
        });

        // Проверяем сохранённый токен
        const saved = sessionStorage.getItem('access_token');
        if (saved) setToken(saved);
    }, []);

    // 2. Загрузить комментарии (без авторизации — GET публичный)
    useEffect(() => {
        fetch(`${API_BASE}/api/posts/${postId}/comments`)
            .then(r => r.json())
            .then(data => {
                if (Array.isArray(data)) setComments(data);
            })
            .catch(err => console.error('Ошибка загрузки комментариев:', err));
    }, [postId]);

    // 3. WebSocket подписка (без авторизации)
    useEffect(() => {
        const ws = new WebSocket(`wss://api.terramorf.ai-info.ru/ws`);
        wsRef.current = ws;

        ws.onmessage = (e) => {
            const msg = JSON.parse(e.data);
            if (msg.type === 'new_comment' && msg.comment.post_id == postId) {
                setComments(prev => {
                    // Не добавляем дубликат
                    if (prev.some(c => c.id === msg.comment.id)) return prev;
                    return [...prev, msg.comment];
                });
            } else if (msg.type === 'update_comment') {
                setComments(prev => prev.map(c =>
                    c.id === msg.comment.id ? { ...c, body: msg.comment.body } : c
                ));
            } else if (msg.type === 'delete_comment') {
                setComments(prev => prev.filter(c => c.id !== msg.comment_id));
            } else if (msg.type === 'user_renamed') {
                setComments(prev => prev.map(c =>
                    String(c.author_id) === String(msg.user_id)
                        ? { ...c, author_name: msg.new_name }
                        : c
                ));
            }
        };

        ws.onclose = () => {
            console.log('WS closed');
        };

        return () => {
            ws.close();
            wsRef.current = null;
        };
    }, [postId]);

    // Fetch с автообновлением токена при 401
    async function authedFetch(url, options = {}) {
        let currentToken = token || sessionStorage.getItem('access_token');
        if (!currentToken) return null;

        let response = await fetch(url, {
            ...options,
            headers: {
                ...options.headers,
                'Authorization': `Bearer ${currentToken}`,
            }
        });

        if (response.status === 401) {
            const newToken = await refreshToken();
            if (!newToken) return null;
            sessionStorage.setItem('access_token', newToken);
            setToken(newToken);
            return fetch(url, {
                ...options,
                headers: {
                    ...options.headers,
                    'Authorization': `Bearer ${newToken}`,
                }
            });
        }
        return response;
    }

    // ─── CREATE ───
    async function addComment(e) {
        e.preventDefault();
        if (!body.trim()) return;

        const res = await authedFetch(`${API_BASE}/api/posts/${postId}/comments`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ body, author_name: userName })
        });

        if (res?.ok) setBody('');
    }

    // ─── UPDATE ───
    async function saveEdit(commentId) {
        if (!editBody.trim()) return;

        const res = await authedFetch(`${API_BASE}/api/comments/${commentId}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ body: editBody })
        });

        if (res?.ok) {
            setEditingId(null);
            setEditBody('');
        }
    }

    // ─── DELETE ───
    async function deleteComment(commentId) {
        if (!confirm('Удалить комментарий?')) return;

        await authedFetch(`${API_BASE}/api/comments/${commentId}`, {
            method: 'DELETE',
        });
    }

    function startEditing(comment) {
        setEditingId(comment.id);
        setEditBody(comment.body);
    }

    function cancelEditing() {
        setEditingId(null);
        setEditBody('');
    }

    const isOwner = (comment) => String(comment.author_id) === String(currentUserId);

    return (
        <div style={{ marginTop: '20px' }}>
            <h3 style={{ marginBottom: '12px' }}>Комментарии ({comments.length})</h3>

            {/* Список комментариев — видны всем */}
            <ul style={{ listStyle: 'none', padding: 0 }}>
                {comments.map(c => (
                    <li key={c.id} style={{
                        borderBottom: '1px solid #eee',
                        padding: '10px 0',
                        marginBottom: '4px'
                    }}>
                        {editingId === c.id ? (
                            /* Режим редактирования */
                            <div>
                                <textarea
                                    value={editBody}
                                    onChange={e => setEditBody(e.target.value)}
                                    style={{ width: '100%', minHeight: '60px', marginBottom: '8px' }}
                                />
                                <button
                                    onClick={() => saveEdit(c.id)}
                                    style={{
                                        padding: '4px 12px', background: '#10b981',
                                        color: 'white', border: 'none', borderRadius: '4px',
                                        cursor: 'pointer', marginRight: '8px'
                                    }}
                                >
                                    Сохранить
                                </button>
                                <button
                                    onClick={cancelEditing}
                                    style={{
                                        padding: '4px 12px', background: '#6b7280',
                                        color: 'white', border: 'none', borderRadius: '4px',
                                        cursor: 'pointer'
                                    }}
                                >
                                    Отмена
                                </button>
                            </div>
                        ) : (
                            /* Режим просмотра */
                            <div>
                                <div>
                                    <strong>{c.author_name}</strong>
                                    <span style={{ color: '#999', fontSize: '0.85em', marginLeft: '8px' }}>
                                        {c.created_at ? new Date(c.created_at).toLocaleString() : ''}
                                    </span>
                                </div>
                                <div style={{ margin: '4px 0', whiteSpace: 'pre-wrap' }}>{c.body}</div>

                                {/* Кнопки редактирования/удаления — только для владельца */}
                                {token && isOwner(c) && (
                                    <div style={{ marginTop: '4px' }}>
                                        <button
                                            onClick={() => startEditing(c)}
                                            style={{
                                                padding: '2px 8px', fontSize: '0.85em',
                                                background: '#3b82f6', color: 'white',
                                                border: 'none', borderRadius: '3px',
                                                cursor: 'pointer', marginRight: '6px'
                                            }}
                                        >
                                            Редактировать
                                        </button>
                                        <button
                                            onClick={() => deleteComment(c.id)}
                                            style={{
                                                padding: '2px 8px', fontSize: '0.85em',
                                                background: '#ef4444', color: 'white',
                                                border: 'none', borderRadius: '3px',
                                                cursor: 'pointer'
                                            }}
                                        >
                                            Удалить
                                        </button>
                                    </div>
                                )}
                            </div>
                        )}
                    </li>
                ))}
            </ul>

            {comments.length === 0 && (
                <p style={{ color: '#999', fontStyle: 'italic' }}>Комментариев пока нет.</p>
            )}

            {/* Форма — только для авторизованных */}
            {token ? (
                <form onSubmit={addComment} style={{ marginTop: '16px' }}>
                    <textarea
                        value={body}
                        onChange={e => setBody(e.target.value)}
                        placeholder="Написать комментарий..."
                        style={{
                            width: '100%', minHeight: '80px',
                            padding: '8px', borderRadius: '4px',
                            border: '1px solid #ddd', marginBottom: '8px'
                        }}
                    />
                    <button
                        type="submit"
                        style={{
                            padding: '6px 16px', background: '#10b981',
                            color: 'white', border: 'none', borderRadius: '4px',
                            cursor: 'pointer', fontWeight: '500'
                        }}
                    >
                        Отправить
                    </button>
                </form>
            ) : (
                <div style={{
                    marginTop: '16px', padding: '12px',
                    background: '#f1f5f9', borderRadius: '4px', textAlign: 'center'
                }}>
                    <button
                        onClick={startLogin}
                        style={{
                            padding: '8px 20px', background: '#3b82f6',
                            color: 'white', border: 'none', borderRadius: '4px',
                            cursor: 'pointer', fontWeight: '500'
                        }}
                    >
                        Войти через OAuth, чтобы комментировать
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