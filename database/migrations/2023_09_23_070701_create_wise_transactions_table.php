<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWiseTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wise_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('wise_transaction_id')->nullable();
            $table->string('wise_name')->nullable();
            $table->float('value')->nullable();
            $table->integer('status')->nullable();
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
        Schema::dropIfExists('wise_transactions');
    }
}
