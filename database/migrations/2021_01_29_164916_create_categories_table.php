<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 255)->nullable(false);
            $table->boolean('is_active')->nullable(false);
        });

        DB::table('categories')->insert(
            array([
                'name' => 'Маици',
                'is_active' => true
            ], [
                'name' => 'Панталони',
                'is_active' => true
            ], [
                'name' => 'Фармерки',
                'is_active' => true
            ], [
                'name' => 'Блузи',
                'is_active' => true
            ], [
                'name' => 'Патики',
                'is_active' => true
            ], [
                'name' => 'Јакни',
                'is_active' => true
            ], [
                'name' => 'Дуксери',
                'is_active' => true
            ])
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('categories');
    }
}
