<?php

namespace app\controllers;

use app\services\LoanRequestService;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

class RequestsController extends Controller
{
    /**
     * API-only controller: CSRF is disabled for JSON requests.
     *
     * @var bool
     */
    public $enableCsrfValidation = false;

    /**
     * @return array<string, mixed>
     */
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

    /**
     * Creates a new loan request.
     *
     * @return array<string, mixed>
     */
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

        $service = new LoanRequestService();
        $result = $service->create($data);

        if (($result['result'] ?? false) !== true) {
            Yii::$app->response->statusCode = 400;
            return $result;
        }

        Yii::$app->response->statusCode = 201;
        return $result;
    }
}
