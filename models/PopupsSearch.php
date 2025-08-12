<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Popups;

class PopupsSearch extends Popups
{
    public function rules()
    {
        return [
            [['id_popups', 'deleted'], 'integer'],
            [['judul_popups', 'rincian_popups', 'timestamp', 'timestamp_lastupdate'], 'safe'],
        ];
    }

    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = Popups::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['timestamp' => SORT_DESC, 'id_popups' => SORT_DESC]]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id_popups' => $this->id_popups,
            'deleted' => $this->deleted,
            'timestamp' => $this->timestamp,
            'timestamp_lastupdate' => $this->timestamp_lastupdate,
        ]);

        $query->andFilterWhere(['like', 'judul_popups', $this->judul_popups])
            ->andFilterWhere(['like', 'rincian_popups', $this->rincian_popups]);

        return $dataProvider;
    }
}
