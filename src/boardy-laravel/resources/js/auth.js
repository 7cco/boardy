import { generateVerifier, generateChallenge, generateState } from './pkce.js';

const CLIENT_ID = '019e58ef-b004-730d-910b-850fb1d78b1f';
const REDIRECT_URI = window.location.origin + '/oauth/callback';

export async function startLogin() {
    console.log('🔐 [startLogin] Начало OAuth flow');
    const verifier = generateVerifier();
    const challenge = await generateChallenge(verifier);
    const state = generateState();

    sessionStorage.setItem('pkce_verifier', verifier);
    sessionStorage.setItem('oauth_state', state);

    const params = new URLSearchParams({
        client_id: CLIENT_ID,
        response_type: 'code',
        redirect_uri: REDIRECT_URI,
        code_challenge: challenge,
        code_challenge_method: 'S256',
        state: state,
        scope: '*',
    });

    window.location = '/oauth/authorize?' + params.toString();
}
if (typeof window !== 'undefined') {
    window.startLogin = startLogin;
}
export async function handleCallback() {
    console.log('🔐 [handleCallback] START, URL:', window.location.href);
    
    const params = new URLSearchParams(window.location.search);
    const code = params.get('code');
    const state = params.get('state');
    
    console.log('🔐 [handleCallback] code:', code ? 'есть' : 'нет', 'state:', state);

    if (!code) {
        console.log('⚠️ [handleCallback] Нет code в URL');
        return null;
    }

    const savedState = sessionStorage.getItem('oauth_state');
    if (state !== savedState) {
        console.error('❌ [handleCallback] State mismatch! CSRF?');
        throw new Error('Invalid state');
    }

    const verifier = sessionStorage.getItem('pkce_verifier');
    if (!verifier) {
        console.error('❌ [handleCallback] Нет verifier в sessionStorage');
        throw new Error('No code_verifier');
    }

    console.log('🔐 [handleCallback] Обмен code на токены...');
    
    try {
        const res = await fetch('/oauth/token', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({
                grant_type: 'authorization_code',
                client_id: CLIENT_ID,
                code,
                code_verifier: verifier,
                redirect_uri: REDIRECT_URI,
            }),
        });

        const text = await res.text();
        console.log('🔐 [handleCallback] Raw response:', res.status, text.substring(0, 200));
        
        if (!res.ok) {
            throw new Error(`Token request failed: ${res.status}`);
        }

        const data = JSON.parse(text);
        console.log('🔐 [handleCallback] Получены токены:', {
            has_access: !!data.access_token,
            has_refresh: !!data.refresh_token,
            expires_in: data.expires_in
        });

        // ← СОХРАНЯЕМ В SESSIONSTORAGE
        if (data.access_token) {
            sessionStorage.setItem('access_token', data.access_token);
            console.log('✅ [handleCallback] access_token сохранён');
        }
        if (data.refresh_token) {
            sessionStorage.setItem('refresh_token', data.refresh_token);
            console.log('✅ [handleCallback] refresh_token сохранён');
        }

        sessionStorage.removeItem('pkce_verifier');
        sessionStorage.removeItem('oauth_state');

        return data.access_token || null;
        
    } catch (err) {
        console.error('❌ [handleCallback] Ошибка:', err);
        throw err;
    }
}

export async function refreshToken() {
    console.log('🔄 [refreshToken] Попытка обновить токен');
    
    const refresh = sessionStorage.getItem('refresh_token');
    if (!refresh) {
        console.log('⚠️ [refreshToken] Нет refresh_token, начинаем новый login');
        startLogin();
        return null;
    }

    try {
        const res = await fetch('/oauth/token', {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                grant_type: 'refresh_token',
                client_id: CLIENT_ID,
                refresh_token: refresh,  // ← передаём из sessionStorage
            }),
        });

        if (!res.ok) {
            console.error('❌ [refreshToken] Refresh failed:', res.status);
            sessionStorage.removeItem('refresh_token');
            sessionStorage.removeItem('access_token');
            startLogin();
            return null;
        }

        const data = await res.json();
        console.log('✅ [refreshToken] Новые токены получены');
        
        if (data.access_token) {
            sessionStorage.setItem('access_token', data.access_token);
        }
        if (data.refresh_token) {
            sessionStorage.setItem('refresh_token', data.refresh_token);
        }
        
        return data.access_token;
        
    } catch (err) {
        console.error('❌ [refreshToken] Ошибка:', err);
        startLogin();
        return null;
    }
}

// ← Утилита: получить текущий токен (для отладки)
export function getCurrentToken() {
    return sessionStorage.getItem('access_token');
}
