<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;


class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Tạo 10 bình luận gốc
        // Comment::factory(10)->create()->each(function ($comment) {
        //     // Mỗi bình luận có từ 2-5 reply
        //     Comment::factory(rand(2, 5))->reply($comment->id)->create();
        // });

        $faker = Faker::create('vi_VN'); // Faker với dữ liệu tiếng Việt

        // Lấy tất cả bài viết
        $posts = Post::all();

        // Tạo 3 bình luận cho mỗi bài viết
        foreach ($posts as $post) {
            foreach (range(1, 3) as $index) {
                Comment::create([
                    'user_id' => User::inRandomOrder()->first()->id, // Chọn ngẫu nhiên user
                    'parent_id' => null, // Bình luận gốc, không có bình luận cha
                    'content' => $faker->text(200), // Nội dung bình luận
                    'commentable_type' => 'Post', // Loại đối tượng là bài viết
                    'commentable_id' => $post->id, // ID bài viết
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Tạo một số bình luận con (reply) cho mỗi bài viết
            foreach (range(1, 2) as $index) {
                Comment::create([
                    'user_id' => User::inRandomOrder()->first()->id, // Chọn ngẫu nhiên user
                    'parent_id' => Comment::where('commentable_id', $post->id)->inRandomOrder()->first()->id, // Chọn ngẫu nhiên bình luận cha
                    'content' => $faker->text(150), // Nội dung bình luận
                    'commentable_type' => 'Post', // Loại đối tượng là bài viết
                    'commentable_id' => $post->id, // ID bài viết
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
