<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToBotQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bot_questions', function (Blueprint $table) {
            $table->foreign('category_id', 'FK_bot_questions_bot_subcategories')->references('id')->on('bot_subcategories')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bot_questions', function (Blueprint $table) {
            $table->dropForeign('FK_bot_questions_bot_subcategories');
        });
    }
}
