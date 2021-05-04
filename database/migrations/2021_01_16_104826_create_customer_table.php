<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->timestamps();
            $table->dateTime('lasttime')->nullable();
            $table->dateTime('transfertime')->nullable();
            $table->dateTime('attendtime')->nullable();
            $table->string('phonenumber', 50)->nullable();
            $table->string('name', 50)->nullable();
            $table->integer('agentId')->nullable();
            $table->string('email', 50)->nullable();
            $table->string('languageOfChoice', 50)->default('');
            $table->bigInteger('channelId')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customer');
    }
}
