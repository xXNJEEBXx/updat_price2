<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProgressOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        Schema::create('progress_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type')->nullable();
            $table->string('order_id')->nullable();
            $table->string('payment')->nullable();
            $table->integer('status')->nullable();
            $table->string('email')->nullable();
            $table->string('pay_id')->nullable();
            $table->string('currency')->nullable();
            $table->string('binace_nickname')->nullable();
            $table->string('binace_name')->nullable();
            $table->string('wise_name')->nullable();
            $table->float('value')->nullable();
            $table->string('finishedOn')->nullable();
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
        Schema::dropIfExists('progress_orders');
    }
}
