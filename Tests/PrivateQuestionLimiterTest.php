<?php

namespace Modules\PrivateQuestions\Tests;

use Tests\TestCase;
use Domain\Auth\Models\User;
use Database\Factories\UserFactory;
use Modules\PrivateQuestions\PrivateQuestionLimiter;
use Modules\PrivateQuestions\Database\factories\PrivateQuestionFactory;

class PrivateQuestionLimiterTest extends TestCase
{
    public function test_it_count_current_month_questions()
    {
        $user = UserFactory::new()->create();

        PrivateQuestionFactory::new()
            ->count(5)
            ->sequence(
                ['created_at' => now()],
                ['created_at' => now()->startOfMonth()],
                ['created_at' => now()->endOfMonth()],
                ['created_at' => now()->addYear()],
                ['created_at' => now()->addMonth()],
            )
            ->forUser($user)
            ->create();

        $this->assertEquals(3, PrivateQuestionLimiter::attempts($user));
    }

    public function test_it_count_questions_for_same_user()
    {
        $user = UserFactory::new()->count(2)->create()->first();

        PrivateQuestionFactory::new()
            ->count(3)
            ->sequence(
                ['created_at' => now(), 'user_id' => 1],
                ['created_at' => now(), 'user_id' => 2],
                ['created_at' => now(), 'user_id' => 2],
            )
            ->create();

        $this->assertEquals(2, PrivateQuestionLimiter::attempts(User::find(2)));
    }
}
