<?php

namespace Modules\PrivateQuestions\Http\Requests;

use App\Http\Requests\FormRequest;

class StorePrivateQuestionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'text' => 'required',
        ];
    }
}
