<?php


namespace app\sso;

use JKD\SSO\Client\Provider\Keycloak;

class SSOBPS
{
    public string $clientId;
    public string $clientSecret;
    private string $redirectUri;

    public function __construct()
    {
        if (!isset($_SESSION)) {
            session_start();
        }
    }

    public function setCredential($clientId, $clientSecret)
    {
        $this->clientId     = $clientId;
        $this->clientSecret = $clientSecret;
    }

    public function setRedirectUri($uri)
    {
        $this->redirectUri = $uri;
    }

    public function getLogin()
    {
        $provider = new Keycloak([
            'authServerUrl' => 'https://sso.bps.go.id',
            'realm'         => 'pegawai-bps',
            'clientId'      => $this->clientId,
            'clientSecret'  => $this->clientSecret,
            'redirectUri'   => $this->redirectUri
        ]);

        // Kalau belum ada 'code', arahkan ke SSO
        if (!isset($_GET['code'])) {
            $authUrl = $provider->getAuthorizationUrl();
            $_SESSION['oauth2state'] = $provider->getState();
            \Yii::$app->response->redirect($authUrl)->send();
            \Yii::$app->end();
        }

        // Validasi state
        if (empty($_GET['state']) || ($_GET['state'] !== ($_SESSION['oauth2state'] ?? null))) {
            unset($_SESSION['oauth2state']);
            $authUrl = $provider->getAuthorizationUrl();
            $_SESSION['oauth2state'] = $provider->getState();
            \Yii::$app->response->redirect($authUrl)->send();
            \Yii::$app->end();
        }

        // Ambil token
        try {
            $token = $provider->getAccessToken('authorization_code', [
                'code' => $_GET['code']
            ]);
        } catch (\Exception $e) {
            throw new \Exception("Gagal mendapatkan token: " . $e->getMessage());
        }

        // Ambil data user
        try {
            $userInfo = $provider->getResourceOwner($token)->toArray();
        } catch (\Exception $e) {
            throw new \Exception("Gagal mendapatkan data user: " . $e->getMessage());
        }

        // Sesuaikan format supaya sama seperti versi lama
        return [
            'token' => $token->getToken(),
            'user' => [
                'nama'            => $userInfo['name'] ?? null,
                'email'           => $userInfo['email'] ?? null,
                'username'        => $userInfo['preferred_username'] ?? $userInfo['username'] ?? null,
                'nip'             => $userInfo['nip-lama'] ?? null,
                'nip_baru'        => $userInfo['nip'] ?? null,
                'kode_organisasi' => $userInfo['organisasi'] ?? null,
                'kode_provinsi'   => isset($userInfo['organisasi']) ? substr($userInfo['organisasi'], 0, 2) : null,
                'kode_kabupaten'  => isset($userInfo['organisasi']) ? substr($userInfo['organisasi'], 2, 2) : null,
                'alamat_kantor'   => $userInfo['alamat-kantor'] ?? null,
                'provinsi'        => $userInfo['provinsi'] ?? null,
                'kabupaten'       => $userInfo['kabupaten'] ?? null,
                'golongan'        => $userInfo['golongan'] ?? null,
                'jabatan'         => $userInfo['jabatan'] ?? null,
                'foto'            => $userInfo['foto'] ?? null,
                'eselon'          => $userInfo['eselon'] ?? null,
            ],
            'logout_url' => 'https://sso.bps.go.id/auth/realms/pegawai-bps/protocol/openid-connect/logout?redirect_uri=' . $this->redirectUri
        ];
    }

    public function getLoginAsJSON()
    {
        header('Content-Type: application/json');
        echo json_encode($this->getLogin());
    }
}
