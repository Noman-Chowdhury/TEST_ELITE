<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRedeemtionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('redeemtions', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->unsignedDouble('volumes');
            $table->unsignedDouble('total_scan_point');
            $table->unsignedDouble('bonus_point');
            $table->unsignedDouble('total_point');
            $table->unsignedDouble('redeem_point');
            $table->string('dealer_id')->nullable();
            $table->string('painter_id')->nullable();
            $table->string('transaction_code')->nullable();
            $table->string('transaction_date')->nullable();
            $table->string('status')->default(2);
            $table->date('start_date');
            $table->date('end_date');
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
        Schema::dropIfExists('redeemtions');
    }
}
