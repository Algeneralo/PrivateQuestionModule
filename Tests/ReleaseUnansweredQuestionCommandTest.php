<?php

namespace Modules\PrivateQuestions\Tests;


use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Modules\PrivateQuestions\Console\ReleaseUnansweredQuestionCommand;
use Modules\PrivateQuestions\Database\factories\PrivateQuestionFactory;

class ReleaseUnansweredQuestionCommandTest extends TestCase
{
    public function test_it_release_unanswered_questions()
    {
        $privateQuestion = PrivateQuestionFactory::new()->create(['answer' => null, 'assigned_at' => now()->subDays(2)]);
        $this->assertNotNull($privateQuestion->specialist_id);

        Artisan::call(ReleaseUnansweredQuestionCommand::class);

        $this->assertNull($privateQuestion->fresh()->specialist_id);
    }

    public function test_it_do_not_release_unanswered_questions_that_not_expired()
    {
        $privateQuestion = PrivateQuestionFactory::new()->create(['answer' => null, 'assigned_at' => now()]);
        $this->assertNotNull($privateQuestion->specialist_id);

        Artisan::call(ReleaseUnansweredQuestionCommand::class);

        $this->assertNotNull($privateQuestion->fresh()->specialist_id);
    }
}