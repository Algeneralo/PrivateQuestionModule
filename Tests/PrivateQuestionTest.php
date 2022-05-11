<?php

namespace Modules\PrivateQuestions\Tests;

use Tests\TestCase;
use App\Models\Setting;
use Domain\Auth\Models\User;
use Database\Factories\UserFactory;
use Domain\Specialists\Models\Specialist;
use Database\Factories\SpecialistFactory;
use Modules\PrivateQuestions\Models\PrivateQuestion;
use Modules\PrivateQuestions\Models\AvailableSpecialistForQuestion;
use Modules\PrivateQuestions\Database\factories\PrivateQuestionFactory;

class PrivateQuestionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        AvailableSpecialistForQuestion::query()->create(['specialist_id' => 1]);
        AvailableSpecialistForQuestion::query()->create(['specialist_id' => 2]);

    }

    public function testGetRemainingQuestionsText()
    {
        Setting::fake(Setting::PRIVATE_QUESTION_LIMIT, 1);

        $user = UserFactory::new()->create();

        $this->assertEquals('بإمكانك اضافة 1 سؤال جديد', PrivateQuestion::getRemainingQuestionsText($user, true));
    }

    public function testGetRemainingQuestionsText_with_disabled_setting()
    {
        Setting::fake(Setting::PRIVATE_QUESTION_LIMIT, 0);
        $user = UserFactory::new()->create();

        $this->assertEquals('بإمكانك اضافة سؤال جديد', PrivateQuestion::getRemainingQuestionsText($user, false));
    }

    public function testGetRemainingQuestionsText_when_limit_exceeded()
    {
        Setting::fake(Setting::PRIVATE_QUESTION_LIMIT, 1);

        $question = PrivateQuestionFactory::new()->create();

        $this->assertEquals('لا تستطيع اضافة أكثر من 1 أسئلة', PrivateQuestion::getRemainingQuestionsText($question->user, false));
    }

    public function test_getShowNotificationBadgeAttribute_for_user()
    {
        $user = UserFactory::new()->create();
        $question = PrivateQuestionFactory::new()->create(['answer' => null, 'answered_viewed_at' => null]);
        $this->assertFalse($question->getShowNotificationBadge($user));

        $question = PrivateQuestionFactory::new()->create(['answer' => 'fake answer', 'answered_viewed_at' => null]);
        $this->assertTrue($question->getShowNotificationBadge($user));

        $question = PrivateQuestionFactory::new()->create(['answer' => 'fake answer', 'answered_viewed_at' => now()]);
        $this->assertFalse($question->getShowNotificationBadge($user));
    }

    public function test_getShowNotificationBadgeAttribute_for_specialist()
    {
        $specialist = SpecialistFactory::new()->create();

        $question = PrivateQuestionFactory::new()->create(['answer' => null]);
        $this->assertTrue($question->getShowNotificationBadge($specialist));

        $question = PrivateQuestionFactory::new()->create(['answer' => 'fake answer']);
        $this->assertFalse($question->getShowNotificationBadge($specialist));
    }

    public function test_notificationCountForUser_get_for_same_user()
    {
        UserFactory::new()->count(2)->create();

        PrivateQuestionFactory::new()
            ->count(3)
            ->sequence(
                ['user_id' => 2],
                ['user_id' => 1],
            )
            ->create(['answer' => 'fake', 'answered_viewed_at' => null]);

        $user = User::find(2);
        $this->assertEquals(2, PrivateQuestion::notificationCountForUser($user));
    }

    public function test_notificationCountForUser_get_for_answered_questions()
    {
        $user = UserFactory::new()->create();

        PrivateQuestionFactory::new()
            ->count(3)
            ->forUser($user)
            ->sequence(
                ['answer' => 'fake'],
                ['answer' => null],
                ['answer' => 'fake'],
            )
            ->create(['answered_viewed_at' => null]);

        $this->assertEquals(2, PrivateQuestion::notificationCountForUser($user));
    }

    public function test_notificationCountForUser_get_for_answered_not_viewed_questions()
    {
        $user = UserFactory::new()->create();

        PrivateQuestionFactory::new()
            ->count(3)
            ->forUser($user)
            ->sequence(
                ['answered_viewed_at' => null],
                ['answered_viewed_at' => now()],
                ['answered_viewed_at' => now()],
            )
            ->create(['answer' => 'fake']);

        $this->assertEquals(1, PrivateQuestion::notificationCountForUser($user));
    }

    public function test_notificationCountForSpecialist_get_for_not_answered()
    {
        $specialist = SpecialistFactory::new()->create();

        PrivateQuestionFactory::new()
            ->count(3)
            ->sequence(
                ['answer' => null],
                ['answer' => null],
                ['answer' => 'fake answer'],
            )
            ->create(['specialist_id' => null]);

        $this->assertEquals(2, PrivateQuestion::notificationCountForSpecialist($specialist));
    }

    public function test_notificationCountForSpecialist_get_all_and_his_questions()
    {
        SpecialistFactory::new()->count(2)->create();

        PrivateQuestionFactory::new()
            ->count(3)
            ->sequence(
                ['specialist_id' => 1],
                ['specialist_id' => 2],
                ['specialist_id' => null],
            )
            ->create(['answer' => null]);

        $specialist = Specialist::first();

        $this->assertEquals(2, PrivateQuestion::notificationCountForSpecialist($specialist));
    }
}
