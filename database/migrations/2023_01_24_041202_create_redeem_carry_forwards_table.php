<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRedeemCarryForwardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('redeem_carry_forwards', function (Blueprint $table) {
            $table->id();
            $table->string('user_type');
            $table->string('user_id');
            $table->string('total_point');
            $table->string('redeem_point');
            $table->string('forward_point');
            $table->string('from_code');
            $table->string('to_code')->nullable();
            $table->string('process_date')->nullable();
            $table->enum('status',[0,1])->default(0);
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
        Schema::dropIfExists('redeem_carry_forwards');
    }
}
