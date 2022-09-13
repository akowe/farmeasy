<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->string('country');
            $table->string('user_type');
            $table->string('name');
            $table->string('farm_type')->nullable();
            $table->string('service_type')->nullable();
            $table->string('country_code');
            $table->string('phone')->unique();
            $table->string('password');
            $table->string('reg_code')->nullable();//user verification code
            $table->string('status')->nullable();
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
