<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBotSubcategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bot_subcategories', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('bot_category_id')->nullable();
            $table->string('sub_category_name', 50)->default('');
            $table->integer('delete')->default(0);
            $table->integer('sub_category_id')->nullable();
            $table->integer('has_sub');
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
        Schema::dropIfExists('bot_subcategories');
    }
}
