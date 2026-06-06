// resources/js/oauth-callback.js
import { handleCallback } from './auth.js';

console.log('🔐 [oauth-callback] Страница загружена');

// Сразу при загрузке обрабатываем callback
handleCallback().then(token => {
    console.log('🔐 [oauth-callback] handleCallback вернул:', token ? 'токен' : 'null');
    
    if (token) {
        console.log('✅ [oauth-callback] Токен получен и сохранён!');
        document.getElementById('status').textContent = '✅ Вход успешен! Перенаправляем...';
        
        // Небольшая задержка чтобы пользователь увидел успех
        setTimeout(() => {
            window.location.href = '/posts';
        }, 500);
    } else {
        console.log('⚠️ [oauth-callback] Токен не получен (нормально если нет code в URL)');
        document.getElementById('status').textContent = '❌ Ошибка входа. Перенаправляем...';
        
        setTimeout(() => {
            window.location.href = '/posts';
        }, 1000);
    }
}).catch(err => {
    console.error('❌ [oauth-callback] Ошибка:', err);
    document.getElementById('status').textContent = '❌ Ошибка: ' + err.message;
    
    setTimeout(() => {
        window.location.href = '/posts';
    }, 2000);
});