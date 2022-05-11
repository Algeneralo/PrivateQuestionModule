<?php

namespace Modules\PrivateQuestions;

use App\Models\Setting;
use Domain\Auth\Models\User;
use Modules\PrivateQuestions\Models\PrivateQuestion;

class PrivateQuestionLimiter
{
    public static function attempts(User $user, $forMonth = null)
    {
        $forMonth = $forMonth ?? now();

        return PrivateQuestion::query()
            ->where('user_id', $user->id)
            ->whereMonth('created_at', $forMonth)
            ->whereYear('created_at', $forMonth)
            ->count();
    }

    public static function remaining(User $user)
    {
        $questions_limit = Setting::value(Setting::PRIVATE_QUESTION_LIMIT);

        return $questions_limit - self::attempts($user);
    }
}
