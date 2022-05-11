<?php

namespace Modules\PrivateQuestions\Policies;

use App\Models\Setting;
use Domain\Auth\Models\User;
use Domain\Specialists\Models\Specialist;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\PrivateQuestions\Models\PrivateQuestion;
use Modules\PrivateQuestions\PrivateQuestionLimiter;

class PrivateQuestionPolicy
{
    use HandlesAuthorization;

    public function viewAny()
    {
        return true;
    }

    public function view()
    {
        return true;
    }

    public function create(User $user): bool
    {
        if (Setting::privateQuestionLimitDisabled()) {
            return true;
        }

        return PrivateQuestionLimiter::remaining($user) > 0;
    }

    public function acquire(Specialist $specialist, PrivateQuestion $privateQuestion): bool
    {
        return is_null($privateQuestion->specialist_id) || $privateQuestion->specialist_id == $specialist->id;
    }

    public function answer(Specialist $specialist, PrivateQuestion $privateQuestion): bool
    {
        return $privateQuestion->specialist_id == $specialist->id && is_null($privateQuestion->answer);
    }
}
