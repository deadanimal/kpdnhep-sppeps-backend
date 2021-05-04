<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConversationIdTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('conversation_id', function (Blueprint $table) {
            $table->integer('id', true);
            $table->bigInteger('clientid')->nullable();
            $table->bigInteger('agentid')->nullable();
            $table->text('receipent')->nullable();
            $table->timestamps();
            $table->text('message')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('conversation_id');
    }
}
