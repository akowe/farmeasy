<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
             $table->bigIncrements('id');
            $table->string('user_id');
            $table->string('agent_id')->nullable();
            $table->string('sp_id')->nullable();
            $table->string('land_hectare')->nullable();
            $table->string('location');
            $table->string('service_type');// service type requested for
            $table->string('farm_type')->nullable();
            $table->string('name')->nullable();
            $table->string('phone')->nullable();
            $table->string('amount')->nullable();
            $table->string('status');
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
        Schema::dropIfExists('orders');
    }
}
