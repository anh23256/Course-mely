<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('spin_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('reward_type'); // "no_reward", "spin", "coupon", "gift"
            $table->unsignedBigInteger('reward_id')->nullable(); // ID của coupon hoặc gift (nếu có)
            $table->string('reward_name'); // Tên phần thưởng (lưu lại để dễ hiển thị)
            $table->timestamp('spun_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spin_histories');
    }
};
