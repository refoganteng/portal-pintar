<?php

namespace app\models;

class Eoq2025 extends \yii\db\ActiveRecord
{
    public static function getDb()
    {
        return \Yii::$app->db_eoq;
    }

    public static function tableName()
    {
        return 'eoq_2025';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['total_pilihan_1', 'total_pilihan_2', 'total_pilihan_3'], 'default', 'value' => null],
            [['chosen'], 'default', 'value' => 0],
            [['tahun', 'triwulan', 'pegawai', 'ranking_sistem'], 'required'],
            [['tahun', 'triwulan', 'ranking_sistem', 'total_pilihan_1', 'total_pilihan_2', 'total_pilihan_3', 'chosen'], 'integer'],
            [['timestamp', 'timestamp_lastupdated'], 'safe'],
            [['pegawai'], 'string', 'max' => 30],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id_eoq_2025' => 'Id Eoq 2025',
            'tahun' => 'Tahun',
            'triwulan' => 'Triwulan',
            'pegawai' => 'Pegawai',
            'ranking_sistem' => 'Ranking SKP',
            'total_pilihan_1' => 'Total Pilihan 1',
            'total_pilihan_2' => 'Total Pilihan 2',
            'total_pilihan_3' => 'Total Pilihan 3',
            'chosen' => 'Chosen',
            'timestamp' => 'Timestamp',
            'timestamp_lastupdated' => 'Timestamp Lastupdated',
        ];
    }
    public function getYears()
    {
        $currentYear = date('Y');
        $yearFrom = 2025;
        $yearsRange = range($currentYear, $yearFrom);
        return array_combine($yearsRange, $yearsRange);
    }

    public function getPenggunae()
    {
        return $this->hasOne(Pengguna::className(), ['username' => 'pegawai']);
    }
}
