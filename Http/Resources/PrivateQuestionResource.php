<?php

namespace Modules\PrivateQuestions\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use App\Http\Resources\BaseResource;
use App\Http\Resources\SpecialistProfileResource;
use Modules\PrivateQuestions\Models\PrivateQuestion;

/** @mixin PrivateQuestion */
class PrivateQuestionResource extends BaseResource
{
    /**
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'                      => $this->id,
            'user'                    => $this->whenLoaded('user', function () {
                return new UserResource($this->user);
            }),
            'specialist'              => $this->whenLoaded('specialist', function () {
                return $this->answer ? new SpecialistProfileResource($this->specialist) : null;
            }),
            'specialist_id'           => $this->specialist_id,
            'question'                => $this->question,
            'answer'                  => $this->answer,
            'answered_at'             => $this->answered_at,
            'questioned_at'           => $this->created_at,
            'tag'                     => $this->getQuestionTag(),
            'show_notification_badge' => $this->getShowNotificationBadge($request->user()),
        ];
    }
}
