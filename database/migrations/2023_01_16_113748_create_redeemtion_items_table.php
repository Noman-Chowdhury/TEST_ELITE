<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRedeemtionItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('redeemtion_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('redeemtion_id')->constrained('redeemtions')->cascadeOnDelete();
            $table->unsignedDouble('volumes');
            $table->unsignedDouble('total_scan_point');
            $table->unsignedDouble('bonus_point');
            $table->unsignedDouble('total_point');
            $table->unsignedDouble('redeem_point');
            $table->string('dealer_id')->nullable();
            $table->string('painter_id')->nullable();
            $table->string('transaction_code');
            $table->string('transaction_date');
            $table->string('status');
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
        Schema::dropIfExists('redeemtion_items');
    }
}
