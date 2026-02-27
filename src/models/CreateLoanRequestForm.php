<?php

namespace app\models;

use yii\base\Model;

/**
 * Input model for POST /requests payload validation.
 */
class CreateLoanRequestForm extends Model
{
    public ?int $user_id = null;
    public ?int $amount = null;
    public ?int $term = null;

    /**
     * @return array<int, array<int|string, mixed>>
     */
    public function rules(): array
    {
        return [
            [['user_id', 'amount', 'term'], 'required'],
            [['user_id', 'amount', 'term'], 'integer', 'min' => 1],
        ];
    }
}
