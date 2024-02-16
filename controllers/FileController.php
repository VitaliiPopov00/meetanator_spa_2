<?php

namespace app\controllers;

use yii\filters\auth\HttpBearerAuth;
use yii\rest\ActiveController;
use yii\web\UploadedFile;
use app\models\DateMeet;
use app\models\FileMeet;
use app\models\Meet;
use app\models\Role;
use app\models\TimeMeet;
use app\models\User;
use Yii;


class FileController extends ActiveController
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
                    (
                        isset($_SERVER['HTTP_ORIGIN'])
                        ? $_SERVER['HTTP_ORIGIN']
                        : 'http://' . $_SERVER['REMOTE_ADDR']
                    ),
                ],
                'Access-Control-Request-Method' => ['content-type', 'Authorization'],
                'Access-Control-Request-Headers' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
            ],
        ];

        $auth = [
            'class' => HttpBearerAuth::class,
            'only' => [''],
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
            $fileDB = FileMeet::findOne(['meet_id' => $meet->id, 'filename' => $filename]);
            $path = Yii::getAlias('@app') . '/upload/' . $meet->hash . '/info/';

            if ($fileDB && file_exists($path . $fileDB->filename)) {
                return Yii::$app->response->sendSteamAsFile(fopen($path . $fileDB->filename, 'r'), $fileDB->filename);
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