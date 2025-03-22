<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\User;
use App\Notifications\CourseApprovedNotification;
use Illuminate\Support\Facades\Notification;

class NotificationSeeder extends Seeder
{
    public function run()
    {
        $admins = User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->get();

        for ($i = 0; $i < 1000; $i++) {
            $course = Course::inRandomOrder()->first();

            Notification::send($admins, new CourseApprovedNotification($course));
        }
    }
}
