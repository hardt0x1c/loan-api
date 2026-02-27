<?php

namespace app\models;

use yii\base\Model;

/**
 * Input model for GET /processor query validation.
 */
class ProcessRequestsForm extends Model
{
    public const MAX_DELAY_SECONDS = 300;

    public int $delay = 0;

    /**
     * @return array<int, array<int|string, mixed>>
     */
    public function rules(): array
    {
        return [
            ['delay', 'default', 'value' => 0],
            ['delay', 'integer', 'min' => 0, 'max' => self::MAX_DELAY_SECONDS],
        ];
    }
}
