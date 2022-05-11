<?php

namespace Modules\PrivateQuestions\Tests;

use Tests\TestCase;
use App\Models\Setting;
use Database\Factories\UserFactory;
use Database\Factories\SpecialistFactory;
use Modules\PrivateQuestions\Policies\PrivateQuestionPolicy;
use Modules\PrivateQuestions\Database\factories\PrivateQuestionFactory;

class PrivateQuestionPolicyTest extends TestCase
{
    /**
     * @dataProvider privateQuestionPolicyDataProvider
     */
    public function test_it_return_true_if_setting_is_disabled($limit, $questionsCount, $expected)
    {
        Setting::fake(Setting::PRIVATE_QUESTION_LIMIT, $limit);

        $user = UserFactory::new()->create();
        PrivateQuestionFactory::new()->count($questionsCount)->forUser($user)->create();

        $this->assertEquals($expected, (new PrivateQuestionPolicy)->create($user));
    }

    public function privateQuestionPolicyDataProvider()
    {
        return [
            'Setting Disabled' => ['limit' => 0, 'questionsCount' => 1, 'expected' => true],
            'Questions Less'   => ['limit' => 3, 'questionsCount' => 1, 'expected' => true],
            'Questions Equal'  => ['limit' => 3, 'questionsCount' => 3, 'expected' => false],
            'Questions More'   => ['limit' => 3, 'questionsCount' => 4, 'expected' => false],
        ];
    }

    public function test_it_return_403_for_adding_new_question()
    {
        Setting::fake(Setting::PRIVATE_QUESTION_LIMIT, 1);

        $user = UserFactory::new()->create();
        PrivateQuestionFactory::new()->count(2)->forUser($user)->create();

        $this->setUser($user)
            ->postJson('/api/v2/users/1/private-questions', [
                'text' => 'fake question',
            ])
            ->assertForbidden();
    }

    public function test_acquire_policy()
    {
        $specialist = SpecialistFactory::new()->create();
        $question = PrivateQuestionFactory::new()->create(['specialist_id' => null]);

        $this->assertTrue((new PrivateQuestionPolicy)->acquire($specialist, $question));

        $question = PrivateQuestionFactory::new()->create(['specialist_id' => $specialist->id]);
        $this->assertFalse((new PrivateQuestionPolicy)->acquire(SpecialistFactory::new()->create(), $question));
    }

    public function test_answer_policy_for_same_acquired_specialist_only()
    {
        $specialist = SpecialistFactory::new()->create();
        $anotherSpecialist = SpecialistFactory::new()->create();
        $question = PrivateQuestionFactory::new()->create(['specialist_id' => null, 'answer' => null]);

        $this->assertFalse((new PrivateQuestionPolicy)->answer($specialist, $question));

        $question->update(['specialist_id' => $anotherSpecialist->id]);

        $this->assertFalse((new PrivateQuestionPolicy)->answer($specialist, $question->fresh()));

        $question->update(['specialist_id' => $specialist->id]);

        $this->assertTrue((new PrivateQuestionPolicy)->answer($specialist, $question->fresh()));
    }

    public function test_acquire_policy_allowed_for_same_specialist()
    {
        $specialist = SpecialistFactory::new()->create();
        $question = PrivateQuestionFactory::new()->create(['specialist_id' => $specialist->id]);

        $this->assertTrue((new PrivateQuestionPolicy)->acquire($specialist, $question));
    }
}
