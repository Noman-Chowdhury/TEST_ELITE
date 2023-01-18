<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRedeemtionHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('redeemtion_histories', function (Blueprint $table) {
            $table->id();
            $table->date('from');
            $table->date('to');
            $table->string('earned');
            $table->string('redeem')->nullable();
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
        Schema::dropIfExists('redeemtion_histories');
    }
}
