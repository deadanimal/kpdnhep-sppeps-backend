<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDynamicVariablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dynamic_variables', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('name', 50)->default('');
            $table->text('value')->nullable();
            $table->integer('delete')->default(0);
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
        Schema::dropIfExists('dynamic_variables');
    }
}
