<?php

namespace Modules\PrivateQuestions\Http\Controllers;

use App\Http\Controllers\Controller;
use Domain\Specialists\Models\Specialist;
use Modules\PrivateQuestions\Models\PrivateQuestion;
use Modules\PrivateQuestions\Models\AvailableSpecialistForQuestion;
use Modules\PrivateQuestions\Http\Resources\PrivateQuestionResource;
use Modules\PrivateQuestions\Http\Queries\SpecialistPrivateQuestionsIndexQuery;
use Modules\Notifications\Notifications\User\PrivateQuestionAnsweredNotification;

class SpecialistPrivateQuestionsController extends Controller
{
    public function index(SpecialistPrivateQuestionsIndexQuery $specialistPrivateQuestionsIndexQuery)
    {
        return PrivateQuestionResource::collection($specialistPrivateQuestionsIndexQuery->paginate());
    }

    public function acquire(Specialist $specialist, PrivateQuestion $privateQuestion)
    {
        $this->authorize('acquire', $privateQuestion);

        $privateQuestion->update(['specialist_id' => $specialist->id, 'assigned_at' => now()]);

        return $this->success();
    }

    public function show(Specialist $specialist, PrivateQuestion $privateQuestion)
    {
        return (new PrivateQuestionResource($privateQuestion->load('user')));
    }

    public function answer(Specialist $specialist, PrivateQuestion $privateQuestion)
    {
        $this->authorize('answer', $privateQuestion);

        $privateQuestion->update([
            'answer'      => request('text'),
            'answered_at' => now(),
        ]);

        $privateQuestion = $privateQuestion->fresh();

        $privateQuestion->user->notify(new PrivateQuestionAnsweredNotification($privateQuestion));
        $privateQuestion->user->notifyDevices(new PrivateQuestionAnsweredNotification($privateQuestion));

        return new PrivateQuestionResource($privateQuestion->load('user')->unsetRelation('specialist'));
    }

    public function notifications(Specialist $specialist)
    {
        return response()->json([
            'data' => [
                'count' => PrivateQuestion::notificationCountForSpecialist($specialist),

                'show_me_notification_badge' => PrivateQuestion::query()
                    ->forSameOrganization($specialist->organization_id)
                    ->notAnswered()
                    ->when(AvailableSpecialistForQuestion::outOfExperiment($specialist), function ($query) {
                        $query->whereNull('id');
                    })
                    ->specialistQuestions($specialist)
                    ->exists(),

                'show_open_notification_badge' => PrivateQuestion::query()
                    ->forSameOrganization($specialist->organization_id)
                    ->notAnswered()
                    ->openQuestions()
                    ->when(AvailableSpecialistForQuestion::outOfExperiment($specialist), function ($query) {
                        $query->whereNull('id');
                    })
                    ->exists(),
            ],
        ]);
    }
}
