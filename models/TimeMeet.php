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
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'meet_id' => 'Meet ID',
            'available' => 'Available',
        ];
    }
}
