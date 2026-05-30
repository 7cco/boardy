<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;

class RefreshTokenCookie
{
    public function handle(Request $request, Closure $next)
    {
        if (
            $request->is('oauth/token')
            && $request->input('grant_type') === 'refresh_token'
            && !$request->input('refresh_token')
            && $request->cookie('refresh_token')
        ) {
            $request->merge([
                'refresh_token' => $request->cookie('refresh_token'),
            ]);
        }

        $response = $next($request);

        if (!$request->is('oauth/token') || !$response->isOk()) {
            return $response;
        }

        $content = json_decode($response->getContent(), true);

        if (!is_array($content) || !isset($content['refresh_token'])) {
            return $response;
        }

        $refreshToken = $content['refresh_token'];

        unset($content['refresh_token']);
        $response->setContent(json_encode($content));

        $response->headers->setCookie(new Cookie(
            'refresh_token',
            $refreshToken,
            now()->addDays(30)->getTimestamp(), 
            '/',
            null,    // domain
            true,    // secure
            true,    // httpOnly
            false,   // raw
            'Strict' // sameSite
        ));

        return $response;
    }
}