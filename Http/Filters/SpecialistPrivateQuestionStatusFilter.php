<?php

namespace Modules\PrivateQuestions\Http\Filters;

use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Domain\Specialists\Models\Specialist;
use Modules\PrivateQuestions\Builders\PrivateQuestionBuilder;
use Modules\PrivateQuestions\Models\AvailableSpecialistForQuestion;

class SpecialistPrivateQuestionStatusFilter implements Filter
{
    public function __invoke(Builder $query, $value, string $property)
    {
        /** @var Specialist $specialist */
        $specialist = auth()->user();
        return $query->when($value == 'me', function (PrivateQuestionBuilder $query) use ($specialist) {
            $query->specialistQuestions($specialist);
        })->when($value == 'open', function (PrivateQuestionBuilder $query) use ($specialist) {
            if (AvailableSpecialistForQuestion::outOfExperiment($specialist)) {
                // return 0 questions
                return $query->whereNull('id');
            }
            return $query->openQuestions();
        });
    }
}
