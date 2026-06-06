<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    public function updated(User $user): void
    {
        try {
            if ($user->wasChanged('name')) {
                Redis::publish('user.renamed', json_encode([
                    'id' => $user->id,
                    'new_name' => $user->name,
                ]));
            }
        } catch (\Exception $e) {
            Log::warning('Redis failed in UserObserver: ' . $e->getMessage());
        }
    }
}