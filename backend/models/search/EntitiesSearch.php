<?php

namespace backend\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Entities;

/**
 * EntitiesSearch represents the model behind the search form of `backend\models\Entities`.
 */
class EntitiesSearch extends Entities
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'attribute_id', 'parent_id', 'display_order', 'status'], 'integer'],
            [['created_at', 'updated_at', 'language_code', 'row_value'], 'safe'],
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
        $query = Entities::find();

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
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'attribute_id' => $this->attribute_id,
            'parent_id' => $this->parent_id,
            'display_order' => $this->display_order,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'language_code', $this->language_code])
            ->andFilterWhere(['like', 'row_value', $this->row_value]);

        return $dataProvider;
    }
}
