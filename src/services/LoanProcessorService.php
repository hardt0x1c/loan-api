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
    /**
     * @param int $delaySeconds Artificial delay before decision making.
     */
    public function processPending(int $delaySeconds = 0): void
    {
        $delaySeconds = max(0, $delaySeconds);

        while (true) {
            $requestId = $this->acquirePendingRequestId();
            if ($requestId === null) {
                break;
            }

            if ($delaySeconds > 0) {
                sleep($delaySeconds);
            }

            $this->finalizeRequest($requestId);
        }
    }

    /**
     * Atomically grabs one pending request and marks it as processing.
     *
     * @return int|null
     */
    private function acquirePendingRequestId(): ?int
    {
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();

        try {
            $row = $db->createCommand(
                'WITH candidate AS ('
                . ' SELECT id FROM {{%loan_requests}}'
                . ' WHERE status = :pending'
                . ' ORDER BY id ASC'
                . ' FOR UPDATE SKIP LOCKED'
                . ' LIMIT 1'
                . ') '
                . 'UPDATE {{%loan_requests}} AS target '
                . 'SET status = :processing '
                . 'FROM candidate '
                . 'WHERE target.id = candidate.id '
                . 'RETURNING target.id',
                [
                    ':pending' => LoanRequest::STATUS_PENDING,
                    ':processing' => LoanRequest::STATUS_PROCESSING,
                ]
            )->queryOne();

            $transaction->commit();

            if ($row === false || $row === null) {
                return null;
            }

            return (int) $row['id'];
        } catch (Throwable $e) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }

            Yii::error($e->getMessage(), __METHOD__);
            return null;
        }
    }

    /**
     * @param int $requestId
     */
    private function finalizeRequest(int $requestId): void
    {
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();

        try {
            $loanRequest = LoanRequest::find()
                ->where([
                    'id' => $requestId,
                    'status' => LoanRequest::STATUS_PROCESSING,
                ])
                ->one();

            if ($loanRequest === null) {
                $transaction->commit();
                return;
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
        }
    }

    /**
     * @return bool
     */
    private function shouldApprove(): bool
    {
        return random_int(1, 10) === 1;
    }

    private function tryApproveOrDecline(LoanRequest $loanRequest): void
    {
        try {
            $this->setApproved($loanRequest);
        } catch (IntegrityException $e) {
            $this->setDeclined($loanRequest);
        }
    }

    /**
     * @param LoanRequest $loanRequest
     */
    private function setApproved(LoanRequest $loanRequest): void
    {
        $loanRequest->status = LoanRequest::STATUS_APPROVED;
        $loanRequest->processed_at = new Expression('NOW()');

        if ($loanRequest->update(false, ['status', 'processed_at']) === false) {
            throw new StaleObjectException('Failed to set approved status.');
        }
    }

    /**
     * @param LoanRequest $loanRequest
     */
    private function setDeclined(LoanRequest $loanRequest): void
    {
        $loanRequest->status = LoanRequest::STATUS_DECLINED;
        $loanRequest->processed_at = new Expression('NOW()');

        if ($loanRequest->update(false, ['status', 'processed_at']) === false) {
            throw new StaleObjectException('Failed to set declined status.');
        }
    }
}
