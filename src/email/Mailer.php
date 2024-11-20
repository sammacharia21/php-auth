<?php

namespace Demo\Email;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer
{
    private $mailer;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
        $this->configure();
    }

    private function configure()
    {
        if (!file_exists(__DIR__ . '/../../config/email.php')) {
            $config = [
                'smtp_host' => 'smtpgmail.com',
                'smtp_port' => 587,
                'smtp_secure' => 'tls',
                'smtp_username' => 'your-email@gmail.com',
                'smtp_password' => 'your-app-password',
            ];
            file_put_contents(
                __DIR__ . '/../../config/email.php',
                '<?php return ' . var_export($config, true) . '; '
            );
        }

        $config = require __DIR__  . '/../../config/email.php';

        $this->mailer->isSMTP();
        $this->mailer->Host = $config['smtp_host'];
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $config['smtp_username'];
        $this->mailer->Password = $config['smtp_password'];
        $this->mailer->SMTPSecure = $config['smtp_secure'];
        $this->mailer->Port = $config['smtp_port'];
    }

    public function sendMail($to, $subject, $body)
    {
        try {
            $this->mailer->setFrom($this->mailer->Username);
            $this->mailer->addAddress($to);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;
            return $this->mailer->send();  
        } catch (Exception $e) {
            throw new \Exception("Email could not be sent. Mailer Error: {$this->mailer->ErrorInfo}");
        }
    }
}