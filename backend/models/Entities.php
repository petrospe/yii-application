<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "entities".
 *
 * @property int $id
 * @property string $created_at
 * @property string $updated_at
 * @property int $attribute_id
 * @property int $parent_id
 * @property string $language_code
 * @property int $display_order
 * @property string $row_value
 * @property int $status
 *
 * @property Attributes $attribute0
 * @property Languages $languageCode
 * @property Status $status0
 */
class Entities extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'entities';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'safe'],
            [['attribute_id', 'language_code', 'status'], 'required'],
            [['attribute_id', 'parent_id', 'display_order', 'status'], 'integer'],
            [['row_value'], 'string'],
            [['language_code'], 'string', 'max' => 2],
            [['attribute_id'], 'exist', 'skipOnError' => true, 'targetClass' => Attributes::className(), 'targetAttribute' => ['attribute_id' => 'id']],
            [['language_code'], 'exist', 'skipOnError' => true, 'targetClass' => Languages::className(), 'targetAttribute' => ['language_code' => 'code']],
            [['status'], 'exist', 'skipOnError' => true, 'targetClass' => Status::className(), 'targetAttribute' => ['status' => 'status_value']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'attribute_id' => Yii::t('app', 'Attribute ID'),
            'parent_id' => Yii::t('app', 'Parent ID'),
            'language_code' => Yii::t('app', 'Language Code'),
            'display_order' => Yii::t('app', 'Display Order'),
            'row_value' => Yii::t('app', 'Row Value'),
            'status' => Yii::t('app', 'Status'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAttribute0()
    {
        return $this->hasOne(Attributes::className(), ['id' => 'attribute_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLanguageCode()
    {
        return $this->hasOne(Languages::className(), ['code' => 'language_code']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStatus0()
    {
        return $this->hasOne(Status::className(), ['status_value' => 'status']);
    }
}
