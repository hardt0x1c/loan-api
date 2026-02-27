<?php

namespace app\models;

use yii\db\ActiveRecord;

class LoanRequest extends ActiveRecord
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_DECLINED = 'declined';

    /**
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%loan_requests}}';
    }

    /**
     * @return array<int, array<int|string, mixed>>
     */
    public function rules(): array
    {
        return [
            [['user_id', 'amount', 'term'], 'required'],
            [['user_id', 'amount', 'term'], 'integer', 'min' => 1],
            ['status', 'default', 'value' => self::STATUS_PENDING],
            ['status', 'in', 'range' => [self::STATUS_PENDING, self::STATUS_PROCESSING, self::STATUS_APPROVED, self::STATUS_DECLINED]],
        ];
    }

    /**
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * @return bool
     */
    public function isProcessing(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    /**
     * @return bool
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * @return bool
     */
    public function isDeclined(): bool
    {
        return $this->status === self::STATUS_DECLINED;
    }
}
