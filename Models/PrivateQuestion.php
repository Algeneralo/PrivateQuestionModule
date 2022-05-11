<?php

namespace Modules\PrivateQuestions\Models;

use App\Models\Setting;
use Domain\Auth\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Domain\Specialists\Models\Specialist;
use Domain\Specialists\Builders\SpecialistBuilder;
use Modules\PrivateQuestions\PrivateQuestionLimiter;
use Modules\PrivateQuestions\Builders\PrivateQuestionBuilder;
use Modules\Notifications\Notifications\Specialist\NewPrivateQuestionNotification;

/**
 * @method PrivateQuestionBuilder query()
 */
class PrivateQuestion extends Model
{
    protected $guarded = [];

    protected $casts = [
        'answered_viewed_at' => 'datetime',
        'question_viewed_at' => 'datetime',
        'answered_at'        => 'datetime',
    ];

    public function specialist()
    {
        return $this->belongsTo(Specialist::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function newEloquentBuilder($query): PrivateQuestionBuilder
    {
        return new PrivateQuestionBuilder($query);
    }

    public function getShowNotificationBadge(Specialist|User $user)
    {
        if ($user instanceof Specialist) {
            return !$this->answer;
        }

        return $this->answer && !$this->answered_viewed_at;
    }

    public function getQuestionTag()
    {
        $text = 'لم يتم الاجابة بعد';
        $icon = 'close.svg';

        if ($this->answer) {
            $text = 'تمت الإجابة';
            $icon = 'correct.svg';
        }

        return [
            'text' => $text,
            'icon' => asset("uploads/$icon"),
        ];
    }

    public static function latestSpecialistForUser(User $user)
    {
        $specialist = $user->conversations()
            ->withoutTrashed()
            ->inSuccessStatusCategory()
            ->latest('due_at')
            ->first()
            ?->specialist;
        if ($specialist && $specialist->active) {
            return $specialist;
        }
        return null;
    }

    public static function getRemainingQuestionsText(User $user, bool $can_ask)
    {
        if (Setting::privateQuestionLimitDisabled()) {
            return 'بإمكانك اضافة سؤال جديد';
        }

        if (!$can_ask) {
            $limit = Setting::value(Setting::PRIVATE_QUESTION_LIMIT);

            return "لا تستطيع اضافة أكثر من {$limit} أسئلة";
        }

        $remaining = PrivateQuestionLimiter::remaining($user);

        return "بإمكانك اضافة $remaining سؤال جديد";
    }

    public static function notificationCountForUser($user)
    {
        return self::query()
            ->where('user_id', $user->id)
            ->whereNotNull('answer')
            ->whereNull('answered_viewed_at')
            ->count();
    }

    public static function notificationCountForSpecialist($specialist)
    {
        return self::query()
            ->forSameOrganization($specialist->organization_id)
            ->whereNull('answer')
            // return empty if not in experiment
            ->when(AvailableSpecialistForQuestion::outOfExperiment($specialist), function ($query) {
                $query->whereNull('id');
            })
            ->where(function (Builder $query) use ($specialist) {
                $query
                    ->whereNull('specialist_id')
                    ->orWhere('specialist_id', $specialist->id);
            })
            ->count();
    }

    public function notifySpecialists()
    {
        if ($this->specialist_id) {
            $this->specialist->notifyDevices(new NewPrivateQuestionNotification($this));
            return null;
        }
        return null;
        AvailableSpecialistForQuestion::query()
            ->with('specialist')
            ->whereHas('specialist', function (SpecialistBuilder $query) {
                $query->filterByOrganization($this->user->organization_id);
            })
            ->get()
            ->each(function (AvailableSpecialistForQuestion $model) {
                $model->specialist->notifyDevices(new NewPrivateQuestionNotification($this));
            });
    }
}
