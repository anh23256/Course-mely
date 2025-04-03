<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;


use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $startTime = microtime(true);

        $this->call([
            // RoleSeeder::class,
            // PermissionsSeeder::class,
            // SupportedBankSeeder::class,
            // TagSeeder::class,
            // UserSeeder::class,
            // WalletSedder::class,
            // BannerSeeder::class,
            // CategorySeeder::class,
            // CourseSeeder::class,
            // ApprovableSeeder::class,
            // CouponSeeder::class,
            // PostSeeder::class,
            // InvoiceSeeder::class,
            // WithdrawalsRequestSeeder::class,
            // TransactionSeeder::class,
            // SystemFundSeeder::class,
            // MembershipSeeder::class

            PostSeeder::class,
            CommentSeeder::class,
            ReactionSeeder::class,
        ]);

        $endTime = microtime(true);

        echo 'Thời gian thực hiện: ' . round($endTime - $startTime) . 's';
    }
}
