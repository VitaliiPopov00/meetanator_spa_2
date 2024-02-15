<?php

namespace app\controllers;

use yii\filters\auth\HttpBearerAuth;
use yii\rest\ActiveController;
use yii\web\UploadedFile;


class ImgController extends ActiveController
{
    public $modelClass = '';
    public $enableCsrfValidation = false;


    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $auth = $behaviors['authenticator'];
        unset($behaviors['authenticator']);
        
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::class,
            'cors' => [
                'Origin' => [
                    (isset($_SERVER['HTTP_ORIGIN'])
                        ? $_SERVER['HTTP_ORIGIN']
                        : 'http://' . $_SERVER['REMOTE_ADDR']
                    ),
                ],
                'Access-Control-Request-Method' => ['content-type', 'Authorization'],
                'Access-Control-Request-Headers' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
            ],
            'actions' => [
                'login' => [
                    'Access-Control-Allow-Credentials' => true,
                ]
            ]
        ];

        $auth = [
            'class' => HttpBearerAuth::class,
            'only' => [],
        ];
        
        $behaviors['authenticator'] = $auth;

        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();

        unset($actions['index'], $actions['create'], $actions['view'], $actions['update'], $actions['delete']);

        return $actions;
    }

    public function actionShow($hash, $filename)
    {
        if ($meet = Meet::findOne(['hash' => $hash])) {
            $imgDB = FileMeet::findOne(['meet_id' => $meet->id, 'filename' => $filename]);
            $path = Yii::getAlias('@app') . '/upload/' . $meet->hash . '/image/';

            if ($imgDB && file_exists($path . $imgDB->filename)) {
                header('Content-Type: image/png');
                return Yii::$app->response->sendFile($path . $imgDB->filename);
            } else {
                Yii::$app->response->statusCode = 404;

                return $this->asJson([
                    'error' => [
                        'code' => 404,
                        'message' => 'Не найдено',
                    ],
                ]);
            }
        } else {
            Yii::$app->response->statusCode = 404;

            return $this->asJson([
                'error' => [
                    'code' => 404,
                    'message' => 'Не найдено',
                ],
            ]);
        }
    }


}