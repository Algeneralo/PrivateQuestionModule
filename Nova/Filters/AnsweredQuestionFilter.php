<?php

namespace Modules\PrivateQuestions\Nova\Filters;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class AnsweredQuestionFilter extends Filter
{
    public $component = 'select-filter';

    public function name()
    {
        return __('Answered status');
    }

    /**
     * Apply the filter to the given query.
     *
     * @param Request $request
     * @param Builder $query
     * @param mixed $value
     * @return Builder
     */
    public function apply(Request $request, $query, $value)
    {
        $query->{$value}('answer');
    }

    public function options(Request $request)
    {
        return [
            __('Answered')     => 'whereNotNull',
            __('Not Answered') => 'whereNull',
        ];
    }
}
