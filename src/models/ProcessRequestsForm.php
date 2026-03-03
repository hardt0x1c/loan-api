<?php

namespace app\models;

use yii\base\Model;

/**
 * Input model for GET /processor query validation.
 */
class ProcessRequestsForm extends Model
{
    public ?int $delay = null;

    /**
     * @return array<int, array<int|string, mixed>>
     */
    public function rules(): array
    {
        return [
            ['delay', 'required'],
            ['delay', 'integer', 'min' => 0],
        ];
    }
}
