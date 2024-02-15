<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "time_meet".
 *
 * @property int $id
 * @property int $user_id
 * @property int $meet_id
 * @property string $available
 *
 * @property Meet $meet
 * @property User $user
 */
class TimeMeet extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'time_meet';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'meet_id', 'available'], 'required'],
            [['user_id', 'meet_id'], 'integer'],
            [['available'], 'string', 'max' => 255],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['meet_id'], 'exist', 'skipOnError' => true, 'targetClass' => Meet::class, 'targetAttribute' => ['meet_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'Идентификатор пользователя',
            'meet_id' => 'Идентификатор встречи',
            'available' => 'Доступность',
        ];
    }

    /**
     * Gets query for [[Meet]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMeet()
    {
        return $this->hasOne(Meet::class, ['id' => 'meet_id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
