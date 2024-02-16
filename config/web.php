<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'language' => 'ru-RU',
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'BtVGqeQUwS1HGmeT9P_hs6q67ITe0N--',
            'baseUrl' => '',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ]
        ],
        'response' => [
            // ...
            'formatters' => [
                \yii\web\Response::FORMAT_JSON => [
                    'class' => 'yii\web\JsonResponseFormatter',
                    'prettyPrint' => YII_DEBUG, // use "pretty" output in debug mode
                    'encodeOptions' => JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
                    // ...
                ],
            ],
            'class' => 'yii\web\Response',
            'on beforeSend' => function ($event) {
                $response = $event->sender;
                if ($response->statusCode == 401) {
                    $response->data = [
                        'error' => [
                            'code' => 401,
                            'message' => 'Unauthorized',
                        ]
                    ];
                }
            },
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            // send all mails to a file by default.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' => [
                'OPTIONS api/meet' => 'meet/options',
                'POST api/meet' => 'meet/new',

                'OPTIONS api/user' => 'user/options',
                'GET api/user' => 'user/info',

                [
                    'prefix' => 'api',
                    'pluralize' => false,
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'meet',
                    'extraPatterns' => [
                        'OPTIONS <hash>' => 'options',
                        'GET <hash>' => 'info',

                        'OPTIONS <hash>/<hashLeader>' => 'options',
                        'GET <hash>/<hashLeader>' => 'check-access',

                        'OPTIONS <hash>/login' => 'options',
                        'POST <hash>/login' => 'login',

                        'OPTIONS <hash>/user/<userID>' => 'options',
                        'PATCH <hash>/user/<userID>' => 'interval',

                        'OPTIONS <hash>/<hashLeader>' => 'options',
                        'DELETE <hash>/<hashLeader>' => 'block-meet',

                        'OPTIONS <hash>/<hashLeader>/delete' => 'options',
                        'DELETE <hash>/<hashLeader>/delete' => 'delete-meet',

                        'OPTIONS <hash>/<hashLeader>/file/<filename>' => 'options',
                        'DELETE <hash>/<hashLeader>/file/<filename>' => 'delete-file',

                        'OPTIONS <hash>/<hashLeader>/user/<userID>' => 'options',
                        'DELETE <hash>/<hashLeader>/user/<userID>' => 'delete-user',

                        'OPTIONS <hash>/<hashLeader>/file' => 'options',
                        'POST <hash>/<hashLeader>/file' => 'update-file',

                        'OPTIONS <hash>/<hashLeader>/invite' => 'options',
                        'POST <hash>/<hashLeader>/invite' => 'invite',
                    ],
                ],
                [
                    'prefix' => 'api',
                    'pluralize' => false,
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'user',
                    'extraPatterns' => [
                        'OPTIONS login' => 'options',
                        'POST login' => 'login',

                        'OPTIONS logout' => 'options',
                        'GET logout' => 'logout',

                        'OPTIONS profile' => 'options',
                        'GET profile' => 'info',
                    ],
                ],
                [
                    'prefix' => 'api',
                    'pluralize' => false,
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'img',
                    'extraPatterns' => [
                        'OPTIONS <hash>/<filename>' => 'options',
                        'GET <hash>/<filename>' => 'show',
                    ],
                ],
                [
                    'prefix' => 'api',
                    'pluralize' => false,
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'file',
                    'extraPatterns' => [
                        'OPTIONS <hash>/<filename>' => 'options',
                        'GET <hash>/<filename>' => 'show',
                    ],
                ],
            ],
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['*'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['*'],
    ];
}

return $config;
