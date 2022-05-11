<?php

namespace Modules\PrivateQuestions\Http\Controllers;

use Domain\Auth\Models\User;
use App\Http\Controllers\Controller;
use Modules\PrivateQuestions\Models\PrivateQuestion;
use Modules\PrivateQuestions\Policies\PrivateQuestionPolicy;
use Modules\PrivateQuestions\Http\Resources\PrivateQuestionResource;
use Modules\PrivateQuestions\Http\Requests\StorePrivateQuestionRequest;

class UserPrivateQuestionsController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(PrivateQuestion::class, 'privateQuestion');
    }

    public function index()
    {
        /** @var User $user */
        $user = auth()->user();

        $private_questions = PrivateQuestion::query()
            ->where('user_id', $user->id)
            ->with('specialist')
            ->latest()
            ->get();

        $can_ask = (new PrivateQuestionPolicy())->create($user);

        return PrivateQuestionResource::collection($private_questions)
            ->additional([
                'meta' => [
                    'can_ask'              => $can_ask,
                    'remain_question_text' => PrivateQuestion::getRemainingQuestionsText($user, $can_ask),
                ],
            ]);
    }

    public function store(StorePrivateQuestionRequest $storePrivateQuestionRequest)
    {
        /** @var User $user */
        $user = auth()->user();

        $specialist_id = PrivateQuestion::latestSpecialistForUser($user)?->id;
        $question = PrivateQuestion::query()
            ->create([
                'user_id'       => $user->id,
                'specialist_id' => $specialist_id,
                'assigned_at'   => $specialist_id ? now() : null,
                'question'      => $storePrivateQuestionRequest->get('text'),
            ]);

        dispatch(function () use ($question) {
            $question->notifySpecialists();
        });

        $can_ask = (new PrivateQuestionPolicy())->create($user);

        return (new PrivateQuestionResource($question))->additional([
            'meta' => [
                'can_ask'              => $can_ask,
                'remain_question_text' => PrivateQuestion::getRemainingQuestionsText($user, $can_ask),
            ],
        ]);
    }

    public function show(User $user, PrivateQuestion $privateQuestion)
    {
        $can_ask = (new PrivateQuestionPolicy())->create($user);

        return (new PrivateQuestionResource($privateQuestion->load('specialist')))->additional([
            'meta' => [
                'can_ask'              => $can_ask,
                'remain_question_text' => PrivateQuestion::getRemainingQuestionsText($user, $can_ask),
            ],
        ]);
    }

    public function read(User $user, PrivateQuestion $privateQuestion)
    {
        $privateQuestion->update(['answered_viewed_at' => now()]);

        return $this->success();
    }

    public function notifications(User $user)
    {
        return response()->json([
            'data' => [
                'count' => PrivateQuestion::notificationCountForUser($user),
            ],
        ]);
    }
}
