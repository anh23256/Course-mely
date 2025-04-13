<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\InstructorCommission;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class InstructorCommissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        
        $instructors = User::whereHas('roles', function ($query) {
            $query->where('name', 'instructor');
        })->inRandomOrder()->take(10)->get();

        
        $courses = Course::inRandomOrder()->take(20)->get();

        // Giả lập 50 hoa hồng
        for ($i = 0; $i < 50; $i++) {
            $instructor = $instructors->random();
            $course = $courses->random();

            // Giá khóa học giả lập (nếu không có sẵn)
            $coursePrice = $course->price ?? fake()->numberBetween(200000, 1000000);

            // Tỷ lệ hoa hồng (20% - 50%)
            $percentage = fake()->randomElement([20, 25, 30, 35, 40, 45, 50]);

            $commissionAmount = round($coursePrice * ($percentage / 100), 2);

            InstructorCommission::create([
                'instructor_id' => $instructor->id,
                'course_id' => $course->id,
                'commission_amount' => $commissionAmount,
                'percentage' => $percentage,
                'created_at' => fake()->dateTimeBetween('-3 months', 'now'),
                'updated_at' => now(),
            ]);
        }
    }
}
