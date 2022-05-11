<?php

namespace Modules\PrivateQuestions\Nova;

use App\Nova\User;
use App\Nova\Resource;
use App\Nova\Specialist;
use Illuminate\Support\Str;
use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Monaye\NovaTippyField\Tippy;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Http\Requests\ActionRequest;
use App\Nova\Filters\Packages\CreatedAtFilter;
use Modules\PrivateQuestions\Nova\Actions\AssignPrivateQuestion;
use Modules\PrivateQuestions\Nova\Filters\AnsweredQuestionFilter;

class PrivateQuestion extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var  string
     */
    public static $model = \Modules\PrivateQuestions\Models\PrivateQuestion::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var  string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var  array
     */
    public static $search = [
        'id', 'user_id', 'specialist_id',
    ];

    /**
     * Get the displayable label of the resource.
     *
     * @return  string
     */
    public static function label()
    {
        return __('Private Questions');
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return  string
     */
    public static function singularLabel()
    {
        return __('Private Question');
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param Request $request
     * @return  array
     */
    public function fields(Request $request)
    {
        return [
            ID::make(__('Id'), 'id')
                ->rules('required')
                ->sortable(),
            BelongsTo::make(__('User'), 'user', User::class)
                ->searchable()
                ->sortable(),
            Boolean::make(__('Returned Client'), function (\Modules\PrivateQuestions\Models\PrivateQuestion $model) {
                return $model->user->lastSpecialist();
            })
                ->sortable(),
            BelongsTo::make(__('Specialist'), 'specialist', Specialist::class)
                ->searchable()
                ->sortable(),
            Tippy::make(__('Question'))
                ->text(Str::limit($this->question))
                ->tipContent($this->question)
                ->shouldShow(),
            Textarea::make(__('Answer'), 'answer')
                ->alwaysShow(),
            DateTime::make(__('Questioned At'), 'created_at')
                ->sortable(),
            DateTime::make(__('Answered At'), 'answered_at')
                ->sortable(),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param Request $request
     * @return  array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param Request $request
     * @return  array
     */
    public function filters(Request $request)
    {
        return [
            new AnsweredQuestionFilter(),
            new CreatedAtFilter(),
        ];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param Request $request
     * @return  array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param Request $request
     * @return  array
     */
    public function actions(Request $request)
    {
        return [
            (new AssignPrivateQuestion())
                ->canSee(function ($request) {
                    if ($request instanceof ActionRequest) {
                        return true;
                    }
                    /** @var \Modules\PrivateQuestions\Models\PrivateQuestion $privateQuestion */
                    $privateQuestion = $this->resource;

                    return is_null($privateQuestion->answer);
                })
                ->onlyOnTableRow(),
        ];
    }

    public static function authorizedToCreate(Request $request)
    {
        return false;
    }

    public function authorizedToUpdate(Request $request)
    {
        if ($request instanceof ActionRequest) {
            return true;
        }

        return false;
    }
}
