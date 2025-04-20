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
        Schema::table('system_funds', function (Blueprint $table) {
            $table->decimal('pending_balance', 18, 2)->default(0)->after('balance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('system_funds', function (Blueprint $table) {
            $table->dropColumn('pending_balance');
        });
    }
};
