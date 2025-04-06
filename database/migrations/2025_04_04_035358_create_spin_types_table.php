<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSpinTypesTable extends Migration
{
    public function up()
    {
        Schema::create('spin_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Tên loại phần thưởng (no_reward, coupon, spin, v.v.)
            $table->string('display_name'); // Tên hiển thị cho người dùng (Không trúng, Mã giảm giá, v.v.)
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('spin_types');
    }
}