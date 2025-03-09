<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::query()->whereHas('roles', function ($query) {
            $query->where('name', 'member');
        })->pluck('id')->toArray();

        $courses = Course::query()->where('status', 'approved')->pluck('id')->toArray();

        for ($i=0; $i < 2000; $i++) { 
            $created_at = fake()->dateTimeBetween('-2 years', now(), env('APP_TIMEZONE'));

            Invoice::insert([
                'user_id' => fake()->randomElement($users),
                'course_id' => fake()->randomElement($courses),
                'amount' => fake()->randomFloat(2, 10000, 10000000),
                'final_amount' => fake()->randomFloat(2, 10000, 10000000),
                'status' => fake()->randomElement(['Đã thanh toán', 'Chưa thanh toán', 'Chờ thanh toán']),
                'created_at' => $created_at,
                'updated_at' => fake()->dateTimeBetween($created_at, now(), env('APP_TIMEZONE'))
            ]);
        }
    }
}
