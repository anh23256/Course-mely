<?php

namespace Database\Seeders;

use App\Models\Gift;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $gifts = [
            [
                'name' => 'iPhone 16 Pro Max',
                'probability' => 1,
                'stock' => 10,
            ],
            [
                'name' => 'MacBook Air Pro M5',
                'probability' => 1,
                'stock' => 5,
            ],
        ];

        foreach ($gifts as $gift) {
            Gift::create($gift);
        }
    }
}
