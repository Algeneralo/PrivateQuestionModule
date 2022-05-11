<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePrivateQuestionsTable extends Migration
{
    public function up()
    {
        Schema::create('private_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->foreignId('specialist_id')->nullable();
            $table->text('question');
            $table->text('answer')->nullable();
            $table->timestamp('answered_viewed_at')->nullable();
            $table->timestamp('question_viewed_at')->nullable();
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('private_questions');
    }
}
