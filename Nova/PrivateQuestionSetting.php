<?php

namespace Modules\PrivateQuestions\Nova;

use App\Nova\Setting;
use Illuminate\Http\Request;
use Laravel\Nova\Http\Requests\NovaRequest;

class PrivateQuestionSetting extends Setting
{

    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query->where('category', \App\Models\Setting::PRIVATE_QUESTION_CATEGORY);
    }

    public function filters(Request $request)
    {
        return [];
    }
}
