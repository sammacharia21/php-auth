<?php
session_start();
require '../vendor/autoload.php';

use Demo\Auth\TwoFactorAuth;
use Demo\Email\Mailer;

$message = '';
$qrCode = '';
$tfa = new TwoFactorAuth();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['verify_2fa'])) {
        $code = $_POST['code'];
        if ($tfa->verifyCode($code)) {
            $message = "2FA verification successful!";
            $_SESSION['2fa_verified'] = true;
        } else {
            $message = "2FA verification failed!";
        }
    } elseif (isset($_POST['send_email']) && isset($_SESSION['2fa_verified'])) {
        $gmail = $_POST['gmail'];
        $app_password = $_POST['app_password'];
        
        // Update email configuration
        $email_config = [
            'smtp_host' => 'smtp.gmail.com',
            'smtp_port' => 587,
            'smtp_secure' => 'tls',
            'smtp_username' => $gmail,
            'smtp_password' => $app_password,
        ];
        file_put_contents(__DIR__ . '/../config/email.php', "<?php\nreturn " . var_export($email_config, true) . ";");

        try {
            $mailer = new Mailer();
            $mailer->sendMail(
                $gmail,
                'Test Email',
                'This is a test email from our PHP demo application.'
            );
            $message = "Email sent successfully!";
        } catch (Exception $e) {
            $message = "Email error: " . $e->getMessage();
        }
    }
}

// Generate QR code for new users
if (!isset($_SESSION['qr_code'])) {
    $_SESSION['qr_code'] = $tfa->getQRCodeUrl('testuser@example.com');
}
$qrCode = $_SESSION['qr_code'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Authentication Demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .qr-code-container {
            text-align: center;
            margin: 20px 0;
        }
        .qr-code-container img {
            max-width: 200px;
            margin: 10px auto;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">PHP Authentication Demo</h1>
        
        <?php if ($message): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3>Two-Factor Authentication</h3>
                    </div>
                    <div class="card-body">
                        <div class="qr-code-container">
                            <p>1. Scan this QR code with Google Authenticator:</p>
                            <img src="<?php echo htmlspecialchars($qrCode); ?>" alt="QR Code">
                        </div>
                        
                        <form method="POST" class="mt-3">
                            <div class="mb-3">
                                <label for="code" class="form-label">Enter the code from Google Authenticator:</label>
                                <input type="text" class="form-control" id="code" name="code" required>
                            </div>
                            <button type="submit" name="verify_2fa" class="btn btn-primary">Verify Code</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3>Email Configuration</h3>
                    </div>
                    <div class="card-body">
                        <?php if (!isset($_SESSION['2fa_verified'])): ?>
                            <div class="alert alert-warning">Please verify 2FA first</div>
                        <?php else: ?>
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="gmail" class="form-label">Gmail Address:</label>
                                    <input type="email" class="form-control" id="gmail" name="gmail" required>
                                </div>
                                <div class="mb-3">
                                    <label for="app_password" class="form-label">Gmail App Password:</label>
                                    <input type="password" class="form-control" id="app_password" name="app_password" required>
                                    <small class="form-text text-muted">Use an App Password from your Google Account settings</small>
                                </div>
                                <button type="submit" name="send_email" class="btn btn-primary">Send Test Email</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>