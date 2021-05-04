<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConversationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->integer('id', true);
            $table->text('message')->nullable();
            $table->bigInteger('clientid')->nullable();
            $table->integer('recipient')->nullable()->comment('0 is bot');
            $table->dateTime('sendtime')->nullable();
            $table->text('botMsg')->nullable();
            $table->text('agentMsg')->nullable();
            $table->dateTime('receivetime')->nullable();
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
        Schema::dropIfExists('conversations');
    }
}
