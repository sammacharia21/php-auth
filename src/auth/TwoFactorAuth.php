<?php

namespace Demo\Auth;

use RobThree\Auth\TwoFactorAuth as GoogleAuth;

class TwoFactorAuth
{
    private $tfa;
    private $secret;

    public function __construct()
    {
        $this->tfa = new GoogleAuth('Demo App');
        $this->secret = $this->loadOrGenerateSecret();
    }

    private function loadOrGenerateSecret()
    {
        // Load secret from database or generate a new one
        if (!file_exists(__DIR__ . '/../../config/auth.php')) {
            $secret = $this->tfa->createSecret();
            file_put_contents(__DIR__ . '/../../config/auth.php', "<?php\nreturn ['2fa_secret' => '$secret'];");
            return $secret;
        }
        $config = require __DIR__ . '/../../config/auth.php';
        return $config['2fa_secret'];
    }

    public function getQRCodeUrl($username)
    {
        return $this->tfa->getQRCodeImageAsDataUri($username, $this->secret);
    }

    public function verifyCode($code)
    {
        return $this->tfa->verifyCode($this->secret, $code);
    }

}