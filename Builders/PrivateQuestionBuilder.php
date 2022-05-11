<?php

namespace Modules\PrivateQuestions\Builders;

use Domain\Auth\Builders\UserBuilder;
use Domain\Specialists\Models\Specialist;
use Illuminate\Database\Eloquent\Builder;

class PrivateQuestionBuilder extends Builder
{
    public function specialistQuestions(Specialist $specialist): self
    {
        return $this->where('specialist_id', $specialist->id)
            ->orderByRaw('answered_at desc NULLS first');
    }

    public function openQuestions(): self
    {
        return $this->whereNull('specialist_id')->oldest();
    }

    public function notAnswered(): self
    {
        return $this->whereNull('answer');
    }

    public function forSameOrganization($organizationId)
    {
        return $this->whereHas('user', function (UserBuilder $query) use ($organizationId) {
            $query->filterByOrganization($organizationId);
        });
    }
}
