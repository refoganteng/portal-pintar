<?php

namespace app\models;

use Yii;

class Agenda extends \yii\db\ActiveRecord
{

    public $waktu, $pilihpelaksana, $pelaksanatext, $tempattext, $pilihtempat, $teams, $approval;
    public static function tableName()
    {
        return 'agenda';
    }

    public function rules()
    {
        return [
            [['kegiatan', 'metode', 'progress', 'peserta', 'reporter', 'waktumulai', 'waktuselesai', 'pemimpin', 'fk_kategori', 'surat_lanjutan'], 'required'],
            [['pilihpelaksana', 'pilihtempat'], 'required', 'on' => ['create', 'update']],
            [['waktumulai_tunda', 'waktuselesai_tunda', 'peserta_lain', 'hashtags', 'by_event_team', 'event_team_leader'], 'safe'],
            [['kegiatan'], 'string'],
            [['waktumulai', 'waktuselesai', 'timestamp', 'timestamp_lastupdate'], 'safe'],
            [['metode', 'progress', 'id_lanjutan', 'pilihpelaksana'], 'integer'],
            [['pelaksana', 'tempat'], 'string', 'max' => 255],
            [['reporter'], 'string', 'max' => 50],
            [['presensi'], 'string', 'max' => 100],
            [['waktumulai', 'waktumulai_tunda', 'waktuselesai', 'waktuselesai_tunda'], 'validateSembilanBelasMei'],
            ['waktumulai', 'validateDates'],
            ['waktumulai_tunda', 'validateDatesTunda'],
            ['tempat', 'validateRooms'],
            ['surat_lanjutan', 'validateSuratOtomatis'],
            ['presensi', 'url', 'validSchemes' => ['http', 'https']],
        ];
    }
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_agenda' => 'Id Agenda',
            'kegiatan' => 'Kegiatan',
            'waktumulai' => 'Mulai',
            'waktuselesai' => 'Selesai',
            'pemimpin' => 'Pemimpin Rapat',
            'metode' => 'Jenis Agenda',
            'pelaksana' => 'Project (Tim)',
            'pelaksanatext' => 'Penyelenggara Eksternal',
            'tempat' => 'Ruang di Kantor',
            'tempattext' => 'Lokasi Luar Kantor',
            'progress' => 'Progress',
            'presensi' => 'Link Presensi',
            'peserta' => 'Peserta',
            'id_lanjutan' => 'Agenda Sebelumnya',
            'surat_lanjutan' => 'Apakah ingin lanjut buat surat internal?',
            'reporter' => 'Reporter',
            'timestamp' => 'Timestamp',
            'timestamp_lastupdate' => 'Timestamp Lastupdate',
            'pilihpelaksana' => 'Cakupan',
            'pilihtempat' => 'Lokasi',
            'peserta_lain' => 'Peserta Tambahan',
            'fk_kategori' => 'Kategori Agenda'
        ];
    }
    public function validateSembilanBelasMei()
    {
        if (Yii::$app->controller->action->id == 'update' || Yii::$app->controller->action->id == 'create') {
            if (strtotime($this->waktuselesai) < strtotime(date("2023-05-19"))) {
                $this->addError('waktuselesai', 'Portal Pintar hanya menerima data sejak 19 Mei 2023.');
            }
            if (strtotime($this->waktumulai) < strtotime(date("2023-05-19"))) {
                $this->addError('waktumulai', 'Portal Pintar hanya menerima data sejak 19 Mei 2023.');
            }
            if (Yii::$app->controller->action->id == 'tunda') {
                if (strtotime($this->waktumulai_tunda) < strtotime(date("2023-05-19"))) {
                    $this->addError('waktumulai_tunda', 'Portal Pintar hanya menerima data sejak 19 Mei 2023.');
                }
                if (strtotime($this->waktuselesai_tunda) < strtotime(date("2023-05-19"))) {
                    $this->addError('waktuselesai_tunda', 'Portal Pintar hanya menerima data sejak 19 Mei 2023.');
                }
            }
        }
    }
    public function validateDates()
    {
        if (Yii::$app->controller->action->id == 'update' || Yii::$app->controller->action->id == 'create') {
            if (strtotime($this->waktuselesai) <= strtotime($this->waktumulai)) {
                $this->addError('waktumulai', 'Tanggal/jam selesai tidak bisa sama/lebih awal dari tanggal mulai.');
                $this->addError('waktuselesai', 'Tanggal/jam selesai tidak bisa sama/lebih awal dari tanggal mulai.');
            }
        }
    }
    public function validateDatesTunda()
    {
        if (Yii::$app->controller->action->id == 'tunda') {
            if (strtotime($this->waktuselesai_tunda) <= strtotime($this->waktumulai_tunda)) {
                // if (7 < 1) {
                $this->addError('waktumulai_tunda', 'Tanggal/jam penundaan selesai tidak bisa sama/lebih awal dari tanggal mulai.');
                $this->addError('waktuselesai_tunda', 'Tanggal/jam penundaan selesai tidak bisa sama/lebih awal dari tanggal mulai.');
            }
        }
    }
    public function validateRooms()
    {
        $mulai = $this->waktumulai;
        $selesai = $this->waktuselesai;
        $ruangan = $this->tempat;
    
        $query = Agenda::find()
            ->where(['tempat' => $ruangan])
            ->andWhere(['progress' => '0'])
            ->andWhere(['deleted' => 0]);
    
        if (Yii::$app->controller->action->id = 'update') {
            $id = $this->id_agenda;
            $query->andWhere(['<>', 'id_agenda', $id]);
        } elseif (Yii::$app->controller->action->id = 'tunda') {
            $id = $this->id_agenda;
            $mulai = $this->waktumulai_tunda;
            $selesai = $this->waktuselesai_tunda;
            $query->andWhere(['<>', 'id_agenda', $id]);
        }
    
        // Improved Overlap Condition
        $query->andWhere([
            'or',
            ['and', ['>=', 'waktumulai', $mulai], ['<', 'waktumulai', $selesai]], // Starts inside
            ['and', ['>', 'waktuselesai', $mulai], ['<=', 'waktuselesai', $selesai]], // Ends inside
            ['and', ['<=', 'waktumulai', $mulai], ['>=', 'waktuselesai', $selesai]], // Fully contains
            ['and', ['>=', 'waktumulai', $mulai], ['<=', 'waktuselesai', $selesai]], // Fully contained
        ]);
    
        $agenda = $query->all();
    
        if (Yii::$app->controller->action->id != 'editpeserta' && count($agenda) > 0 && $ruangan != 13) {
            $this->addError('tempat', "Ruangan dan jadwal tersebut sudah digunakan untuk " . $agenda[0]['kegiatan']);
        } elseif (Yii::$app->controller->action->id != 'editpeserta' && count($agenda) > 1 && $ruangan == 13) {
            $this->addError('tempat', "Zoom dan jadwal tersebut sudah digunakan untuk " . $agenda[0]['kegiatan']);
        }
    }

    public function validateSuratOtomatis(){
        $surats = Suratrepo::find()
            ->select('*')
            ->where(['owner' => Yii::$app->user->identity->username])
            ->andWhere(['deleted' => 0])
            ->andWhere(
                ['>', 'DATEDIFF(NOW(), DATE(timestamp_suratrepo_lastupdate))', 3], // diinput dalam span 3 hari
            )
            ->asArray()
            ->all();
        // Get the current date and time
        $currentDate = new \DateTime();
        // Subtract 2 days from the current date
        $threeDaysAgo = $currentDate->modify('-2 days');
        // Loop through each $surats and check if the file exists
        $missingFiles = [];
        $missingNumbers = [];
        $missingTitles = [];
        foreach ($surats as $surat) {
            $filePath = Yii::getAlias('@webroot/surat/internal/pdf/' . $surat['id_suratrepo'] . '.pdf');
            if (!file_exists($filePath)) {
                // File does not exist, add the id_suratrepoeks to the missingFiles array
                $missingFiles[] = $surat['id_suratrepo'];
                $missingNumbers[] = $surat['nomor_suratrepo'];
                $missingTitles[] = $surat['perihal_suratrepo'];
            }
        }
        // Print the list of id_suratrepoeks without corresponding files
        if (!empty($missingFiles) && $this->surat_lanjutan == 1) {            
            $this->addError('pelaksana', "Untuk memanfaatkan fitur 'Generator Surat Undangan', mohon upload terlebih dahulu, scan surat-surat Anda sebelum " . $threeDaysAgo->format('d F Y') .".");
        }
    }

    public function getTempate()
    {
        $db = Rooms::findOne(['id_rooms' => $this->tempat]);
        if ($db !== null) {
            return $db->nama_ruangan;
        } else {
            return $this->tempat;
        }
    }
    public function getPelaksanae()
    {
        $db = Project::findOne(['id_project' => $this->pelaksana]);
        if ($db !== null) {
            return $db->panggilan_project;
        } else {
            return $this->pelaksana;
        }
    }
    public function getPelaksanalengkape()
    {
        $db = Project::findOne(['id_project' => $this->pelaksana]);
        if ($db !== null) {
            return 'Project ' . $db->nama_project;
        } else {
            return $this->pelaksana;
        }
    }
    public function getReportere()
    {
        return $this->hasOne(Pengguna::className(), ['username' => 'reporter']);
    }
    public function getKategorie()
    {
        return $this->hasOne(Kategori::className(), ['id_kategori' => 'fk_kategori']);
    }
    public function getPemimpine()
    {
        return $this->hasOne(Pengguna::className(), ['username' => 'pemimpin']);
    }
    public function getLaporane()
    {
        return $this->hasOne(Laporan::className(), ['id_laporan' => 'id_agenda']);
    }
    public function getUploadere()
    {
        return $this->hasOne(Pengguna::className(), ['username' => 'uploader'])->via('laporane');
    }
    public function getLanjutan()
    {
        // return $this->hasOne(self::className(), ['id_agenda' => 'id_lanjutan']);
        if ($this->id_lanjutan == null)
            return '-';
        else {
            $lanjut = Agenda::findOne(['id_agenda' => $this->id_lanjutan]);
            $formatter = Yii::$app->formatter;
            $formatter->locale = 'id-ID'; // set the locale to Indonesian
            $timezone = new \DateTimeZone('Asia/Jakarta'); // create a timezone object for WIB
            $waktumulai = new \DateTime($lanjut->waktumulai, new \DateTimeZone('UTC')); // create a datetime object for waktumulai with UTC timezone
            $waktumulai->setTimeZone($timezone); // set the timezone to WIB
            $waktumulaiFormatted = $formatter->asDatetime($waktumulai, 'd MMMM Y, H:mm'); // format the waktumulai datetime value
            $waktuselesai = new \DateTime($lanjut->waktuselesai, new \DateTimeZone('UTC')); // create a datetime object for waktuselesai with UTC timezone
            $waktuselesai->setTimeZone($timezone); // set the timezone to WIB
            $waktuselesaiFormatted = $formatter->asDatetime($waktuselesai, 'H:mm'); // format the waktuselesai time value only
            if ($waktumulai->format('Y-m-d') === $waktuselesai->format('Y-m-d')) {
                // if waktumulai and waktuselesai are on the same day, format the time range differently
                $waktumulaiFormatted = $formatter->asDatetime($waktumulai, 'd MMMM Y, H:mm'); // format the waktumulai datetime value with the year and time
                return $lanjut->kegiatan . ' pada ' . $waktumulaiFormatted . ' - ' . $waktuselesaiFormatted . ' WIB'; // concatenate the formatted dates
            } else {
                // if waktumulai and waktuselesai are on different days, format the date range normally
                $waktuselesaiFormatted = $formatter->asDatetime($waktuselesai, 'd MMMM Y, H:mm'); // format the waktuselesai datetime value
                return $lanjut->kegiatan . ' pada ' . $waktumulaiFormatted . ' WIB <br/>s.d ' . $waktuselesaiFormatted . ' WIB'; // concatenate the formatted dates
            }
            // return $lanjut->kegiatan . ' pada ' . Yii::$app->formatter->asDatetime(strtotime($lanjut->waktumulai), "d MMMM y 'pada' H:mm a");
        }
    }
    public function getSuratrepoe()
    {
        return $this->hasMany(Suratrepo::className(), ['fk_agenda' => 'id_agenda']);
    }
    public function getProjecte()
    {
        return $this->hasOne(Project::className(), ['id_project' => 'pelaksana']);
    }
    public function getZoomsnya()
    {
        return $this->hasMany(Zooms::className(), ['fk_agenda' => 'id_agenda']);
    }

    public function getZoomse()
    {
        $zooms = Zooms::find()
            ->joinWith('zoomstypee')
            ->where(['fk_agenda' => $this->id_agenda])
            ->one();
        if ($zooms != null)
            return '<span class="badge bg-success">' . $zooms->zoomstypee->nama_zoomstype . '</span>';
        else
            return '-';
    }
}
