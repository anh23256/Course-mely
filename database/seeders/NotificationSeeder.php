<?php

namespace Database\Seeders;

use App\Models\Conversation;
use App\Models\MembershipPlan;
use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\Message;
use App\Models\Post;
use App\Models\User;
use App\Notifications\MessageNotification;
use App\Notifications\CourseApprovedNotification;
use App\Notifications\PostSubmittedForApprovalNotification;
use App\Notifications\UserBuyMembershipNotification;
use Illuminate\Support\Facades\Notification;

class NotificationSeeder extends Seeder
{
    public function run()
    {
        $admins = User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->get();

        for ($i = 0; $i < 100; $i++) {
            $courses = Course::inRandomOrder()->first();

            Notification::send($admins, new CourseApprovedNotification($courses));

            $posts = Post::inRandomOrder()->first();

            Notification::send($admins, new PostSubmittedForApprovalNotification($posts));

            $posts = Post::inRandomOrder()->first();

            Notification::send($admins, new PostSubmittedForApprovalNotification($posts));

            $memberships = MembershipPlan::inRandomOrder()->first();
            $student = User::whereHas('roles', function ($query) {
                $query->where('name', 'member');
            })->inRandomOrder()->first();

            Notification::send($admins, new UserBuyMembershipNotification($student, $memberships));

            $messages = Message::inRandomOrder()->first();

            Notification::send($admins, new MessageNotification($messages));
        }
    }
}
