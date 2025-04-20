<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserRoleInstructor extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 0; $i < 50; $i++) {
            $uuid = strtolower(str_replace('-', '', Str::uuid()->toString()));
            $uniqueCode = substr($uuid, 0, 10);

            $user = User::query()->create([
                'code' => $uniqueCode,
                'name' => fake()->name(),
                'email' => fake()->unique()->email(),
                'email_verified_at' => now(),
                'status' => 'active',
                'avatar' => 'https://res.cloudinary.com/dvrexlsgx/image/upload/v1742154424/Gemini_Generated_Image_oczkv6oczkv6oczk_dw0bur.jpg',
                'password' => 'Abc123456'
            ]);

            $user->assignRole('instructor');

            $user->profile()->create([
                'phone' => fake()->unique()->phoneNumber(),
                'about_me' => fake()->paragraph(2),
                'address' => fake()->address(),
                'experience' => fake()->paragraph(2),
                'bio' => json_encode(fake()->paragraph(2)),
            ]);
        }
    }
}
