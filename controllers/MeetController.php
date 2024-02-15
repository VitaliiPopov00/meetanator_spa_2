<?php

namespace app\controllers;

use yii\filters\auth\HttpBearerAuth;
use yii\rest\ActiveController;
use yii\web\UploadedFile;


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
            if ($user->password) {
                $user->role_id = Role::getRoleIDByTitle('leader');
                $user->setPasswordHash();
                $user->save(false);
            } else {
                $users = User::findAll(['login' => $user->login, 'role_id' => Role::getRoleIDByTitle('leader')]);
                $user = null;

                foreach ($users as $userFromDB) {
                    if ($userFromDB->password) {
                        $user = $userFromDB;
                        break;
                    }
                }

                if (!$user) {
                    $user = new User();
                    $user->role_id = Role::getRoleIDByTitle('leader');
                    $user->load(Yii::$app->request->post(), '');
                    $user->save(false);
                }
            }

            $meet->user_id = $user->id;
            $meet->setHashForMeet();
            $meet->setHashForLeaderMeet();
    
            if ($meet->save()) {
                $dateMeet = new DateMeet();
                $dateMeet->setDateMeet($meet->id, $meet->dates);
                TimeMeet::setClearIntervalForUser($user->id, $meet->id, $meet->start, $meet->end, count($meet->dates));
    
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
                ])
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
                    if (($userInMeet = $meet->userInMeet($user->login))) {
                        if ($userInMeet->password) {
                            if ($userInMeet->validatePassword($user->password)) {
                                $result = [
                                    'data' [
                                        'user' => [
                                            'login' => $userInMeet->login,
                                            'id' => $userInMeet->implode,
                                            'isLeader' => $userInMeet->getRole()->all()[0]->title == 'leader' ? true : false,
                                        ],
                                    ],
                                ];

                                if ($result['data']['user']['isLeader']) {
                                    $result['data']['meet']['hashLeader'] = $meet->hash_leader;
                                }

                                Yii::$app->response->statusCode = 200;

                                return $this->asJson($result);
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
                        $user->role_id = Role::getRoleIDByTitle('participant');

                        if ($user->password) {
                            $user->setPasswordHash();
                        }

                        $user->save(false);
                        TimeMeet::setClearIntervalForUser($user->id, $meet->id, $meet->start, $meet->end, count($meet->dates));
                        
                        Yii::$app->response->statusCode = 200;

                        return $this->asJson([
                            'data' => [
                                'user' => [
                                    'login' => $user->login,
                                    'id' => $user->id,
                                    'isLeader' => $user->getRole()->all()[0]->title == 'leader' ? true : false,
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

                    if (!($record = TimeMeet::findOne(['meet_id' => $meet->id, 'user_id' => $user->id]))) {
                        $record = new TimeMeet();
                        $record->user_id = $user->id;
                        $record->meet_id = $meet->id;
                    }

                    $record->availables = json_encode($availables);
                    $record->save(false);

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
                    ])
                }
            } else {
                Yii::$app->response->statusCode = 403;

                return $this->asJson([
                    'error' => [
                        'code' => 403,
                        'message' => 'Недоступно для вас',
                    ]
                ])
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
                        'meet' => $meet->getInfo(),
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