<?php

namespace App\Console\Commands;

use App\Events\UserStatusChanged;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use App\Events\UserStatusUpdated;
use App\Models\User;

class CheckUserActivity extends Command
{
    protected $signature = 'user:check-activity';
    protected $description = 'Kiểm tra trạng thái hoạt động của người dùng';

    public function handle()
    {
        $users = User::all();
        foreach ($users as $user) {
            $lastActivity = Cache::get("last_activity_{$user->id}");

            if (!$lastActivity || now()->diffInMinutes($lastActivity) > 3) {
                Cache::forget("user_status_{$user->id}");
                Cache::forget("last_activity_{$user->id}");
                Broadcast(new UserStatusChanged($user->id))->toOthers();
            }
        }

        $this->info('User activity checked successfully.');
    }
}
