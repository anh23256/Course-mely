<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Post;
use App\Models\Reaction;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class ReactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $faker = Faker::create('vi_VN'); // Faker với dữ liệu tiếng Việt

        // Lấy tất cả bài viết và bình luận
        $posts = Post::all();
        $comments = Comment::all();

        // Tạo 5 phản ứng ngẫu nhiên cho mỗi bài viết
        foreach ($posts as $post) {
            foreach (range(1, 5) as $index) {
                Reaction::create([
                    'user_id' => User::inRandomOrder()->first()->id, // Chọn ngẫu nhiên user
                    'type' => $faker->randomElement(['like', 'love', 'haha', 'wow', 'sad', 'angry']), // Chọn ngẫu nhiên phản ứng
                    'reactable_type' => 'Post', // Phản ứng đối với bài viết
                    'reactable_id' => $post->id, // ID bài viết
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Tạo 3 phản ứng ngẫu nhiên cho mỗi bình luận
        foreach ($comments as $comment) {
            foreach (range(1, 3) as $index) {
                Reaction::create([
                    'user_id' => User::inRandomOrder()->first()->id, // Chọn ngẫu nhiên user
                    'type' => $faker->randomElement(['like', 'love', 'haha', 'wow', 'sad', 'angry']), // Chọn ngẫu nhiên phản ứng
                    'reactable_type' => 'Comment', // Phản ứng đối với bình luận
                    'reactable_id' => $comment->id, // ID bình luận
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
