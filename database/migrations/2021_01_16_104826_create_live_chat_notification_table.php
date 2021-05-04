<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLiveChatNotificationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('live_chat_notification', function (Blueprint $table) {
            $table->integer('id', true);
            $table->bigInteger('userid');
            $table->integer('waiting')->default(0);
            $table->string('language', 50)->default('');
            $table->string('who', 50)->default('');
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
        Schema::dropIfExists('live_chat_notification');
    }
}
