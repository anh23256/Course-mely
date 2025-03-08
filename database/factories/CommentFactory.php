<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Comment::class;

    public function definition()
    {
        // Chọn ngẫu nhiên một commentable type (ở đây chỉ dùng Post, bạn có thể thêm loại khác)
        $commentable = Post::inRandomOrder()->first();

        // Kiểm tra nếu không có bài viết nào thì tạo một bài viết trước
        if (!$commentable) {
            $commentable = Post::factory()->create();
        }

        // Chọn ngẫu nhiên một bình luận cha nếu có
        $parentComment = Comment::inRandomOrder()->whereNull('parent_id')->first();
        $isReply = $this->faker->boolean(30); // 30% khả năng sẽ là comment con

        return [
            'user_id' => User::inRandomOrder()->first()->id ?? User::factory()->create()->id,
            'commentable_id' => $commentable->id,
            'commentable_type' => Post::class, // Nếu có nhiều loại thì random thêm
            'content' => $this->faker->sentence(),
            'parent_id' => $isReply && $parentComment ? $parentComment->id : null, // Nếu là reply, lấy id của comment cha
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function reply($parentId)
    {
        return $this->state([
            'parent_id' => $parentId,
        ]);
    }
}
