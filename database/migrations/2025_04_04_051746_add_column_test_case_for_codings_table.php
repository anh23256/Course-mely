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
        Schema::table('codings', function (Blueprint $table) {
            $table->json('test_case')->nullable()->after('solution_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('codings', function (Blueprint $table) {
            $table->dropColumn('test_case');
        });
    }
};
