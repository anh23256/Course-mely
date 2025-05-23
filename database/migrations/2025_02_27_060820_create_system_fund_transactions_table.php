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
        Schema::create('system_fund_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Transaction::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(\App\Models\Course::class)->constrained()->onDelete('cascade')->nullable();
            $table->foreignIdFor(\App\Models\User::class)->constrained()->onDelete('cascade')->nullable();
            $table->decimal('total_amount', 10, 2);
            $table->decimal('retained_amount', 10, 2)->nullable();
            $table->enum('type', ['commission_received', 'withdrawal'])->default('commission_received');
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_fund_transactions');
    }
};
