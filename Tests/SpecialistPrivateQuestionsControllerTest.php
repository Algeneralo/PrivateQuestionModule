<?php

namespace Modules\PrivateQuestions\Tests;

use Carbon\Carbon;
use Tests\TestCase;
use Database\Factories\SpecialistFactory;
use Illuminate\Support\Facades\Notification;
use Modules\PrivateQuestions\Models\AvailableSpecialistForQuestion;
use Modules\PrivateQuestions\Database\factories\PrivateQuestionFactory;
use Modules\Notifications\Notifications\User\PrivateQuestionAnsweredNotification;

class SpecialistPrivateQuestionsControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        AvailableSpecialistForQuestion::query()->create(['specialist_id' => 1]);
    }

    public function test_specialist_can_list_his_questions()
    {
        $specialist = SpecialistFactory::new()->create();

        PrivateQuestionFactory::new()
            ->count(3)
            ->sequence(
                ['specialist_id' => $specialist->id],
                ['specialist_id' => $specialist->id],
                ['specialist_id' => null],
            )
            ->create();

        $this->setSpecialist($specialist)
            ->getJson('api/v2/specialists/1/private-questions?filter[assignee]=me')
            ->assertSuccessful()
            ->assertDataCount(2)
            ->assertDataStructure([
                [
                    'id',
                    'user' => [
                        'id',
                    ],
                    'question',
                    'answer',
                    'answered_at',
                    'questioned_at',
                    'tag',
                    'show_notification_badge',
                ],
            ]);
    }

    public function test_specialist_can_get_his_questions_ordered_by_answered()
    {
        $specialist = SpecialistFactory::new()->create();

        PrivateQuestionFactory::new()
            ->count(3)
            ->sequence(
                ['answered_at' => now()->subDay()],
                ['answered_at' => null],
                ['answered_at' => now()->addDay()],
            )
            ->create(['specialist_id' => $specialist->id]);

        $this->setSpecialist($specialist)
            ->getJson('api/v2/specialists/1/private-questions?filter[assignee]=me')
            ->assertSuccessful()
            ->assertData([
                ['id' => 2],
                ['id' => 3],
                ['id' => 1],
            ]);
    }

    public function test_specialist_can_get_all_questions_ordered_by_oldest()
    {
        $specialist = SpecialistFactory::new()->create();

        PrivateQuestionFactory::new()
            ->count(3)
            ->sequence(
                ['created_at' => now()->addDays(3)],
                ['created_at' => now()->addDays(1)],
                ['created_at' => now()->addDays(2)],
            )
            ->create(['specialist_id' => null]);

        $this->setSpecialist($specialist)
            ->getJson('api/v2/specialists/1/private-questions?filter[assignee]=open')
            ->assertSuccessful()
            ->assertData([
                ['id' => 2],
                ['id' => 3],
                ['id' => 1],
            ]);
    }

    public function test_specialist_can_acquire_question()
    {
        Carbon::setTestNow($now = now());
        $specialist = SpecialistFactory::new()->create();

        $question = PrivateQuestionFactory::new()->create(['specialist_id' => null]);

        $this->setSpecialist($specialist)
            ->putJson('api/v2/specialists/1/private-questions/1/acquire')
            ->assertSuccessful();

        $this->assertEquals($specialist->id, $question->fresh()->specialist_id);
        $this->assertEquals($now, $question->fresh()->assigned_at);
    }

    public function test_specialist_can_answer_question()
    {
        Carbon::setTestNow($now = now());
        $specialist = SpecialistFactory::new()->create();

        PrivateQuestionFactory::new()->create(['specialist_id' => $specialist->id, 'answer' => null]);

        $this->setSpecialist($specialist)
            ->putJson('api/v2/specialists/1/private-questions/1/answer', [
                'text' => 'fake answer',
            ])
            ->assertSuccessful()
            ->assertData([
                'answer'      => 'fake answer',
                'answered_at' => $now->startOfSecond()->toISOString(),
            ]);
    }

    public function test_it_return_notification_count()
    {
        $this->setSpecialist()
            ->getJson('/api/v2/specialists/1/private-questions/notifications')
            ->assertSuccessful()
            ->assertDataStructure([
                'count',
                'show_open_notification_badge',
                'show_me_notification_badge',
            ]);
    }

    public function test_it_send_notification_after_answering_question()
    {
        \Notification::fake();

        $privateQuestion = PrivateQuestionFactory::new()
            ->forUserWithDevice()
            ->create(['answer' => null]);

        $this->setSpecialist($privateQuestion->specialist)
            ->putJson('api/v2/specialists/1/private-questions/1/answer', [
                'text' => 'fake answer',
            ])
            ->assertSuccessful();

        Notification::assertSentTo($privateQuestion->user->deviceTokens, PrivateQuestionAnsweredNotification::class);
        Notification::assertSentTo($privateQuestion->user, PrivateQuestionAnsweredNotification::class);
    }

    public function test_it_only_load_user_after_answering_question()
    {
        $privateQuestion = PrivateQuestionFactory::new()->create(['answer' => null]);

        $response = $this->setSpecialist($privateQuestion->specialist)
            ->putJson('api/v2/specialists/1/private-questions/1/answer', [
                'text' => 'fake answer',
            ])->assertSuccessful()->json();

        $this->assertArrayNotHasKey('specialist', $response['data']);
    }

    public function test_it_show_private_question()
    {
        $privateQuestion = PrivateQuestionFactory::new()->create();

        $this->setUser($privateQuestion->specialist)
            ->getJson('/api/v2/specialists/1/private-questions/1')
            ->assertSuccessful()
            ->assertData([
                'id' => $privateQuestion->id,
            ]);
    }
}
