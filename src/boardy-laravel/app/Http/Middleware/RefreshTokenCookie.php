<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;

class RefreshTokenCookie
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        \Log::info('RefreshTokenCookie middleware', [
            'path' => $request->path(),
            'is_oauth_token' => $request->is('oauth/token'),
            'response_status' => $response->getStatusCode(),
        ]);

        if ($request->is('oauth/token') && $response->isOk()) {
            $content = json_decode($response->getContent(), true);

            \Log::info('OAuth token response', ['content' => $content]);

            if (isset($content['refresh_token'])) {
                $refreshToken = $content['refresh_token'];
                
                unset($content['refresh_token']);
                $response->setContent(json_encode($content));

                $response->headers->setCookie(new Cookie(
                    'refresh_token',
                    $refreshToken,
                    60 * 24 * 30,
                    '/',
                    null,
                    true,   // Secure
                    true,   // HttpOnly
                    false,  // Raw
                    false,
                    'Strict'
                ));
                \Log::info('Refresh token cookie set');
            }
            else {
                \Log::warning('No refresh_token in response');
            }
        }

        \Log::info('Final check', [
    'has_cookie' => $response->headers->has('Set-Cookie'),
    'cookies' => $response->headers->getCookies(),
]);
        return $response;
    }
}
