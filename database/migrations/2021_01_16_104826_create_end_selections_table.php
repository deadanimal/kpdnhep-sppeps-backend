<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEndSelectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('end_selections', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('subcategory')->default(0);
            $table->text('name');
            $table->text('link')->nullable();
            $table->integer('delete')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('end_selections');
    }
}
