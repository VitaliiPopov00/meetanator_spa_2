<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "meet".
 *
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property string $start
 * @property string $end
 * @property int $interval
 * @property int $block
 * @property int $delete
 * @property string $hash
 * @property string $hash_leader
 * @property int $user_id
 */
class Meet extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'meet';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'hash', 'hash_leader', 'user_id'], 'required'],
            [['start', 'end'], 'safe'],
            [['interval', 'block', 'delete', 'user_id'], 'integer'],
            [['title', 'description', 'hash', 'hash_leader'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'description' => 'Description',
            'start' => 'Start',
            'end' => 'End',
            'interval' => 'Interval',
            'block' => 'Block',
            'delete' => 'Delete',
            'hash' => 'Hash',
            'hash_leader' => 'Hash Leader',
            'user_id' => 'User ID',
        ];
    }
}
