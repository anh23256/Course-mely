<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSpinSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('spin_settings', function (Blueprint $table) {
            $table->id();
            $table->enum('status', ['active', 'inactive'])->default('inactive');
            $table->boolean('has_enough_spin_types')->default(false);
            $table->boolean('has_enough_gifts')->default(false);
            $table->boolean('is_probability_valid')->default(false);
            $table->float('total_probability')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('spin_settings');
    }
}