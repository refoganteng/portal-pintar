<?php
namespace app\models;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Beritarilis;

class BeritarilisSearch extends Beritarilis
{
    public function rules()
    {
        return [
            [['id_beritarilis'], 'integer'],
            [['tanggal_rilis', 'waktu_rilis', 'waktu_rilis_selesai', 'materi_rilis', 'narasumber', 'lokasi', 'reporter', 'timestamp', 'timestamp_lastupdate'], 'safe'],
        ];
    }
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }
    public function search($params)
    {
        $query = Beritarilis::find();
        // add conditions that should always apply here
        $query->where(['deleted' => 0]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['waktuselesai' => SORT_DESC]]
        ]);
        $this->load($params);
        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        // grid filtering conditions
        $query->andFilterWhere([
            'id_beritarilis' => $this->id_beritarilis,
            'timestamp' => $this->timestamp,
            'timestamp_lastupdate' => $this->timestamp_lastupdate,
        ]);
        $query->andFilterWhere(['like', 'materi_rilis', $this->materi_rilis])
            ->andFilterWhere(['like', 'narasumber', $this->narasumber])
            ->andFilterWhere(['like', 'lokasi', $this->lokasi])
            ->andFilterWhere(['like', 'reporter', $this->reporter]);
        return $dataProvider;
    }
}
