<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "date_meet".
 *
 * @property int $id
 * @property int $meet_id
 * @property string $date
 *
 * @property Meet $meet
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
            'meet_id' => 'Идентификатор встречи',
            'date' => 'Дата',
        ];
    }

    public static function setDateMeet($meetID, $dates)
    {
        foreach ($dates as $date) {
            $record = new static();
            $record->meet_id = $meetID;
            $record->date = $date;
            $record->save(false);
        }
    }

    public static function getDayMeet($meetID)
    {
        $result = [];
        $dates = static::findAll(['meet_id' => $meetID]);

        foreach ($dates as $date) {
            $result[] = $date->date;
        }

        return $result;
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
}
