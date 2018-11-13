<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "languages".
 *
 * @property string $code
 * @property string $name_english
 * @property string $name_native
 *
 * @property Entities[] $entities
 */
class Languages extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'languages';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['code', 'name_english', 'name_native'], 'required'],
            [['code'], 'string', 'max' => 2],
            [['name_english', 'name_native'], 'string', 'max' => 255],
            [['code'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'code' => 'Code',
            'name_english' => 'Name English',
            'name_native' => 'Name Native',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEntities()
    {
        return $this->hasMany(Entities::className(), ['language_code' => 'code']);
    }
}
