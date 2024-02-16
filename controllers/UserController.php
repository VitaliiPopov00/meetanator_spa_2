<?php

namespace app\controllers;

use yii\filters\auth\HttpBearerAuth;
use yii\rest\ActiveController;
use app\models\DateMeet;
use app\models\FileMeet;
use app\models\Meet;
use app\models\Role;
use app\models\TimeMeet;
use app\models\User;
use Yii;

class UserController extends ActiveController
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
                'Access-Control-Request-Headers' => ['content-type', 'Authorization'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
            ],
        ];

        $auth = [
            'class' => HttpBearerAuth::class,
            'only' => ['logout', 'info'],
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

    public function actionLogin()
    {
        $user = new User();
        $user->scenario = User::SCENARIO_LOGIN;

        if ($user->load(Yii::$app->request->post(), '') && $user->validate()) {
            $password = $user->password; // запоминаем переданный пароль от клиента
            $users = User::findAll(['login' => $user->login, 'role_id' => Role::getRoleIDByTitle('leader')]); // находим всех лидеров в БД (незарегистрированные и зарегистрированный)
            $user = null; // создаем "пустого" пользователя, в которого в дальнейшем будут прогружены данные зарегистрированного пользователя (если найдется)

            foreach ($users as $userFromDB) {
                if ($userFromDB->password) { // ищем пользователя, который будет зарегистрирован
                    $user = $userFromDB; // прогружаем данные в "пустышку"
                    break;
                }
            }

            if ($user) { // если нашли зарегистрированного пользователя с таким логином, то проводим авторизацию
                if ($user->validatePassword($password)) {
                    $user->setToken();
                    $user->save(false);

                    Yii::$app->response->statusCode = 200;

                    return $this->asJson([
                        'data' => [
                            'token' => $user->token,
                        ],
                    ]);
                } else {
                    Yii::$app->response->statusCode = 401;

                    return $this->asJson([
                        'error' => [
                            'code' => 401,
                            'message' => 'Unauthorized',
                        ],
                    ]);
                }
            } else { // не нашли зарегистрированного пользователя с таким логином
                Yii::$app->response->statusCode = 401;

                return $this->asJson([
                    'error' => [
                        'code' => 401,
                        'message' => 'Unauthorized',
                    ],
                ]);
            }
        } else {
            Yii::$app->response->statusCode = 422;

            return $this->asJson([
                'error' => [
                    'code' => 422,
                    'message' => 'Validation error',
                    'errors' => $user->errors,
                ],
            ]);
        }
    }

    public function actionInfo()
    {
        Yii::$app->response->statusCode = 200;

        return $this->asJson([
            'data' => [
                'user' => User::getInfo(Yii::$app->user->identity->id),
            ],
        ]);
    }

    public function actionLogout()
    {
        $user = Yii::$app->user->identity;
        $user->token = null;
        $user->save(false);

        Yii::$app->response->statusCode = 204;
    }

}