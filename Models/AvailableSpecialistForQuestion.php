<?php

namespace Modules\PrivateQuestions\Models;

use Illuminate\Database\Eloquent\Model;
use Domain\Specialists\Models\Specialist;

class AvailableSpecialistForQuestion extends Model
{
    protected $guarded = [];

    /**
     * @return Specialist
     */
    public function specialist()
    {
        return $this->belongsTo(Specialist::class);
    }

    public static function outOfExperiment($specialist)
    {
        return AvailableSpecialistForQuestion::query()->where('specialist_id', $specialist->id)->doesntExist();
    }
}
