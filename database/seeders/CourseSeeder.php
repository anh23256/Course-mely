<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use App\Models\Course;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::query()->limit(10)->whereHas('roles', function($query){
            $query->where('name', 'instructor');
        })->pluck('id')->toArray();
        $idInstructor = User::query()->where('email', 'instructor@gmail.com')->pluck('id')->toArray();
        
        $arrayUser = array_merge($users, $idInstructor);
        $categories = Category::query()->pluck('id')->all();

        for ($i = 1; $i <= 100; $i++) {
            $name = 'Khóa học số '. $i;
            $slug = Str::slug($name);

            Course::query()->create([
                'user_id' => fake()->randomElement($arrayUser),
                'category_id' => fake()->randomElement($categories),
                'code' => substr(str_replace('-', '', (string) Str::uuid()), 0, 10),
                'name' => $name,
                'slug' => $slug,
                'price' => fake()->randomFloat(2, 10000, 500000),
                'price_sale' => fake()->randomFloat(2, 5000, 400000),
                'level' => fake()->randomElement(['beginner', 'intermediate', 'advanced']),
                'total_student' => random_int(0, 100),
                'status' => fake()->randomElement(['approved', 'rejected']),
                'accepted' => fake()->dateTimeBetween('-1 year', 'now'),
                'thumbnail' => fake()->imageUrl(),
                'intro' => fake()->title(),
                'requirements' => json_encode([fake()->sentence(), fake()->sentence(), fake()->sentence()]),
                'benefits' => json_encode([fake()->sentence(), fake()->sentence(), fake()->sentence()]),
                'qa' => json_encode([
                    [
                        'question' => fake()->sentence(),
                        'answers'  => fake()->sentence()
                    ],
                    [
                        'question' => fake()->sentence(),
                        'answers'  => fake()->sentence()
                    ],
                    [
                        'question' => fake()->sentence(),
                        'answers'  => fake()->sentence()
                    ]
                ],)
            ]);
        }
    }
}
