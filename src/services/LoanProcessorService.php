<?php

namespace app\services;

use app\models\LoanRequest;
use Throwable;
use Yii;
use yii\db\Expression;
use yii\db\IntegrityException;
use yii\db\StaleObjectException;

class LoanProcessorService
{
    public function processPending(int $delaySeconds = 0): void
    {
        $db = Yii::$app->db;
        $delaySeconds = max(0, $delaySeconds);

        while (true) {
            $transaction = $db->beginTransaction();

            try {
                $row = $db->createCommand(
                    'SELECT id FROM {{%loan_requests}} '
                    . 'WHERE status = :pending '
                    . 'ORDER BY id ASC '
                    . 'FOR UPDATE SKIP LOCKED '
                    . 'LIMIT 1',
                    [':pending' => LoanRequest::STATUS_PENDING]
                )->queryOne();

                if ($row === false || $row === null) {
                    $transaction->commit();
                    break;
                }

                $loanRequest = LoanRequest::findOne((int) $row['id']);
                if ($loanRequest === null || !$loanRequest->isPending()) {
                    $transaction->commit();
                    continue;
                }

                if ($delaySeconds > 0) {
                    sleep($delaySeconds);
                }

                if ($this->shouldApprove()) {
                    $this->tryApproveOrDecline($loanRequest);
                } else {
                    $this->setDeclined($loanRequest);
                }

                $transaction->commit();
            } catch (Throwable $e) {
                if ($transaction->isActive) {
                    $transaction->rollBack();
                }

                Yii::error($e->getMessage(), __METHOD__);
                break;
            }
        }
    }

    private function shouldApprove(): bool
    {
        return random_int(1, 10) === 1;
    }

    private function tryApproveOrDecline(LoanRequest $loanRequest): void
    {
        $db = Yii::$app->db;

        $savepointTransaction = $db->beginTransaction();
        try {
            $this->setApproved($loanRequest);
            $savepointTransaction->commit();
        } catch (IntegrityException $e) {
            if ($savepointTransaction->isActive) {
                $savepointTransaction->rollBack();
            }

            $this->setDeclined($loanRequest);
        }
    }

    private function setApproved(LoanRequest $loanRequest): void
    {
        $loanRequest->status = LoanRequest::STATUS_APPROVED;
        $loanRequest->processed_at = new Expression('NOW()');

        if ($loanRequest->update(false, ['status', 'processed_at']) === false) {
            throw new StaleObjectException('Failed to set approved status.');
        }
    }

    private function setDeclined(LoanRequest $loanRequest): void
    {
        $loanRequest->status = LoanRequest::STATUS_DECLINED;
        $loanRequest->processed_at = new Expression('NOW()');

        if ($loanRequest->update(false, ['status', 'processed_at']) === false) {
            throw new StaleObjectException('Failed to set declined status.');
        }
    }
}
