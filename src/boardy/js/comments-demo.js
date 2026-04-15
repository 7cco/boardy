const API = 'https://api.terramorf.ai-info.ru';
const POST_ID = 1;

// Экранирование (защита от XSS)
function esc(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

// Загрузка комментариев
async function loadComments() {
    try {
        const res = await fetch(`${API}/posts/${POST_ID}/comments`);
        
        if (!res.ok) {
            const errText = await res.text();
            throw new Error(`HTTP ${res.status}: ${errText}`);
        }
        
        const data = await res.json();
        const container = document.getElementById('list');
        
        if (!data.items || data.items.length === 0) {
            container.innerHTML = '<p>Комментариев пока нет</p>';
            return;
        }
        
        container.innerHTML = data.items.map(item => `
            <article class="comment">
                <header>
                    <strong>${esc(item.author_name || 'Аноним')}</strong>
                    <time>${esc(new Date(item.created_at).toLocaleString('ru-RU'))}</time>
                </header>
                <p>${esc(item.body)}</p>
            </article>
        `).join('');
        
    } catch (err) {
        console.error('Ошибка загрузки:', err);
        document.getElementById('list').innerHTML = 
            `<p style="color:red">Ошибка: ${esc(err.message)}</p>`;
    }
}

// Отправка нового комментария
document.getElementById('btn')?.addEventListener('click', async () => {
    const input = document.getElementById('body');
    const body = input.value.trim();
    
    if (!body) {
        alert('Введите текст комментария');
        return;
    }
    
    try {
        const res = await fetch(`${API}/posts/${POST_ID}/comments`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({body: body})
        });
        
        if (!res.ok) {
            const err = await res.json().catch(() => ({}));
            throw new Error(err.detail || `HTTP ${res.status}`);
        }
        
        input.value = '';  // очистить поле
        await loadComments();  // обновить список
        
    } catch (err) {
        console.error('Ошибка отправки:', err);
        alert(`Не удалось отправить: ${err.message}`);
    }
});

// Запуск при загрузке
document.addEventListener('DOMContentLoaded', loadComments);
