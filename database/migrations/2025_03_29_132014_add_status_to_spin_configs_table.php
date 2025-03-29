<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusToSpinConfigsTable extends Migration
{
    public function up()
    {
        Schema::table('spin_configs', function (Blueprint $table) {
            $table->enum('status', ['active', 'inactive'])->default('inactive')->after('probability');
        });
    }

    public function down()
    {
        Schema::table('spin_configs', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
}
