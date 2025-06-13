<?php

namespace d3yii2\d3paymentsystems\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * D3paymentsystemsFeeSearch represents the model behind the search form about `d3yii2\d3paymentsystems\models\D3paymentsystemsFee`.
 */
class D3paymentsystemsFeeSearch extends D3paymentsystemsFee
{
    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['id', 'wallet_sys_model_id'], 'integer'],
            [['from_country', 'from_type', 'to_country'], 'safe'],
            [['sender_fee', 'receiver_fee'], 'number'],
        ];
    }

    /**
     * @inheritdoc
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
        $query = D3paymentsystemsFee::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to any records when validation fails
            // $query->where('0=1');
            return new ActiveDataProvider([
                'query' => $query,
                //'sort' => ['defaultOrder' => ['????' => SORT_ASC]]            
            ]);
        }

        $query = self::find()
            ->select([
                'd3paymentsystems_fee.*'
            ])
            ->andFilterWhere([
                'd3paymentsystems_fee.id' => $this->id,
                'd3paymentsystems_fee.wallet_sys_model_id' => $this->wallet_sys_model_id,
                'd3paymentsystems_fee.sender_fee' => $this->sender_fee,
                'd3paymentsystems_fee.receiver_fee' => $this->receiver_fee,
            ])
            ->andFilterWhere(['like', 'd3paymentsystems_fee.from_country', $this->from_country])
            ->andFilterWhere(['like', 'd3paymentsystems_fee.from_type', $this->from_type])
            ->andFilterWhere(['like', 'd3paymentsystems_fee.to_country', $this->to_country]);
        return new ActiveDataProvider([
            'query' => $query,
            //'sort' => ['defaultOrder' => ['????' => SORT_ASC]]            
        ]);
    }
}