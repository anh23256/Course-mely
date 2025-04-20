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
        Schema::table('membership_subscriptions', function (Blueprint $table) {
           $table->json('activity_logs')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('membership_subscriptions', function (Blueprint $table) {
            $table->dropColumn('activity_logs');
        });
    }
};
