<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_coding_submissions', function (Blueprint $table) {
            $table->text('result')->change()->nullable();

            if (!Schema::hasColumn('user_coding_submissions', 'is_correct')) {
                $table->boolean('is_correct')->default(0)->after('code');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_coding_submissions', function (Blueprint $table) {
            $table->boolean('result')->change();

            if (Schema::hasColumn('user_coding_submissions', 'is_correct')) {
                $table->dropColumn('is_correct');
            }
        });
    }
};
