<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('username', 20)->nullable(false);
            $table->string('email', 255)->nullable(false);
            $table->string('password', 255)->nullable(false);
            $table->string('name', 255)->nullable(false);
            $table->string('surname', 255)->nullable(false);
            $table->unsignedBigInteger('role_id')->nullable(false);
            $table->boolean('is_active')->nullable(false);
            $table->timestamps();

            $table->foreign('role_id')->references('id')->on('roles');
        });

        DB::table('users')->insert(
            array([
                'username' => 'admin',
                'password' => Hash::make('admin'),
                'email' => 'admin@staronovo.mk',
                'name' => 'Admin',
                'surname' => 'Admin',
                'role_id' => 1,
                'is_active' => 1,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
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
        Schema::dropIfExists('users');
    }
}
