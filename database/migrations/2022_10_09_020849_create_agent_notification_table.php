<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAgentNotificationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agent_notification', function (Blueprint $table) {
            $table->increments('id');
            $table->string('request_id');
            $table->string('type');
            $table->string('agent_id');
            $table->string('location');
            $table->text('description');
            $table->string('notice_status');

            $table->timestamp('read_at')->nullable();
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
        Schema::dropIfExists('agent_notification');
    }
}
