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
        $permissions = ['employee','admin'];
        foreach ($permissions as $permission) {
            $email = $permission == 'admin' ? 'quaixe121811' : $permission;
            $user = User::create([
                'code' => str_replace('-', '', Str::uuid()),
                'name' => Str::ucfirst($permission),
                'email' => $email . '@gmail.com',
                'email_verified_at' => now(),
                'avatar' => 'https://res.cloudinary.com/dvrexlsgx/image/upload/v1732148083/Avatar-trang-den_apceuv_pgbce6.png',
                'password' => $email . '@gmail.com',
            ]);
            $user->assignRole($permission);
        }
    }
}
