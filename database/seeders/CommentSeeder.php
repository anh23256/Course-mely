<?php

namespace Database\Seeders;

use App\Models\Comment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Tạo 10 bình luận gốc
        Comment::factory(10)->create()->each(function ($comment) {
            // Mỗi bình luận có từ 2-5 reply
            Comment::factory(rand(2, 5))->reply($comment->id)->create();
        });
    }
}
