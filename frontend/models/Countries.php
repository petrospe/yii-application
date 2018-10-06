<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "countries".
 *
 * @property int $id
 * @property string $iso2
 * @property string $iso3
 * @property int $phonecode
 * @property string $currency_name
 * @property string $currency_iso
 * @property string $capital
 * @property string $name_native
 * @property string $name_en
 * @property string $name_el
 *
 * @property Address[] $addresses
 */
class Countries extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'countries';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'iso2', 'iso3', 'phonecode', 'currency_name', 'currency_iso', 'capital', 'name_native', 'name_en', 'name_el'], 'required'],
            [['id', 'phonecode'], 'integer'],
            [['iso2'], 'string', 'max' => 2],
            [['iso3', 'currency_iso'], 'string', 'max' => 3],
            [['currency_name', 'capital', 'name_native', 'name_en', 'name_el'], 'string', 'max' => 255],
            [['id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'iso2' => 'Iso2',
            'iso3' => 'Iso3',
            'phonecode' => 'Phonecode',
            'currency_name' => 'Currency Name',
            'currency_iso' => 'Currency Iso',
            'capital' => 'Capital',
            'name_native' => 'Name Native',
            'name_en' => 'Name En',
            'name_el' => 'Name El',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAddresses()
    {
        return $this->hasMany(Address::className(), ['country_id' => 'id']);
    }
}
