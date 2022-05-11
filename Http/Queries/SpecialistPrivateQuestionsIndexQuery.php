<?php

namespace Modules\PrivateQuestions\Http\Queries;

use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Modules\PrivateQuestions\Models\PrivateQuestion;
use Modules\PrivateQuestions\Http\Filters\SpecialistPrivateQuestionStatusFilter;

class SpecialistPrivateQuestionsIndexQuery extends QueryBuilder
{
    public function __construct(Request $request)
    {
        $query = PrivateQuestion::query()->forSameOrganization($request->user()->organization->id);

        parent::__construct($query, $request);

        $query->with(['user']);

        $this->allowedFilters([
            AllowedFilter::custom('assignee', new SpecialistPrivateQuestionStatusFilter())->default('open'),
        ]);
    }
}
