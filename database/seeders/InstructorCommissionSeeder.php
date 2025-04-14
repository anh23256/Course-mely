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
        $instructors = User::whereHas('roles', function($query){
            $query->where('name', 'instructor');
        })->get();

        foreach ($instructors as $instructor) {
            $rate = fake()->randomElement([0.6,0.8,0.7,0.5,0.7,0.7]);

            $rateLogs = [
                [
                    'rate' => fake()->randomElement([0.3,0.4,0.6,0.8,0.7,0.5,0.7,0.7]),
                    'changed_at' => Carbon::now()->subMonths(3)->format('Y-m-d H:i:s'),
                ],
                [
                    'rate' => fake()->randomElement([0.3,0.4,0.6,0.8,0.7,0.5,0.7,0.7]),
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
