<?php

namespace Database\Seeders;

use App\Models\Approvable;
use App\Models\Invoice;
use App\Models\MembershipPlan;
use App\Models\MembershipSubscription;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;


class MembershipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lấy instructor cụ thể
        $instructor = User::where('email', 'ducmely@gmail.com')->first();

        if (!$instructor) {
            $this->command->error('Instructor not found!');
            return;
        }

        $planNames = ['VIP', 'Pro', 'Basic', 'Premium'];
        $durations = [1, 3, 6, 12];
        $planIds = [];

        foreach ($planNames as $name) {
            foreach ($durations as $duration) {
                $plan = MembershipPlan::updateOrCreate(
                    [
                        'instructor_id' => $instructor->id,
                        'name' => $name,
                        'duration_months' => $duration,
                    ],
                    [
                        'code' => strtoupper(Str::random(8)),
                        'price' => fake()->numberBetween(100000, 1000000),
                        'status' => 'active',
                    ]
                );

                $planIds[] = $plan->id;
            }
        }

        // Lấy random 10 học viên
        $instructors = User::query()->role('instructor')->inRandomOrder()->take(10)->get();


        foreach ($instructors as $instructor) {
            for ($i = 0; $i < 5; $i++) {
                $planId = fake()->randomElement($planIds);
                $startDate = fake()->dateTimeBetween('-1 years', 'now');
                $endDate = (clone $startDate)->modify('+' . fake()->randomElement([1, 3, 6, 12]) . ' months');

                MembershipSubscription::create([
                    'membership_plan_id' => $planId,
                    'user_id' => $instructor->id,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'status' => 'active',
                ]);

                Invoice::create([
                    'user_id' => $instructor->id,
                    'membership_plan_id' => $planId,
                    'course_id' => null,
                    'invoice_type' => 'membership',
                    'final_amount' => $plan->price,
                    'status' => 'Đã thanh toán',
                    'created_at' => $startDate,
                ]);
            }
        }

        $this->command->info('MembershipSeeder run successfully!');
    }
}
