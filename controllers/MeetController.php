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

class MeetController extends ActiveController
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

    public function actionNew()
    {
        $meet = new Meet();
        $meet->scenario = Meet::SCENARIO_CREATE_MEET;
        $meet->load(Yii::$app->request->post(), '');
        $meet->validate();

        $user = new User();
        $user->scenario = User::SCENARIO_CREATE_MEET;
        $user->load(Yii::$app->request->post(), '');
        $user->validate();

        if (!$meet->hasErrors() && !$user->hasErrors()) {
            if ($user->password) { // если пароль был передан (логин 100% уникален среди зарегистрированных пользователей, т.к прошла валидация у пользователя на уникальность логина, при присутствии пароля)
                $user->role_id = Role::getRoleIDByTitle('leader'); // создаем нового зарегистрированного пользтователя
                $user->setPasswordHash();                          // создаем нового зарегистрированного пользтователя 
                $user->save(false);                                // создаем нового зарегистрированного пользтователя
            } else { // если пароль не был передан, то пытаемся найти зарегистрированного пользователя с таким логином либо создать нового незарегистрированного
                $users = User::findAll(['login' => $user->login, 'role_id' => Role::getRoleIDByTitle('leader')]); // получаем всех лидеров из БД с таким логином
                $user = null; // создаем "пустого" пользователя, в которого будут прогружены данные зарегистрированного пользователя, либо будет создан новый незарегистрированный

                foreach ($users as $userFromDB) {
                    if ($userFromDB->password) { // ищем пользователя, который будет зарегистрирован
                        $user = $userFromDB; // прогружаем данные в "пустышку"
                        break;
                    }
                }

                if (!$user) { // если с переданным логином не сущетсвует зарегстрированного пользователя, то создаем нового незарегистрированного
                    $user = new User();                                // создаем нового незарегистрированного пользтователя
                    $user->role_id = Role::getRoleIDByTitle('leader'); // создаем нового незарегистрированного пользтователя
                    $user->load(Yii::$app->request->post(), '');       // создаем нового незарегистрированного пользтователя
                    $user->save(false);                                // создаем нового незарегистрированного пользтователя
                }
            }

            $meet->user_id = $user->id;    // загружаем во встречу необходимые данные
            $meet->setHashForMeet();       // загружаем во встречу необходимые данные
            $meet->setHashForLeaderMeet(); // загружаем во встречу необходимые данные
    
            if ($meet->save()) {
                DateMeet::setDateMeet($meet->id, $meet->dates); // сохранение в БД переданных дат
                TimeMeet::setClearIntervalForUser($user->id, $meet->id, $meet->start, $meet->end, count($meet->dates)); // создание "пустых" интервалов в БД для лидера
    
                Yii::$app->response->statusCode = 200;
    
                return $this->asJson([
                    'data' => [
                        'meet' => [
                            'hash' => $meet->hash,
                            'hashLeader' => $meet->hash_leader,
                        ],
                        'user' => [
                            'id' => $user->id,
                        ]
                    ]
                ]);
            }
        } else {
            Yii::$app->response->statusCode = 422;

            return $this->asJson([
                'error' => [
                    'code' => 422,
                    'message' => 'Validation error',
                    'errors' => [...$meet->errors, ...$user->errors],
                ],
            ]);
        }
    }

    public function actionLogin($hash)
    {
        if (($meet = Meet::findOne(['hash' => $hash]))) {
            if (!$meet->block) {
                $user = new User();
                
                if ($user->load(Yii::$app->request->post(), '') && $user->validate()) {
                    if (($userInMeet = $meet->userInMeet($user->login))) { // если пользователь с переданным логином уже есть во встрече
                        if ($userInMeet->password) { // если пользователь во встрече был зарегистрирован, то проводим авторизацию
                            if ($user->password && $userInMeet->validatePassword($user->password)) {
                                $result = [
                                    'data' => [
                                        'user' => [
                                            'login' => $userInMeet->login,
                                            'id' => $userInMeet->id,
                                            'isLeader' => ($userInMeet->getRole()->one()->title == 'leader' ? true : false),
                                        ],
                                    ],
                                ];

                                if ($result['data']['user']['isLeader']) { // если во встрече авторизовался лидер, то передаем еще и хэш лидера встречи (чтобы был редирект на страницу для лидера)
                                    $result['data']['meet']['hashLeader'] = $meet->hash_leader;
                                }

                                Yii::$app->response->statusCode = 200;

                                return $this->asJson($result);
                            } else {
                                Yii::$app->response->statusCode = 401;

                                return $this->asJson([
                                    'error' => [
                                        'code' => 401,
                                        'message' => 'Unauthorized',
                                    ],
                                ]);
                            }
                        } else { // если пользователь во встрече не регистрировался (дальнейший вход для изменений запрещен)
                            Yii::$app->response->statusCode = 403;

                            return $this->asJson([
                                'error' => [
                                    'code' => 403,
                                    'message' => 'Недоступно для вас',
                                ],
                            ]);
                        }
                    } else {
                        $user->role_id = Role::getRoleIDByTitle('participant'); // если пользователя с таким логином нет во встрече, то создаем нового

                        if ($user->password) {
                            $user->setPasswordHash();
                        }

                        $user->save(false);
                        TimeMeet::setClearIntervalForUser($user->id, $meet->id, $meet->start, $meet->end, count(DateMeet::getDayMeet($meet->id))); // создаем "пустые" интервалы для нового пользователя
                        
                        Yii::$app->response->statusCode = 200;

                        return $this->asJson([
                            'data' => [
                                'user' => [
                                    'login' => $user->login,
                                    'id' => $user->id,
                                    'isLeader' => $user->getRole()->one()->title == 'leader' ? true : false,
                                ],
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
            } else {
                Yii::$app->response->statusCode = 409;

                return $this->asJson([
                    'error' => [
                        'code' => 409,
                        'message' => 'Встреча заблокирована',
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

    public function actionInterval($hash, $userID)
    {
        if ($meet = Meet::findOne(['hash' => $hash])) {
            if (!$meet->block) {
                if ($user = User::findOne($userID)) {
                    $availables = Yii::$app->request->post();

                    $record = TimeMeet::findOne(['meet_id' => $meet->id, 'user_id' => $user->id]); // изменяем существующие интервалы
                    $record->available = json_encode($availables);                                 // изменяем существующие интервалы
                    $record->save(false);                                                          // изменяем существующие интервалы

                    Yii::$app->response->statusCode = 204;
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
                Yii::$app->response->statusCode = 409;

                return $this->asJson([
                    'error' => [
                        'code' => 409,
                        'message' => 'Встреча заблокирована',
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

    public function actionBlockMeet($hash, $hashLeader)
    {
        if ($meet = Meet::findOne(['hash' => $hash])) {
            if ($meet->hash_leader == $hashLeader) {
                if (!$meet->block) {
                    $meet->block = 1;
                    $meet->save(false);
                    Yii::$app->response->statusCode = 204;
                } else {
                    Yii::$app->response->statusCode = 409;

                    return $this->asJson([
                        'error' => [
                            'code' => 409,
                            'message' => 'Встреча уже заблокирована'
                        ]
                    ]);
                }
            } else {
                Yii::$app->response->statusCode = 403;

                return $this->asJson([
                    'error' => [
                        'code' => 403,
                        'message' => 'Недоступно для вас',
                    ]
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

    public function actionInfo($hash)
    {
        if ($meet = Meet::findOne(['hash' => $hash])) {
            if (!$meet->delete) {
                Yii::$app->response->statusCode = 200;

                return $this->asJson([
                    'data' => [
                        'meet' => Meet::getInfo($meet->id),
                    ],
                ]);
            } else {
                Yii::$app->response->statusCode = 403;

                return $this->asJson([
                    'error' => [
                        'code' => 403,
                        'message' => 'Доступ запрещен. Встреча удалена',
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

    public function actionInvite($hash, $hashLeader)
    {
        if ($meet = Meet::findOne(['hash' => $hash])) {
            if ($meet->hash_leader == $hashLeader) {
                if (!$meet->block) {
                    $meet->emails = Yii::$app->request->post();
                    $meet->scenario = Meet::SCENARIO_INVITE;

                    if ($meet->validate()) {
                        Yii::$app->response->statusCode = 204;
                    } else {
                        Yii::$app->response->statusCode = 422;

                        return $this->asJson([
                            'error' => [
                                'code' => 422,
                                'message' => 'Validation error',
                                'errors' => $meet->errors,
                            ],
                        ]);
                    }
                } else {
                    Yii::$app->response->statusCode = 409;

                    return $this->asJson([
                        'error' => [
                            'code' => 409,
                            'message' => 'Встреча заблокирована',
                        ],
                    ]);
                }
            } else {
                Yii::$app->response->statusCode = 403;

                return $this->asJson([
                    'error' => [
                        'code' => 403,
                        'message' => 'Недоступно для вас',
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

    public function actionUpdateFile($hash, $hashLeader)
    {
        if ($meet = Meet::findOne(['hash' => $hash])) {
            if ($meet->hash_leader == $hashLeader) {
                $meet->scenario = Meet::SCENARIO_UPLOAD_FILES;
                $meet->upload_img = UploadedFile::getInstanceByName('upload_img');
                $meet->upload_files = UploadedFile::getInstancesByName('upload_files');

                if ($meet->validate()) {
                    $meet->uploadFile();
                    $meet->save(false);

                    Yii::$app->response->statusCode = 204;
                } else {
                    Yii::$app->response->statusCode = 422;

                    return $this->asJson([
                        'error' => [
                            'code' => 422,
                            'message' => 'Validation error',
                            'errors' => $meet->errors,
                        ],
                    ]);
                }
            } else {
                Yii::$app->response->statusCode = 403;

                return $this->asJson([
                    'error' => [
                        'code' => 403,
                        'message' => 'Недоступно для вас',
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

    public function actionDeleteMeet($hash, $hashLeader)
    {
        if ($meet = Meet::findOne(['hash' => $hash])) {
            if ($meet->hash_leader == $hashLeader) {
                if (!$meet->delete) {
                    $meet->delete = 1;
                    $meet->save(false);

                    Yii::$app->response->statusCode = 204;
                } else {
                    Yii::$app->response->statusCode = 409;

                    return $this->asJson([
                        'error' => [
                            'code' => 409,
                            'message' => 'Недоступно для вас',
                        ],
                    ]);
                }
            } else {
                Yii::$app->response->statusCode = 403;

                return $this->asJson([
                    'error' => [
                        'code' => 403,
                        'message' => 'Недоступно для вас',
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

    public function actionDeleteFile($hash, $hashLeader, $filename)
    {
        if ($meet = Meet::findOne(['hash' => $hash])) {
            if ($meet->hash_leader == $hashLeader) {
                $meet->deleteFile($filename);

                Yii::$app->response->statusCode = 204;
            } else {
                Yii::$app->response->statusCode = 403;

                return $this->asJson([
                    'error' => [
                        'code' => 403,
                        'message' => 'Недоступно для вас',
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

    public function actionDeleteUser($hash, $hashLeader, $userID)
    {
        if ($meet = Meet::findOne(['hash' => $hash])) {
            if ($meet->hash_leader == $hashLeader) {
                if ($record = TimeMeet::findOne(['meet_id' => $meet->id, 'user_id' => $userID])) {
                    $record->delete();

                    Yii::$app->response->statusCode = 204;
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
                Yii::$app->response->statusCode = 403;

                return $this->asJson([
                    'error' => [
                        'code' => 403,
                        'message' => 'Недоступно для вас',
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

    public function actionCheckAccess($hash, $hashLeader)
    {
        if ($meet = Meet::findOne(['hash' => $hash])) {
            if ($meet->hash_leader == $hashLeader) {
                Yii::$app->response->statusCode = 204;
            } else {
                Yii::$app->response->statusCode = 403;

                return $this->asJson([
                    'error' => [
                        'code' => 403,
                        'message' => 'Недоступно для вас',
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