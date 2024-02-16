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
            'login' => 'Логин',
            'password' => 'Пароль',
            'token' => 'Токен',
            'role_id' => 'Идентификатор роли',
            'created_at' => 'Создан в',
            'updated_at' => 'Обновлен в',
        ];
    }

    public function validateLogin($attribute)
    {
        if ($this->password && !$this->hasErrors('password')) {
            $users = static::findAll([$attribute => $this->$attribute, 'role_id' => Role::getRoleIDByTitle('leader')]);
            
            foreach ($users as $user) {
                if ($user->password) {
                    return $this->addError($attribute, 'Логин должен быть уникальным');
                }
            }
        }
    }

    public function setPasswordHash()
    {
        return $this->password = Yii::$app->security->generatePasswordHash($this->password);
    }

    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password);
    }

    public function setToken()
    {
        return $this->token = Yii::$app->security->generateRandomString();
    }

    public static function getInfo($userID)
    {
        $user = User::findOne($userID);

        return [
            'login' => $user->login,
            'created_at' => $user->created_at,
            'meets' => Meet::getMeetUser($user->id),
        ];
    }

    public function getMeets()
    {
        return $this->hasMany(Meet::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[Role]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRole()
    {
        return $this->hasOne(Role::class, ['id' => 'role_id']);
    }

    /**
     * Gets query for [[TimeMeets]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTimeMeets()
    {
        return $this->hasMany(TimeMeet::class, ['user_id' => 'id']);
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
