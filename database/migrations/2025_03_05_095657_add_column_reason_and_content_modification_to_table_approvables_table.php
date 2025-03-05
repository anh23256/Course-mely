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
        Schema::table('approvables', function (Blueprint $table) {
            $table->string('reason')->nullable()->after('note');
            $table->boolean('content_modification')->default(0)->after('reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approvables', function (Blueprint $table) {
            $table->dropColumn('reason');
            $table->dropColumn('content_modification');
        });
    }
};

