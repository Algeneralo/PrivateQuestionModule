<?php

namespace Modules\PrivateQuestions\Nova\Actions;

use Illuminate\Bus\Queueable;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Actions\Action;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\ActionFields;
use Illuminate\Queue\InteractsWithQueue;
use Domain\Specialists\Models\Specialist;
use Modules\PrivateQuestions\Models\PrivateQuestion;
use Modules\Notifications\Notifications\Specialist\NewPrivateQuestionNotification;

class AssignPrivateQuestion extends Action
{
    use InteractsWithQueue, Queueable;

    public function name()
    {
        return __('Assign to specialist');
    }

    /**
     * Perform the action on the given models.
     *
     * @param ActionFields $fields
     * @param Collection $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        /** @var PrivateQuestion $privateQuestion */
        $privateQuestion = $models->first();
        $specialist = Specialist::find($fields->get('specialist_id'));

        $privateQuestion->update([
            'specialist_id' => $specialist->id,
            'assigned_at' => now()
        ]);
        $specialist->notifyDevices(new NewPrivateQuestionNotification($privateQuestion->fresh()->load('user')));
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        $options = Specialist::query()
            ->active()
            ->approved()
            ->pluck('id', 'name')
            ->flip()
            ->toArray();

        return [
            Select::make(__('Specialist'), 'specialist_id')
                ->options($options)
                ->rules('required'),
        ];
    }
}
