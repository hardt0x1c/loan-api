<?php

namespace app\services;

use app\models\CreateLoanRequestForm;
use app\models\LoanRequest;
use yii\web\Request;

/**
 * Business operations for loan request creation.
 */
class LoanRequestService
{
    /**
     * Creates a new loan request from HTTP request data.
     *
     * @param Request $request
     * @return int|null Created loan request ID or null on validation/business failure.
     */
    public function createFromRequest(Request $request): ?int
    {
        if (!$this->isJsonRequest($request)) {
            return null;
        }

        return $this->createFromPayload($request->getBodyParams());
    }

    /**
     * Creates a new loan request from payload data.
     *
     * @param mixed $payload
     * @return int|null Created loan request ID or null on validation/business failure.
     */
    public function createFromPayload($payload): ?int
    {
        if (!is_array($payload)) {
            return null;
        }

        $form = new CreateLoanRequestForm();
        $form->load($payload, '');

        if (!$form->validate()) {
            return null;
        }

        $hasApproved = LoanRequest::find()->where([
            'user_id' => $form->user_id,
            'status' => LoanRequest::STATUS_APPROVED,
        ])->exists();

        if ($hasApproved) {
            return null;
        }

        $loanRequest = new LoanRequest([
            'user_id' => $form->user_id,
            'amount' => $form->amount,
            'term' => $form->term,
            'status' => LoanRequest::STATUS_PENDING,
        ]);

        if (!$loanRequest->save(false)) {
            return null;
        }

        return (int) $loanRequest->id;
    }

    /**
     * @param Request $request
     * @return bool
     */
    private function isJsonRequest(Request $request): bool
    {
        return stripos((string) $request->headers->get('Content-Type', ''), 'application/json') === 0;
    }
}
