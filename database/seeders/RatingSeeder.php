<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RatingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $instructor = User::query()->where('email', 'tiendat@gmail.com')->first();
        $courses = Course::query()->where('user_id',  $instructor->id)->where('status', 'approved')->get();
        $users = User::all();
        foreach ($courses as $course) {
            foreach ($users as $user) {
                if ($user->id !== $instructor->id) {
                    Rating::create([
                        'user_id' => $user->id,
                        'course_id' => $course->id,
                        'rate' => rand(3, 5),
                        'content' => 'Khóa học tuyệt vời, rất hữu ích!',
                    ]);
                }
            }
        }
    }
}
