<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WithdrawalRequest;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::query()->pluck('id')->all();
        $withdrawals = WithdrawalRequest::query()->pluck('id')->all();
        $course = Course::query()->where('status', 'approved')->pluck('id')->all();

        for ($i = 1; $i <= 100; $i++) {
            $type = fake()->randomElement(['invoice', 'withdrawal']);
            $transactionable_type = $type == "invoice" ? 'App\\Models\\Invoice' : 'App\\Models\\WithdrawalRequest';
            $transactionable_id = $type == "invoice" ? fake()->randomElement($course) : fake()->randomElement($withdrawals);
            
            Transaction::query()->create([
                'user_id' => fake()->randomElement($users),
                'transaction_code' => substr(str_replace('-','',Str::uuid()),0,10),
                'type' => $type,
                'transactionable_type' => $transactionable_type,
                'transactionable_id' => $transactionable_id,
                'amount' => fake()->randomFloat(2, 10000, 500000),
                'status' => fake()->randomElement(['pending', 'completed', 'failed']),
            ]);
        }
    }
}
