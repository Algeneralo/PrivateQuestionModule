<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAvailableSpecialistForQuestions extends Migration
{
    public function up()
    {
        Schema::create('available_specialist_for_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('specialist_id')->unique();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('available_specialist_for_questions');
    }
}
