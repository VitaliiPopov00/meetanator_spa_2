<?php

namespace app\models;

use DateTime;
use Yii;
use yii\validators\EmailValidator;


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
 *
 * @property DateMeet[] $dateMeets
 * @property FileMeet[] $fileMeets
 * @property TimeMeet[] $timeMeets
 * @property User $user
 */
class Meet extends \yii\db\ActiveRecord
{
    const SCENARIO_CREATE_MEET = 'createMeet';
    const SCENARIO_UPLOAD_FILES = 'uploadFiles';
    const SCENARIO_INVITE = 'invite';

    public $confirm;
    public $upload_img;
    public $upload_files;
    public $dates;
    public $emails;

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
            [['title', 'start', 'end'], 'required'],

            [['confirm'], 'required', 'requiredValue' => 1, 'message' => 'Необходимо согласиться', 'on' => static::SCENARIO_CREATE_MEET],
            [['dates'], 'required', 'on' => static::SCENARIO_CREATE_MEET],
            [['dates'], 'validateDate', 'on' => static::SCENARIO_CREATE_MEET],

            [['upload_img'], 'image', 'extensions' => 'png, jpg, jped', 'mimeTypes' => 'image/*', 'maxSize' => 2 * 1024 * 1024, 'maxFiles' => 1, 'on' => static::SCENARIO_UPLOAD_FILES],
            [['upload_files'], 'file', 'extensions' => 'pdf', 'mimeTypes' => 'application/*', 'maxSize' => 2 * 1024 * 1024, 'maxFiles' => 2, 'on' => static::SCENARIO_UPLOAD_FILES],

            [['emails'], 'validateEmails', 'on' => static::SCENARIO_INVITE],

            [['start', 'end'], 'safe'],
            [['interval', 'block', 'delete', 'user_id'], 'integer'],
            [['title', 'description', 'hash', 'hash_leader'], 'string', 'max' => 255],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Название',
            'description' => 'Описание',
            'start' => 'Начало',
            'end' => 'Конец',
            'interval' => 'Интервал',
            'block' => 'Заблокирован',
            'delete' => 'Удален',
            'hash' => 'Идентификатор встречи',
            'hash_leader' => 'Идентификатор лидера',
            'user_id' => 'Идентификатор пользователя',
        ];
    }

    public function validateEmails($attribute)
    {
        foreach ($this->$attribute as $index => $email) {
            $validator = new EmailValidator();
            $validator->message = 'Является не корректным E-mail адресом';

            if ($email) {
                if (!$validator->validate($email, $error)) {
                    $this->addError('email' . $index, $error);
                }
            } else {    
                $this->addError('email' . $index, 'Заполните поле');
            }
        }
    }

    public function validateDate($attribute)
    {
        foreach ($this->$attribute as $index => $date) {
            $date = (new DateTime($date))->format('Y-m-d');
            $now = (new DateTime())->format('Y-m-d');
            $dateLast30Days = new DateTime($now . ' + 30 days');

            if ($date && ($date < $now || $date > $dateLast30Days)) {
                $this->addError('date' . $index, 'Дата должна быть минимум сегодняшней и не более 30 дней в будущем');
            }
        }
    }

    public function uploadFile()
    {
        if ($this->upload_img) {
            $dir = Yii::getAlias('@app') . "/upload/{$this->hash}/image/";

            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }

            if (!($fileMeet = FileMeet::findOne(['meet_id' => $this->id, 'type' => 'img']))) {
                $fileMeet = new FileMeet();
                $fileMeet->meet_id = $this->id;
            }

            if ($fileMeet->filename && file_exists($dir . $fileMeet->filename)) {
                unlink($dir . $fileMeet->filename);
            }

            if ($this->upload_img->saveAs($dir . $this->upload_img->baseName . '.' . $this->upload_img->extension)) {
                $fileMeet->filename = $this->upload_img->baseName . '.' . $this->upload_img->extension;
                $fileMeet->type = 'img';
                $fileMeet->save(false);
            } else {
                return false;
            }
        }

        if ($this->upload_files) {
            $dir = Yii::getAlias('@app') . "/upload/{$this->hash}/info/";
            $oldFiles = FileMeet::findAll(['meet_id' => $this->id, 'type' => 'pdf']);

            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }

            foreach ($this->upload_files as $key => $file) {
                if (isset($oldFiles[$key]) && file_exists($dir . $oldFiles[$key]->filename)) {
                    unlink($dir . $oldFiles[$key]->filename);
                }

                if ($file->saveAs($dir . $file->baseName . '.' . $file->extension)) {
                    if (!isset($oldFiles[$key])) {
                        $fileMeet = new FileMeet();
                        $fileMeet->meet_id = $this->id;
                    } else {
                        $fileMeet = $oldFiles[$key];
                    }

                    $fileMeet->filename = $file->baseName . '.' . $file->extension;
                    $fileMeet->type = 'pdf';
                    $fileMeet->save(false);
                } else {
                    return false;
                }
            }
        }
    }

    public function deleteFile($filename)
    {
        if (file_exists(Yii::getAlias('@app') . "/upload/{$this->hash}/image/{$filename}")) {
            FileMeet::findOne(['meet_id' => $this->id, 'filename' => $filename])->delete();
            unlink(Yii::getAlias('@app') . "/upload/{$this->hash}/image/{$filename}");
        }

        if (file_exists(Yii::getAlias('@app') . "/upload/{$this->hash}/info/{$filename}")) {
            FileMeet::findOne(['meet_id' => $this->id, 'filename' => $filename])->delete();
            unlink(Yii::getAlias('@app') . "/upload/{$this->hash}/info/{$filename}");
        }
    }

    public function userInMeet($login)
    {
        if (($users = User::findAll(['login' => $login]))) {
            foreach ($users as $user) {
                if (TimeMeet::findOne(['user_id' => $user->id, 'meet_id' => $this->id])) {
                    return $user;
                }
            }

            return false;
        } else {
            return null;
        }
    }

    public static function getMeetUser($userID)
    {
        $result = [];
        $meets = static::findAll(['user_id' => $userID]);

        foreach ($meets as $meet) {
            $result[] = [
                'title' => $meet->title,
                'hash' => $meet->hash,
                'hashLeader' => $meet->hash_leader,
                'delete' => $meet->delete,
                'block' => $meet->block,
            ];
        }

        return $result;
    }

    public function setHashForMeet()
    {
        return $this->hash = Yii::$app->security->generateRandomString();
    }

    public function setHashForLeaderMeet()
    {
        return $this->hash_leader = Yii::$app->security->generateRandomString();
    }

    public static function getInfo($meetID)
    {
        $meet = static::findOne($meetID);
        $leader = User::findOne($meet->user_id);
        $info = [
            'title' => $meet->title,
            'description' => $meet->description,
            'dates' => DateMeet::getDayMeet($meet->id),
            'start' => $meet->start,
            'end' => $meet->end,
            'interval' => $meet->interval,
            'block' => $meet->block,
            'delete' => $meet->delete,
            'availables' => TimeMeet::getAllUserAvailable($meet->id),
            'leader' => [
                'id' => $leader->id,
                'login' => $leader->login,
            ],
            'img' => '',
            'files' => [],
        ];

        if ($img = FileMeet::findOne(['meet_id' => $meet->id, 'type' => 'img'])) {
            $info['img'] = $img->filename;
        }

        if ($filesPDF = FileMeet::findAll(['meet_id' => $meet->id, 'type' => 'pdf'])) {
            foreach ($filesPDF as $filePDF) {
                $info['files'][] = $filePDF->filename;
            }
        }

        return $info;
    }

    /**
     * Gets query for [[DateMeets]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDateMeets()
    {
        return $this->hasMany(DateMeet::class, ['meet_id' => 'id']);
    }

    /**
     * Gets query for [[FileMeets]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFileMeets()
    {
        return $this->hasMany(FileMeet::class, ['meet_id' => 'id']);
    }

    /**
     * Gets query for [[TimeMeets]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTimeMeets()
    {
        return $this->hasMany(TimeMeet::class, ['meet_id' => 'id']);
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
