<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 100; $i++) {
            $user = User::create([
                'code' => str_replace('-', '', Str::uuid()),
                'name' => fake()->name(),
                'email' => fake()->unique()->email(),
                'email_verified_at' => now(),
                'status' => fake()->randomElement(['active', 'inactive', 'blocked']),
                'avatar' => 'https://res.cloudinary.com/dvrexlsgx/image/upload/v1732148083/Avatar-trang-den_apceuv_pgbce6.png',
                'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
            ]);
            $role = fake()->randomElement(['member', 'instructor', 'employee']);
            $user->assignRole($role);
            $user->profile()->create([
                'phone' => fake()->unique()->phoneNumber(),
                'address' => fake()->address(),
                'experience' => fake()->paragraph(2),
                'bio' => json_encode(fake()->paragraph(2)),
            ]);

            if($role === 'instructor'){

            }
        }

        $permissions = ['member', 'instructor', 'employee', 'admin'];
        foreach ($permissions as $permission) {
            $email = $permission == 'admin' ? 'admin' : $permission;
            $user = User::create([
                'code' => str_replace('-', '', Str::uuid()),
                'name' => Str::ucfirst($permission),
                'email' => $email . '@gmail.com',
                'email_verified_at' => now(),
                'avatar' => 'https://res.cloudinary.com/dvrexlsgx/image/upload/v1732148083/Avatar-trang-den_apceuv_pgbce6.png',
                'password' => password_hash($email . '@gmail.com', PASSWORD_DEFAULT),
            ]);
            $user->assignRole($permission);
        }
    }
}
