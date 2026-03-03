<?php

namespace app\controllers;

use app\models\ProcessRequestsForm;
use app\services\LoanProcessorService;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

class ProcessorController extends Controller
{
    /**
     * @return array<string, mixed>
     */
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

    /**
     * Processes pending loan requests with optional artificial delay.
     *
     * @return array<string, mixed>
     */
    public function actionIndex(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $form = new ProcessRequestsForm();
        $form->load(Yii::$app->request->queryParams, '');
        if (!$form->validate()) {
            Yii::$app->response->statusCode = 400;
            return ['result' => false];
        }

        $service = new LoanProcessorService();
        $service->processPending((int) $form->delay, 0);

        Yii::$app->response->statusCode = 200;
        return ['result' => true];
    }
}
