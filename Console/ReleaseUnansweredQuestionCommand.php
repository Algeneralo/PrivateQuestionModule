<?php

namespace Modules\PrivateQuestions\Console;

use App\Models\Setting;
use Illuminate\Console\Command;
use Modules\PrivateQuestions\Models\PrivateQuestion;

class ReleaseUnansweredQuestionCommand extends Command
{
    protected $signature = 'famcare:release-unanswered-question';

    protected $description = 'Release unanswered question after x hours';

    public function handle()
    {
        $hours = Setting::value(Setting::PRIVATE_QUESTION_KEEP_FOR_SPECIALIST);
        $time = now()->subHours($hours);

        PrivateQuestion::query()
            ->whereNull('answer')
            ->whereNotNull('specialist_id')
            ->whereNotNull('assigned_at')
            ->where('assigned_at', '<=', $time)
            ->update(['specialist_id' => null]);
    }
}
