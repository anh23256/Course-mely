<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Faker\Factory as Faker;


class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $userId = User::query()->pluck('id')->toArray();
        // $categoriesID = Category::query()->pluck('id')->toArray();

        // for ($i = 0; $i <= 100; $i++) {
        //     $title = fake()->title();
        //     $status = fake()->randomElement(['draft', 'pending', 'published', 'private']);

        //     Post::query()->create([
        //         'user_id' => fake()->randomElement($userId),
        //         'category_id' => fake()->randomElement($categoriesID),
        //         'title' => $title,
        //         'slug' => Str::slug($title) . '-' . Str::uuid(),
        //         'description' => fake()->paragraph(2),
        //         'content' => fake()->paragraph(2),
        //         'status' => $status,
        //         'views' => random_int(0,1000),
        //         'is_hot' => random_int(0,1),
        //         'published_at' => $status == 'published' ? fake()->dateTimeBetween('-1 year', 'now') : null,
        //     ]);
        // }


        $faker = Faker::create('vi_VN'); // Faker với dữ liệu tiếng Việt

        // Tạo 10 bài viết mẫu
        foreach (range(1, 20) as $index) {
            Post::create([
                'user_id' => User::inRandomOrder()->first()->id, // Chọn ngẫu nhiên user
                'category_id' => rand(1, 5), // Chọn ngẫu nhiên category
                'title' => $faker->sentence,
                'slug' => $faker->slug,
                'description' => $faker->text(200),
                'content' => $faker->text(1000),
                'status' => $faker->randomElement(['draft', 'pending', 'published']),
                'views' => rand(0, 1000),
                'is_hot' => rand(0, 1),
                'published_at' => now(),
            ]);
        }
    }
}
