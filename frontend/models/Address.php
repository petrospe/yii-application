<?php

namespace frontend\models;

use Yii;
use yii\db\ActiveRecord;
use common\models\User;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\db\Expression;

/**
 * This is the model class for table "address".
 *
 * @property int $id
 * @property int $user_id
 * @property string $receipient_name
 * @property string $telephone
 * @property string $address
 * @property string $post_code
 * @property string $city
 * @property int $country_id
 * @property string $trade
 * @property string $tax
 * @property string $inland_revenue
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Countries $country
 * @property User $user
 */
class Address extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'address';
    }
    
    /**
    * behaviors
    */

    public function behaviors()
    {
        return [
            'timestamp' => [
            'class' => 'yii\behaviors\TimestampBehavior',
            'attributes' => [
                                ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                                ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                            ],
            'value' => new Expression('NOW()'),
                           ],
               ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'receipient_name', 'telephone', 'address', 'post_code', 'city', 'country_id'], 'required'],
            [['user_id', 'country_id'], 'integer'],
            [['receipient_name'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['telephone'], 'string', 'max' => 45],
            [['address', 'city', 'trade', 'inland_revenue'], 'string', 'max' => 255],
            [['post_code', 'tax'], 'string', 'max' => 20],
            [['country_id'], 'exist', 'skipOnError' => true, 'targetClass' => Countries::className(), 'targetAttribute' => ['country_id' => 'id']],
            [['country_id'],'in', 'range'=>array_keys($this->getCountryList())],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'receipient_name' => 'Receipient Name',
            'telephone' => 'Telephone',
            'address' => 'Address',
            'post_code' => 'Post Code',
            'city' => 'City',
            'country_id' => 'Country ID',
            'trade' => 'Trade',
            'tax' => 'Tax',
            'inland_revenue' => 'Inland Revenue',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCountry()
    {
        return $this->hasOne(Countries::className(), ['id' => 'country_id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    
    public function getCountryName() 
    {
       return $this->countries->name_en;
    }
    
    /**
     * get list of genders for dropdown
     */

    public static function getCountryList()
    {
            
        $droptions = Countries::find()->asArray()->all();
        return ArrayHelper::map($droptions, 'id', 'name_en');
        
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @get Username
     */
         
    public function getUsername() 
    {
        return $this->user->username;
    }
 
    /**
     * @getUserId
     */
       
    public function getUserId() 
    {
        return $this->user ? $this->user->id : 'none';
    }

    /**
     * @getUserLink
     */

    public function getUserLink() 
    {
         $url = Url::to(['user/view', 'id'=>$this->UserId]); 
         $options = []; 
         return Html::a($this->getUserName(), $url, $options); 
    }
    
    /**
     * @getAddressLink
     */

    public function getAddressIdLink() 
    {
        $url = Url::to(['address/update', 'id'=>$this->id]);
        $options = [];
        return Html::a($this->id, $url, $options); 
    }
}
