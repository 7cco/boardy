// 📦 Импорт хуков
const { useState, useEffect } = React;

// ⚙️ Конфигурация
const API = 'https://api.terramorf.ai-info.ru';  // ← ваш домен
const POST_ID = 1;                                // ← ID поста (можно сделать динамическим)

// 🧩 Компонент списка комментариев
function CommentList() {
    const [items, setItems] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    // Загрузка данных
    const load = async () => {
        try {
            setLoading(true);
            const res = await fetch(`${API}/posts/${POST_ID}/comments`);
            
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            
            const data = await res.json();
            setItems(data.items || []);
            setError(null);
        } catch (err) {
            console.error('Ошибка загрузки:', err);
            setError(err.message);
        } finally {
            setLoading(false);
        }
    };

    // Загружаем при монтировании компонента
    useEffect(() => { load(); }, []);

    // Форматирование даты
    const formatDate = (iso) => {
        if (!iso) return '';
        return new Date(iso).toLocaleString('ru-RU', {
            day: '2-digit', month: '2-digit', year: 'numeric',
            hour: '2-digit', minute: '2-digit'
        });
    };

    // 🎨 Рендер
    if (loading) return <div className="text-muted">Загрузка...</div>;
    if (error) return <div className="text-danger">Ошибка: {error}</div>;
    if (!items.length) return <div className="text-muted">Комментариев пока нет</div>;

    return (
        <div className="comments-list">
            {items.map(item => (
                <CommentCard 
                    key={item.id} 
                    item={item} 
                    formatDate={formatDate}
                    onUpdated={load}  // колбэк для обновления списка
                />
            ))}
        </div>
    );
}

// 🧩 Компонент одной карточки комментария
function CommentCard({ item, formatDate, onUpdated }) {
    const [editMode, setEditMode] = useState(false);
    const [editText, setEditText] = useState(item.body);

    // Сохранение изменений
    const handleSave = async () => {
        if (!editText.trim()) return;
        try {
            const res = await fetch(`${API}/comments/${item.id}`, {
                method: 'PUT',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({body: editText})
            });
            if (!res.ok) throw new Error('Не удалось сохранить');
            setEditMode(false);
            onUpdated();  // перезагрузить список
        } catch (err) {
            alert('Ошибка: ' + err.message);
        }
    };

    // Удаление
    const handleDelete = async () => {
        if (!confirm('Удалить комментарий?')) return;
        try {
            const res = await fetch(`${API}/comments/${item.id}`, {
                method: 'DELETE'
            });
            if (!res.ok) throw new Error('Не удалось удалить');
            onUpdated();  // перезагрузить список
        } catch (err) {
            alert('Ошибка: ' + err.message);
        }
    };

    return (
        <article className="card mb-3">
            <div className="card-body">
                <div className="d-flex justify-content-between">
                    <strong className="card-title">
                        {item.author_name || 'Аноним'}
                    </strong>
                    <small className="text-muted">
                        {formatDate(item.created_at)}
                    </small>
                </div>
                
                {editMode ? (
                    // ✏️ Режим редактирования
                    <div className="mt-2">
                        <textarea 
                            className="form-control mb-2"
                            value={editText}
                            onChange={(e) => setEditText(e.target.value)}
                            rows="3"
                        />
                        <div className="btn-group">
                            <button className="btn btn-success btn-sm" onClick={handleSave}>
                                💾 Сохранить
                            </button>
                            <button className="btn btn-secondary btn-sm" onClick={() => setEditMode(false)}>
                                ✕ Отмена
                            </button>
                        </div>
                    </div>
                ) : (
                    // 👁️ Режим просмотра
                    <>
                        <p className="card-text mt-2">{item.body}</p>
                        <div className="btn-group">
                            <button 
                                className="btn btn-outline-secondary btn-sm"
                                onClick={() => { setEditMode(true); setEditText(item.body); }}
                            >
                                ✏️
                            </button>
                            <button 
                                className="btn btn-outline-danger btn-sm"
                                onClick={handleDelete}
                            >
                                🗑️
                            </button>
                        </div>
                    </>
                )}
            </div>
        </article>
    );
}

// 🧩 Компонент формы добавления комментария
function CommentForm({ postId, onAdded }) {
    const [text, setText] = useState('');
    const [sending, setSending] = useState(false);

    const handleSubmit = async (e) => {
        e?.preventDefault();  // если форма в <form>
        if (!text.trim() || sending) return;
        
        try {
            setSending(true);
            const res = await fetch(`${API}/posts/${postId}/comments`, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({body: text})
            });
            if (!res.ok) {
                const err = await res.json().catch(() => ({}));
                throw new Error(err.detail || `HTTP ${res.status}`);
            }
            setText('');  // очистить поле
            onAdded();    // уведомить родителя об обновлении
        } catch (err) {
            console.error('Ошибка отправки:', err);
            alert('Не удалось отправить: ' + err.message);
        } finally {
            setSending(false);
        }
    };

    return (
        <form onSubmit={handleSubmit} className="input-group mb-4">
            <input 
                className="form-control" 
                placeholder="Ваш комментарий..."
                value={text}
                onChange={(e) => setText(e.target.value)}
                disabled={sending}
            />
            <button 
                className="btn btn-primary" 
                type="submit"
                disabled={sending || !text.trim()}
            >
                {sending ? 'Отправка...' : 'Отправить'}
            </button>
        </form>
    );
}

// 🧩 Корневой компонент
function App() {
    // Для динамического POST_ID:
    // const POST_ID = parseInt(window.location.pathname.split('/').pop()) || 1;
    
    const [reloadKey, setReloadKey] = useState(0);
    const forceReload = () => setReloadKey(k => k + 1);

    return (
        <div className="container py-4">
            <h1 className="mb-4">💬 Комментарии к посту #{POST_ID}</h1>
            
            <CommentForm postId={POST_ID} onAdded={forceReload} />
            
            <hr />
            
            <CommentList key={reloadKey} />
        </div>
    );
}

// 🚀 Рендер приложения
const root = ReactDOM.createRoot(document.getElementById('app'));
root.render(<App />);
