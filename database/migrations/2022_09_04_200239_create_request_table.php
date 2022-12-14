<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRequestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('request', function (Blueprint $table) {
             $table->bigIncrements('id');
            $table->string('user_id');
            $table->string('agent_id')->nullable();
            $table->string('farm_size')->nullable();
            $table->string('sp_id')->nullable();
            $table->string('hectare_rate')->nullable();
            $table->string('location');
            $table->string('service_type');// service type requested for
            $table->string('farm_type')->nullable();
            $table->string('name')->nullable();
            $table->string('phone')->nullable();
            $table->string('amount')->nullable();
            $table->string('status');
            $table->string('reference')->nullable();
            $table->string('pay_status')->nullable();
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
        Schema::dropIfExists('request');
    }
}
