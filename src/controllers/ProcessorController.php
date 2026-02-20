<?php

namespace app\controllers;

use app\services\LoanProcessorService;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

class ProcessorController extends Controller
{
    public function behaviors(): array
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'index' => ['get'],
                ],
            ],
        ];
    }

    public function actionIndex(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        Yii::$app->response->statusCode = 200;

        $delay = (int) Yii::$app->request->get('delay', 0);
        $service = new LoanProcessorService();
        $service->processPending($delay);

        return ['result' => true];
    }
}
