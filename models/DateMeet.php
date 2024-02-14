<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "date_meet".
 *
 * @property int $id
 * @property int $meet_id
 * @property string $date
 */
class DateMeet extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'date_meet';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['meet_id', 'date'], 'required'],
            [['meet_id'], 'integer'],
            [['date'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'meet_id' => 'Meet ID',
            'date' => 'Date',
        ];
    }
}
