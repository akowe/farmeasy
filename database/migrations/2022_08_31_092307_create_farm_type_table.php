<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFarmTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('farm_type', function (Blueprint $table) {
            $table->increments('id');
            $table->string('rice');
            $table->string('maize');
            $table->string('wheat');
            $table->string('others');
            $table->string('status'); // status can be approved or regected. if approve it become visible on frontend
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
        Schema::dropIfExists('farm_type');
    }
}
