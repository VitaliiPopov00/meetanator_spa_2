<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "file_meet".
 *
 * @property int $id
 * @property int $meet_id
 * @property string $filename
 * @property string $type
 *
 * @property Meet $meet
 */
class FileMeet extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'file_meet';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['meet_id', 'filename', 'type'], 'required'],
            [['meet_id'], 'integer'],
            [['filename', 'type'], 'string', 'max' => 255],
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
            'filename' => 'Название файла',
            'type' => 'Тип',
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
}
