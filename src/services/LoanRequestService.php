<?php

namespace app\services;

use app\models\CreateLoanRequestForm;
use app\models\LoanRequest;

/**
 * Business operations for loan request creation.
 */
class LoanRequestService
{
    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function create(array $payload): array
    {
        $form = new CreateLoanRequestForm();
        $form->load($payload, '');

        if (!$form->validate()) {
            return [
                'result' => false,
                'error' => 'invalid_payload',
                'details' => $form->getErrors(),
            ];
        }

        $hasApproved = LoanRequest::find()->where([
            'user_id' => $form->user_id,
            'status' => LoanRequest::STATUS_APPROVED,
        ])->exists();

        if ($hasApproved) {
            return [
                'result' => false,
                'error' => 'approved_request_already_exists',
            ];
        }

        $loanRequest = new LoanRequest([
            'user_id' => $form->user_id,
            'amount' => $form->amount,
            'term' => $form->term,
            'status' => LoanRequest::STATUS_PENDING,
        ]);

        if (!$loanRequest->save(false)) {
            return [
                'result' => false,
                'error' => 'create_failed',
            ];
        }

        return [
            'result' => true,
            'id' => (int) $loanRequest->id,
        ];
    }
}
