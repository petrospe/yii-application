<?php

namespace frontend\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Address;

/**
 * AddressSearch represents the model behind the search form of `frontend\models\Address`.
 */
class AddressSearch extends Address
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'country_id'], 'integer'],
            [['receipient_name', 'telephone', 'address', 'post_code', 'city', 'trade', 'tax', 'inland_revenue', 'created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Address::find();
        
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'user_id' => Yii::$app->user->identity->id,
            'country_id' => $this->country_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'receipient_name', $this->receipient_name])
            ->andFilterWhere(['like', 'telephone', $this->telephone])
            ->andFilterWhere(['like', 'address', $this->address])
            ->andFilterWhere(['like', 'post_code', $this->post_code])
            ->andFilterWhere(['like', 'city', $this->city])
            ->andFilterWhere(['like', 'trade', $this->trade])
            ->andFilterWhere(['like', 'tax', $this->tax])
            ->andFilterWhere(['like', 'inland_revenue', $this->inland_revenue]);

        return $dataProvider;
    }
}
