<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\PrivateQuestions\Models\PrivateQuestion;

class AddAssignedAtToPrivateQuestionsTable extends Migration
{
    public function up()
    {
        Schema::table('private_questions', function (Blueprint $table) {
            $table->timestamp('assigned_at')->nullable();
        });
        //seed default date
        PrivateQuestion::query()
            ->whereNotNull('specialist_id')
            ->whereNull('answered_at')
            ->update(['assigned_at' => DB::raw('updated_at')]);
    }

    public function down()
    {
        Schema::table('private_questions', function (Blueprint $table) {
            $table->dropColumn('assigned_at');
        });
    }
}