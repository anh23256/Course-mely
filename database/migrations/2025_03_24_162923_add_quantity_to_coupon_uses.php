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
        Schema::table('coupon_uses', function (Blueprint $table) {
            $table->integer('quantity')->default(1)->after('coupon_id');
            $table->json('details')->nullable()->after('quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coupon_uses', function (Blueprint $table) {
            $table->dropColumn('quantity');
            $table->dropColumn('details');
        });
    }
};
