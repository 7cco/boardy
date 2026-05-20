<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GitHubController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('github')->redirect();
    }

    public function callback()
    {
        try {
            $githubUser = Socialite::driver('github')->user();

            $user = User::updateOrCreate(
                ['github_id' => $githubUser->getId()],
                [
                    'name'     => $githubUser->getName() ?: $githubUser->getNickname(),
                    'email'    => $githubUser->getEmail(),
                    'password' => bcrypt(bin2hex(random_bytes(16))),
                ]
            );

            Auth::login($user, remember: true);

            return redirect()->route('posts.index')
                ->with('success', 'Вы вошли через GitHub!');

        } catch (\Exception $e) {
            \Log::error('GitHub auth failed: ' . $e->getMessage());
            
            return redirect()->route('login')
                ->with('error', 'Ошибка входа: ' . $e->getMessage());
        }
    }
}
