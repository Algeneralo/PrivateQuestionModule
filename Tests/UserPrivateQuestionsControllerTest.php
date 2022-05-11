<?php

namespace Modules\PrivateQuestions\Tests;

use Carbon\Carbon;
use Tests\TestCase;
use Database\Factories\SpecialistFactory;
use Domain\Specialists\Models\Specialist;
use Database\Factories\ConversationFactory;
use Illuminate\Support\Facades\Notification;
use Modules\PrivateQuestions\Models\PrivateQuestion;
use Modules\PrivateQuestions\Models\AvailableSpecialistForQuestion;
use Modules\PrivateQuestions\Database\factories\PrivateQuestionFactory;
use Modules\Notifications\Notifications\Specialist\NewPrivateQuestionNotification;

class UserPrivateQuestionsControllerTest extends TestCase
{
    public function test_it_can_list_user_private_questions()
    {
        $question = PrivateQuestionFactory::new()->create();

        $this->setUser($question->user)
            ->getJson('/api/v2/users/1/private-questions')
            ->assertSuccessful()
            ->assertDataStructure([
                [
                    'question',
                    'answer',
                    'answered_at',
                    'questioned_at',
                    'tag',
                ],
            ])
            ->assertMetaStructure([
                'can_ask',
                'remain_question_text',
            ]);
    }

    public function test_it_load_specialist_only_if_answered()
    {
        $question = PrivateQuestionFactory::new()->create(['answer' => null]);

        $this->setUser($question->user)
            ->getJson('/api/v2/users/1/private-questions')
            ->assertSuccessful()
            ->assertData([
                [
                    'specialist' => null,
                ],
            ]);

        $question->update(['answer' => 'fake answer']);

        $this->setUser($question->user)
            ->getJson('/api/v2/users/1/private-questions')
            ->assertSuccessful()
            ->assertData([
                [
                    'specialist' => [
                        'id' => 1,
                    ],
                ],
            ]);
    }

    public function test_user_can_create_question()
    {
        $this->setUser()
            ->postJson('/api/v2/users/1/private-questions', [
                'text' => 'fake question',
            ])
            ->assertSuccessful()
            ->assertData([
                'question' => 'fake question',
            ])
            ->assertMetaStructure([
                'can_ask',
                'remain_question_text',
            ]);
    }

    public function test_it_store_specialist_for_returned_client()
    {
        Carbon::setTestNow($now = now());

        $conversation = ConversationFactory::new()
            ->completed()
            ->assigned()
            ->create();

        $this->setUser($conversation->user)
            ->postJson('/api/v2/users/1/private-questions', [
                'text' => 'fake question',
            ])
            ->assertSuccessful();

        $this->assertEquals($conversation->specialist_id, PrivateQuestion::first()->specialist_id);
        $this->assertEquals($now, PrivateQuestion::first()->assigned_at);
    }

    public function test_it_do_not_store_specialist_for_not_active_specailist()
    {
        $conversation = ConversationFactory::new()
            ->completed()
            ->assigned(false, false)
            ->create();

        $this->setUser($conversation->user)
            ->postJson('/api/v2/users/1/private-questions', [
                'text' => 'fake question',
            ])
            ->assertSuccessful();

        $this->assertNull(PrivateQuestion::first()->specialist_id);
    }

    public function test_user_can_read_answer()
    {
        $now = now();
        Carbon::setTestNow($now);

        $question = PrivateQuestionFactory::new()->create(['answered_viewed_at' => null]);

        $this->setUser($question->user)
            ->putJson('/api/v2/users/1/private-questions/1/read')
            ->assertSuccessful();

        $this->assertEquals($now->startOfSecond(), $question->fresh()->answered_viewed_at->startOfSecond());
    }

    public function test_it_return_notification_count()
    {
        $this->setUser()
            ->getJson('/api/v2/users/1/private-questions/notifications')
            ->assertSuccessful()
            ->assertDataStructure([
                'count',
            ]);
    }

    public function test_it_send_notification_to_previous_specialist_when_returned_client()
    {
//        $this->markTestSkipped('not needed');
        \Notification::fake();

        $conversation = ConversationFactory::new()
            ->completed()
            ->forUserWithDevice()
            ->assignedWithDevice()
            ->create();

        $this->setUser($conversation->user)
            ->postJson('/api/v2/users/1/private-questions', [
                'text' => 'fake question',
            ])
            ->assertSuccessful();

        $privateQuestion = PrivateQuestion::first();
        $this->assertEquals($conversation->specialist_id, $privateQuestion->specialist_id);

        Notification::assertSentTo($privateQuestion->specialist->deviceTokens, NewPrivateQuestionNotification::class);
        Notification::assertTimesSent(1, NewPrivateQuestionNotification::class);
    }

    public function test_it_send_notification_to_available_specialist()
    {
        $this->markTestSkipped('not needed');
        \Notification::fake();
        SpecialistFactory::new()->withDevice()->count(5)->create();
        AvailableSpecialistForQuestion::query()->insert([
            ['specialist_id' => 1],
            ['specialist_id' => 2],
            ['specialist_id' => 3],
        ]);

        $this->setUser()
            ->postJson('/api/v2/users/1/private-questions', [
                'text' => 'fake question',
            ])
            ->assertSuccessful();

        Notification::assertSentTo(Specialist::find(2)->deviceTokens, NewPrivateQuestionNotification::class);
        Notification::assertNotSentTo(Specialist::find(4)->deviceTokens, NewPrivateQuestionNotification::class);
        Notification::assertTimesSent(3, NewPrivateQuestionNotification::class);
    }

    public function test_it_show_private_question()
    {
        $privateQuestion = PrivateQuestionFactory::new()->create();

        $this->setUser($privateQuestion->user)
            ->getJson('/api/v2/users/1/private-questions/1', [
                'text' => 'fake question',
            ])
            ->assertSuccessful()
            ->assertData([
                'id' => $privateQuestion->id,
            ])
            ->assertMetaStructure([
                'can_ask', 'remain_question_text',
            ]);
    }
}
