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
        Schema::table('settings', function (Blueprint $table) {
            //
            $table->string('type')->default('text')->after('value');   // text, select, image,...
            $table->string('group')->nullable()->after('type');        // general, seo,...
            $table->json('options')->nullable()->after('group');       // nếu là select, checkbox thì dùng
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            //
            $table->dropColumn(['type', 'group', 'options']);
        });
    }
};
