<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;


class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Công nghệ thông tin & Truyền thông',
            'Kinh doanh',
            'Marketing',
            'Nghệ thuật & Thiết kế',
            'Kỹ năng mềm',
            'Sức khoẻ & Làm đẹp',
            'Nấu ăn & Ẩm thực',
            'Thể thao & Fitness',
            'Giáo dục & Phát triển bản thân',
            'AI & Machine Learning',
        ];

        foreach ($categories as $category) {
            Category::query()->create([
                'name' => $category,
                'slug' => Str::slug($category),
            ]);
        }
    }
}
