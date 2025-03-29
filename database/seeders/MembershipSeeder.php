<?php

namespace Database\Seeders;

use App\Models\Approvable;
use App\Models\MembershipPlan;
use App\Models\MembershipSubscription;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class MembershipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        $instructors = User::whereHas('roles', function ($query) {
            $query->where('name', 'instructor'); // Lọc role là instructor
        })->pluck('id')->toArray();

        if (empty($instructors)) {
            $instructors = [1]; // Nếu không có giảng viên, mặc định là 1
        }

        // Tạo 5 gói membership giả
        $membershipPlans = [];
        for ($i = 1; $i <= 5; $i++) {
            $membershipPlans[] = MembershipPlan::create([
                'instructor_id' => $faker->randomElement($instructors),
                'code' => 'MP' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'name' => 'Gói ' . $i,

                'description' => $faker->sentence,
                'price' => $faker->randomFloat(2, 100, 1000),
                'duration_months' => $faker->randomElement([3, 6, 9]),
                'status' => 'active',
            ]);
        }

        // Lấy danh sách user (giả sử đã có user trong DB)
        $users = User::pluck('id')->toArray();

        // Tạo 20 đăng ký membership giả
        $subscriptions = [];
        for ($i = 1; $i <= 20; $i++) {
            $plan = $faker->randomElement($membershipPlans);
            $userId = $faker->randomElement($users);
            $startDate = $faker->dateTimeBetween('-1 year', 'now');
            $endDate = (clone $startDate)->modify("+{$plan->duration_months} months");


            $subscriptions[] = MembershipSubscription::create([
                'membership_plan_id' => $plan->id,
                'user_id' => $userId,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => $faker->randomElement(['active', 'expired', 'cancelled']),
                'activity_logs' => json_encode(['log' => 'Subscription created']),
            ]);
        }

        // Tạo 10 bản ghi kiểm duyệt (approvables)
        foreach ($membershipPlans as $plan) { 
            Approvable::create([
                'approvable_type' => MembershipPlan::class, 
                'approvable_id' => $plan->id, 
                'status' => $faker->randomElement(['pending', 'approved', 'rejected']),
                'approver_id' => $faker->randomElement($users),
            ]);
        }
    }
}
