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
            'filename' => 'Filename',
            'type' => 'Type',
        ];
    }
}
