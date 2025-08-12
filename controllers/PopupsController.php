<?php

namespace app\controllers;

use app\models\Pengguna;
use app\models\Popups;
use app\models\PopupsSearch;
use Yii;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

class PopupsController extends BaseController
{
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
                'access' => [
                    'class' => \yii\filters\AccessControl::className(),
                    'rules' => [
                        [
                            'actions' => ['error'],
                            'allow' => true,
                        ],
                        [
                            'actions' => ['create', 'update', 'delete'],
                            'allow' => true,
                            'matchCallback' => function ($rule, $action) {
                                return !\Yii::$app->user->isGuest && \Yii::$app->user->identity->level === 0;
                            },
                        ],
                        [
                            'actions' => ['index'], // add all actions to take guest to login page
                            'allow' => true,
                            'roles' => ['@'],
                        ],
                    ],
                ],
            ]
        );
    }
    public function actionIndex()
    {
        $searchModel = new PopupsSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        } else {
            return $this->render('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
    }
    public function actionCreate()
    {
        $model = new Popups();

        if ($this->request->isPost && $model->load($this->request->post())) {
            if ($model->save()) {
                // Query the pengguna table for the list of names that correspond to the extracted usernames
                $penggunas = Pengguna::find()
                    ->select(['nama', 'nomor_hp'])
                    ->where([
                        'or',
                        ['level' => '1'],
                        ['level' => '0']
                    ])
                    ->all();
                $judul = $model->judul_popups;

                // Convert the list of names to a string in the format that can be used for autofill
                foreach ($penggunas as $name) {
                    $nomor_tujuan = $name->nomor_hp;
                    $nama_peserta = $name->nama;
                    $isi_notif = '*Portal Pintar - WhatsApp Notification Blast*

Bapak/Ibu ' . $nama_peserta . ', Terdapat Pengumuman Terbaru yang dikirimkan oleh Sistem Portal Pintar, tentang *' . $judul . '*.
Silahkan melihat berkas pengumuman di ' . Yii::$app->params['webhostingSatker'] . 'portalpintar/popups/index. Terima kasih.
        
_#pesan ini dikirim oleh Portal Pintar dan tidak perlu dibalas_';
                    $response = AgendaController::wa_engine($nomor_tujuan, $isi_notif);
                }
                Yii::$app->session->setFlash('success', "Popup berhasil ditambahkan.Terima kasih.");
                return $this->redirect(['index']);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if ($this->request->isPost && $model->load($this->request->post())) {
            date_default_timezone_set('Asia/Jakarta');
            $model->timestamp_lastupdate = date('Y-m-d H:i:s');
            if ($model->save()) {
                Yii::$app->session->setFlash('success', "Data popup berhasil dimutakhirkan. Terima kasih.");
                return $this->redirect(['index']);
            }
        }
        return $this->render('update', [
            'model' => $model,
        ]);
    }
    public function actionDelete($id)
    {
        date_default_timezone_set('Asia/Jakarta');
        $affected_rows = Popups::updateAll(['deleted' => 1, 'timestamp_lastupdate' => date('Y-m-d H:i:s')], 'id_popups = "' . $id . '"');
        if ($affected_rows == 0) {
            Yii::$app->session->setFlash('warning', "Gagal. Mohon hubungi Admin.");
            return $this->redirect(['index']);
        } else {
            Yii::$app->session->setFlash('success', "Data popups berhasil dihapus. Terima kasih.");
            return $this->redirect(['index']);
        }
    }
    protected function findModel($id_popups)
    {
        if (($model = Popups::findOne(['id_popups' => $id_popups])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
