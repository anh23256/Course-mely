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
            $table->boolean('ignore_test_case')->default(false)->after('test_case');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('codings', function (Blueprint $table) {
            $table->dropColumn('ignore_test_case');
        });
    }
};
