<?php

namespace app\controllers;

use app\models\LoanRequest;
use Yii;
use yii\db\IntegrityException;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

class RequestsController extends Controller
{
    public $enableCsrfValidation = false;

    public function behaviors(): array
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'create' => ['post'],
                ],
            ],
        ];
    }

    public function actionCreate(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (stripos((string) Yii::$app->request->headers->get('Content-Type', ''), 'application/json') !== 0) {
            Yii::$app->response->statusCode = 400;
            return ['result' => false];
        }

        $data = Yii::$app->request->getBodyParams();
        if (!is_array($data)) {
            Yii::$app->response->statusCode = 400;
            return ['result' => false];
        }

        $loanRequest = new LoanRequest();
        $loanRequest->user_id = $data['user_id'] ?? null;
        $loanRequest->amount = $data['amount'] ?? null;
        $loanRequest->term = $data['term'] ?? null;
        $loanRequest->status = LoanRequest::STATUS_PENDING;

        if (!$loanRequest->validate(['user_id', 'amount', 'term'])) {
            Yii::$app->response->statusCode = 400;
            return ['result' => false];
        }

        $hasApproved = LoanRequest::find()
            ->where([
                'user_id' => $loanRequest->user_id,
                'status' => LoanRequest::STATUS_APPROVED,
            ])
            ->exists();

        if ($hasApproved) {
            Yii::$app->response->statusCode = 400;
            return ['result' => false];
        }

        try {
            if (!$loanRequest->save(false)) {
                Yii::$app->response->statusCode = 400;
                return ['result' => false];
            }
        } catch (IntegrityException $e) {
            Yii::$app->response->statusCode = 400;
            return ['result' => false];
        }

        Yii::$app->response->statusCode = 201;

        return [
            'result' => true,
            'id' => (int) $loanRequest->id,
        ];
    }
}
