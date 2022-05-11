<?php

namespace Modules\PrivateQuestions\Database\factories;

use Domain\Auth\Models\User;
use Illuminate\Support\Carbon;
use Database\Factories\UserFactory;
use Database\Factories\SpecialistFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\PrivateQuestions\Models\PrivateQuestion;

class PrivateQuestionFactory extends Factory
{
    protected $model = PrivateQuestion::class;

    public function definition(): array
    {
        return [
            'question'           => $this->faker->word(),
            'answer'             => $this->faker->word(),
            'answered_viewed_at' => Carbon::now(),
            'question_viewed_at' => Carbon::now(),
            'answered_at'        => Carbon::now(),
            'created_at'         => Carbon::now(),
            'updated_at'         => Carbon::now(),
            'user_id'            => UserFactory::new(),
            'specialist_id'      => SpecialistFactory::new(),
        ];
    }

    public function forUser(User $user)
    {
        return $this->state(function () use ($user) {
            return [
                'user_id' => $user->id,
            ];
        });
    }

    public function forUserWithDevice()
    {
        return $this->for(UserFactory::new()->withDevice());
    }

    public function forSpecailistWithDevice()
    {
        return $this->for(SpecialistFactory::new()->withDevice());
    }
}
