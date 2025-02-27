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
        Schema::table('withdrawal_requests', function (Blueprint $table) {
            $table->text('admin_comment')->nullable()->after('status');
            $table->enum('instructor_confirmation', ['confirmed', 'not_received'])->default('not_received')->after('admin_comment');
            $table->enum('instructor_confirmation_note', ['confirmed', 'not_received'])->nullable()->after('instructor_confirmation');
            $table->timestamp('instructor_confirmation_date')->nullable()->after('instructor_confirmation_note');
            $table->boolean('is_received')->default(false)->after('instructor_confirmation_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('withdrawal_requests', function (Blueprint $table) {
            $table->dropColumn('admin_comment');
            $table->dropColumn('instructor_confirmation');
            $table->dropColumn('instructor_confirmation_date');
            $table->dropColumn('instructor_confirmation_note');
        });
    }
};
