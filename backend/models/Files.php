<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "files".
 *
 * @property int $id
 * @property string $filename
 * @property string $filetype
 * @property string $filesize
 * @property string $filepath
 * @property string $crdate
 * @property string $lmdate
 */
class Files extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'files';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['filename', 'filetype', 'filesize', 'filepath', 'crdate'], 'required'],
            [['filepath'], 'string'],
            [['crdate', 'lmdate'], 'safe'],
            [['filename', 'filetype', 'filesize'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'filename' => 'Filename',
            'filetype' => 'Filetype',
            'filesize' => 'Filesize',
            'filepath' => 'Filepath',
            'crdate' => 'Crdate',
            'lmdate' => 'Lmdate',
        ];
    }
}
