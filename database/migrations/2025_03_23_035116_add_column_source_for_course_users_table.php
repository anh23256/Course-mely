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
        Schema::table('course_users', function (Blueprint $table) {
            $table->enum('source', ['purchase', 'membership'])->default('purchase')->after('completed_at');
            $table->enum('access_status', ['active', 'inactive'])->default('active')->after('source');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_users', function (Blueprint $table) {
            $table->dropColumn('source');
            $table->dropColumn('access_status');
        });
    }
};
