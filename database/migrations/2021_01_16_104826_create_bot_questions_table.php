<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBotQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bot_questions', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('category_id')->index('FK_bot_questions_bot_subcategories');
            $table->text('question');
            $table->integer('delete')->default(0);
            $table->string('button', 50)->nullable();
            $table->text('link')->nullable();
            $table->text('trueRoute')->nullable();
            $table->integer('falseRoute')->nullable();
            $table->timestamps();
            $table->integer('first')->nullable();
            $table->text('requiredAnswers')->nullable();
            $table->string('logic', 50)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bot_questions');
    }
}
