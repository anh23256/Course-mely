<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'name' => 'member',
                'guard_name' => 'web',
                'description' => 'Người dùng của hệ thống (học viên)',
            ],
            [
                'name' => 'employee',
                'guard_name' => 'web',
                'description' => 'Nhân viên hệ thống',
            ],
            [
                'name' => 'instructor',
                'guard_name' => 'web',
                'description' => 'Giảng viên hệ thống',
            ],
            [
                'name' => 'admin',
                'guard_name' => 'web',
                'description' => 'Người có quyền kiểm soát toàn bộ hệ thống',
            ],
        ];

        foreach ($data as $item) {
            \Spatie\Permission\Models\Role::create($item);
        }
    }
}
