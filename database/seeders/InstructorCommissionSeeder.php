<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\InstructorCommission;
use App\Models\User;
use Carbon\Carbon;
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

        $instructors = User::inRandomOrder()->take(5)->get();

        foreach ($instructors as $instructor) {
            $rate = fake()->randomElement([20.00, 25.00, 30.00, 35.00]); // Tỷ lệ hiện tại

            // Lịch sử thay đổi ngẫu nhiên
            $rateLogs = [
                [
                    'rate' => 15,
                    'changed_at' => Carbon::now()->subMonths(3)->format('Y-m-d H:i:s'),
                ],
                [
                    'rate' => 20,
                    'changed_at' => Carbon::now()->subMonths(2)->format('Y-m-d H:i:s'),
                ],
                [
                    'rate' => $rate,
                    'changed_at' => Carbon::now()->subMonth()->format('Y-m-d H:i:s'),
                ],
            ];

            InstructorCommission::create([
                'instructor_id' => $instructor->id,
                'rate' => $rate,
                'rate_logs' => json_encode($rateLogs),
            ]);
        }
    }
}
