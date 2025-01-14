<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBinanceTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('binance_tasks', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('status')->nullable();
            $table->string('order_id')->nullable();
            $table->string('type')->nullable();
            $table->string('messege')->nullable();
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
        Schema::dropIfExists('binance_tasks');
    }
}
