<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_reports', function (Blueprint $table) {
            $table->id();
            $table->string('user_class');
            $table->string('user_id');
            $table->string('date');
            $table->string('month');
            $table->string('year');
            $table->string('redeem_point')->nullable();
            $table->string('bonus_point')->nullable();
            $table->string('scan_point')->nullable();
            $table->string('volume_point')->nullable();
            $table->string('balance')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_reports');
    }
}
