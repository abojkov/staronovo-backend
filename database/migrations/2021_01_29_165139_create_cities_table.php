<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 255)->nullable(false);
        });

        DB::table('cities')->insert(
            array([
                'name' => 'Берово'
            ], [
                'name' => 'Битола'
            ], [
                'name' => 'Богданци'
            ], [
                'name' => 'Валандово'
            ], [
                'name' => 'Велес'
            ], [
                'name' => 'Виница'
            ], [
                'name' => 'Гевгелија'
            ], [
                'name' => 'Гостивар'
            ], [
                'name' => 'Дебар'
            ], [
                'name' => 'Делчево'
            ], [
                'name' => 'Демир Капија'
            ], [
                'name' => 'Демир Хисар'
            ], [
                'name' => 'Кавадарци'
            ], [
                'name' => 'Кичево'
            ], [
                'name' => 'Кочани'
            ], [
                'name' => 'Кратово'
            ], [
                'name' => 'Крива Паланка'
            ], [
                'name' => 'Крушево'
            ], [
                'name' => 'Куманово'
            ], [
                'name' => 'Македонски Брод'
            ], [
                'name' => 'Македонска Каменица'
            ], [
                'name' => 'Неготино'
            ], [
                'name' => 'Охрид'
            ], [
                'name' => 'Пехчево'
            ], [
                'name' => 'Прилеп'
            ], [
                'name' => 'Пробиштип'
            ], [
                'name' => 'Радовиш'
            ], [
                'name' => 'Ресен'
            ], [
                'name' => 'Свети Николе'
            ], [
                'name' => 'Скопје'
            ], [
                'name' => 'Струга'
            ], [
                'name' => 'Струмица'
            ], [
                'name' => 'Тетово'
            ], [
                'name' => 'Штип'
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
        Schema::dropIfExists('cities');
    }
}
