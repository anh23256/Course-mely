<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\User;
use App\Notifications\CourseApprovedNotification;

class NotificationSeeder extends Seeder
{
    public function run()
    {
        for ($i = 0; $i < 1000; $i++) {
            $admins = User::whereHas('roles', function($query){
                $query->where('name', 'admin');
            })->get();

            for ($i = 0; $i < 1000; $i++) {
                $course = Course::inRandomOrder()->first();

                foreach ($admins as $admin) {
                    $admin->notify(new CourseApprovedNotification($course));
                }
            }
        }
    }
}
