<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

class SeedPrivateQuestionsSettings extends Migration
{
    public function up()
    {
        Setting::firstOrCreate(
            [
                'key' => Setting::PRIVATE_QUESTION_TOOLTIP_DURATION,
            ],
            [
                'value'       => '24 ساعة',
                'type'        => Setting::TEXT_TYPE,
                'description' => 'Tooltip duration text for private question',
                'category'    => Setting::PRIVATE_QUESTION_CATEGORY,
            ]
        );

        Setting::firstOrCreate(
            [
                'key' => Setting::PRIVATE_QUESTION_TEXT,
            ],
            [
                'value'       => 'يمكنك الآن البدء بطرح سؤالك لأخصائينا المتاحين وسيتم الرد عليكم خلال 24 ساعة. بإمكانك طرح 3 أسئلة مجانية شهرياً.',
                'type'        => Setting::TEXT_TYPE,
                'description' => '',
                'category'    => Setting::PRIVATE_QUESTION_CATEGORY,
            ]
        );

        Setting::firstOrCreate(
            [
                'key' => Setting::PRIVATE_QUESTION_CHARACTERS_LIMITATION,
            ],
            [
                'value'       => 300,
                'type'        => Setting::NUMBER_TYPE,
                'description' => '',
                'category'    => Setting::PRIVATE_QUESTION_CATEGORY,
            ]
        );

        Setting::firstOrCreate(
            [
                'key' => Setting::PRIVATE_QUESTION_LIMIT,
            ],
            [
                'value'       => 3,
                'type'        => Setting::NUMBER_TYPE,
                'description' => 'Limit questions per user',
                'category'    => Setting::PRIVATE_QUESTION_CATEGORY,
            ]
        );

        Setting::firstOrCreate(
            [
                'key' => Setting::PRIVATE_QUESTION_KEEP_FOR_SPECIALIST,
            ],
            [
                'value'       => 24,
                'type'        => Setting::NUMBER_TYPE,
                'description' => 'X hours to keep returned user question\'s for specialist',
                'category'    => Setting::PRIVATE_QUESTION_CATEGORY,
            ]
        );
    }
}
