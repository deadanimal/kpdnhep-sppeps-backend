<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserReviewTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_review', function (Blueprint $table) {
            $table->integer('id', true);
            $table->text('feedback')->nullable();
            $table->integer('star')->nullable()->default(0);
            $table->timestamps();
            $table->bigInteger('userid')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_review');
    }
}
