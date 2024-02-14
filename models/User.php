<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property string $login
 * @property string|null $password
 * @property string|null $token
 * @property int $role_id
 * @property string $created_at
 * @property string $updated_at
 */
class User extends ActiveRecord implements IdentityInterface
{
    const SCENARIO_CREATE_MEET = 'createMeet';
    const SCENARIO_LOGIN = 'login';
    const SCENARIO_LOGIN_MEET = 'loginMeet';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['login'], 'required'],

            [['password'], 'match', 'pattern' => '/[a-zA-Z\d]{6,}/', 'when' => function ($model) {
                return !empty($model->password);
            }, 'on' => static::SCENARIO_CREATE_MEET],
            [['login'], 'email', 'on' => static::SCENARIO_CREATE_MEET],
            [['login'], 'validateLogin', 'when' => function ($model) {
                return !empty($model->password);
            }, 'on' => static::SCENARIO_CREATE_MEET],

            [['password'], 'required', 'on' => static::SCENARIO_LOGIN],

            [['role_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['login', 'password', 'token'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'login' => 'Login',
            'password' => 'Password',
            'token' => 'Token',
            'role_id' => 'Role ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['token' => $token]);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAuthKey()
    {
        return $this->token;
    }

    public function validateAuthKey($token)
    {
        return $this->token === $token;
    }
}
