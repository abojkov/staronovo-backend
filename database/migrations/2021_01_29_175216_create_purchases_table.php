<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('buyer_id')->nullable(false);
            $table->unsignedBigInteger('post_id')->nullable(false);
            $table->unsignedInteger('quantity')->nullable(false);
            $table->double('total_price')->nullable(false);
            $table->double('rating')->nullable(true);
            $table->text('comment')->nullable(true);
            $table->unsignedInteger('status_id')->nullable(false);
            $table->dateTime('datetime_purchased')->nullable(false);
            $table->dateTime('datetime_delivered')->nullable(true);
            $table->dateTime('datetime_confirmation')->nullable(true);
            $table->dateTime('datetime_rating')->nullable(true);
            $table->unsignedInteger('nth_rating')->nullable(false);


            $table->foreign('buyer_id')->references('id')->on('users');
            $table->foreign('post_id')->references('id')->on('posts');
            $table->foreign('status_id')->references('id')->on('statuses');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchases');
    }
}
