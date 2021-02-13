<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 255)->nullable(false);
            $table->longText('image')->nullable(false);
            $table->text('description')->nullable(false);
            $table->unsignedInteger('quantity_left')->nullable(false);
            $table->double('price')->nullable(false);
            $table->unsignedInteger('category_id')->nullable(false);
            $table->dateTime('datetime_posted')->nullable(false);
            $table->unsignedBigInteger('seller_id')->nullable(false);
            $table->unsignedInteger('city_id')->nullable(false);
            $table->boolean('is_active')->nullable(false);

            $table->foreign('category_id')->references('id')->on('categories');
            $table->foreign('seller_id')->references('id')->on('users');
            $table->foreign('city_id')->references('id')->on('cities');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('posts');
    }
}
