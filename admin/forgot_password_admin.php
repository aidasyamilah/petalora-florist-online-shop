<?php
require_once 'includes/admin_auth.php';
include 'config.php';

$message = '';
$messageType = '';

if (!isset($_SESSION['admin_password_reset_tokens'])) {
    $_SESSION['admin_password_reset_tokens'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'request_reset') {
    $admin_email = trim($_POST['admin_email'] ?? '');
    if ($admin_email === '') {
        $message = 'Please enter your admin email.';
        $messageType = 'error';
    } else {
        $adminLookup = $conn->prepare("SELECT admin_id FROM admin WHERE admin_email = ?");
        $adminLookup->bind_param("s", $admin_email);
        $adminLookup->execute();
        $adminLookup->store_result();

        if ($adminLookup->num_rows < 1) {
            $message = 'Admin email not found.';
            $messageType = 'error';
        } else {
            $resetToken = bin2hex(random_bytes(16));
            $_SESSION['admin_password_reset_tokens'][$resetToken] = [
                'admin_email' => $admin_email,
                'expires_at' => time() + (30 * 60)
            ];
            $resetLink = sprintf(
                '%s://%s%s/forgot_password_admin.php?token=%s',
                (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http',
                $_SERVER['HTTP_HOST'],
                rtrim(dirname($_SERVER['PHP_SELF']), '/\\'),
                urlencode($resetToken)
            );
            $mailSubject = 'Petalora Admin Password Reset';
            $mailBody = "Use this link to reset your admin password:\n\n{$resetLink}\n\nThis link expires in 30 minutes.";
            $mailHeaders = "From: no-reply@petalora.local\r\n";
            @mail($admin_email, $mailSubject, $mailBody, $mailHeaders);

            $message = 'Reset link sent to your email. Please check inbox/spam.';
            $messageType = 'ok';
        }
        $adminLookup->close();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'reset_password') {
    $token = trim($_POST['token'] ?? '');
    $newPassword = trim($_POST['new_password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');

    $tokenRecord = $_SESSION['admin_password_reset_tokens'][$token] ?? null;
    if (!$tokenRecord || ($tokenRecord['expires_at'] ?? 0) < time()) {
        $message = 'Reset token is invalid or expired.';
        $messageType = 'error';
    } elseif ($newPassword === '' || $confirmPassword === '') {
        $message = 'Please fill in all password fields.';
        $messageType = 'error';
    } elseif ($newPassword !== $confirmPassword) {
        $message = 'Password confirmation does not match.';
        $messageType = 'error';
    } elseif (strlen($newPassword) < 6) {
        $message = 'Password must be at least 6 characters.';
        $messageType = 'error';
    } else {
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $updatePassword = $conn->prepare("UPDATE admin SET admin_password = ? WHERE admin_email = ?");
        $updatePassword->bind_param("ss", $passwordHash, $tokenRecord['admin_email']);
        $updatePassword->execute();
        $updatePassword->close();
        unset($_SESSION['admin_password_reset_tokens'][$token]);

        redirect_with_admin_message("login_admin.php", "Password updated. Please sign in.", "success");
    }
}

$tokenFromLink = trim($_GET['token'] ?? '');
$hasValidToken = false;
if ($tokenFromLink !== '' && isset($_SESSION['admin_password_reset_tokens'][$tokenFromLink])) {
    $hasValidToken = ($_SESSION['admin_password_reset_tokens'][$tokenFromLink]['expires_at'] ?? 0) >= time();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Forgot Password - Petalora</title>
    <link rel="stylesheet" href="includes/admin.css">

    <style>
        .login-shell { 
            min-height: 100vh; 
            display: grid; 
            grid-template-columns: 1fr 1fr; 
            background: #0f0f10; 
        }
        
        .login-visual { 
            background: url('includes/login-visual.svg') center/cover no-repeat; 
            min-height: 100vh; 
        }

        .login-panel { 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            padding: 30px; 
            background: #121213; 
            color: #f2f2f2; 
        }

        .login-card { 
            width: min(440px, 100%); 
        }

        .brand { 
            font-family: "Times New Roman", serif; 
            font-size: 42px; 
            margin: 0 0 10px; 
            font-weight: 500; 
        }

        .small { 
            color: #9b9b9b; 
            text-transform: uppercase; 
            letter-spacing: .22em; 
            font-size: 10px; 
        }

        .title { 
            margin: 8px 0 18px; 
            font-family: "Times New Roman", serif; 
            font-size: 34px; 
            font-weight: 500; 
        }

        .line-input { 
            width: 100%; 
            padding: 10px 0; 
            background: transparent; 
            border: 0; 
            border-bottom: 1px solid #303033; 
            color: #fff; 
        }

        .line-input:focus { 
            outline: none; 
            border-bottom-color: #6f1d29; 
        }

        .error { 
            color: #e37d7d; 
            background: #2a1518; 
            border: 1px solid #583136; 
            padding: 10px; 
            margin-bottom: 10px; 
            font-size: 13px; 
        }

        .ok { 
            color: #97e6bf; 
            background: #152a20; 
            border: 1px solid #35614d; 
            padding: 10px; 
            margin-bottom: 10px; 
            font-size: 13px; 
        }

        .forgot-link { 
            color: #bfbfbf; 
            font-size: 13px; 
            text-decoration: none; 
        }
        
        .forgot-link:hover { 
            color: #fff; 
        }

        @media (max-width: 900px) { 
            .login-shell { 
                grid-template-columns: 1fr; 
            } 
            
            .login-visual { 
                display: none; 
            } 
        }
    </style>
    
</head>
<body>
<div class="login-shell">
    <div class="login-visual"></div>
    <div class="login-panel">
        <div class="login-card">
            <h1 class="brand">Petalora</h1>
            <p class="small">Admin Recovery</p>
            <h2 class="title"><?php echo $hasValidToken ? 'Set a new password.' : 'Reset your password.'; ?></h2>

            <?php if ($message !== ''): ?>
                <div class="<?php echo $messageType === 'error' ? 'error' : 'ok'; ?>"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <?php if ($hasValidToken): ?>
                <form method="post" data-custom-validate="true" novalidate>
                    <input type="hidden" name="action" value="reset_password">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($tokenFromLink); ?>">
                    <div class="form-group">
                        <label class="small">New Password</label>
                        <input class="line-input" type="password" name="new_password" data-required="true">
                        <div class="field-error" data-error-for="new_password"></div>
                    </div>
                    <div class="form-group">
                        <label class="small">Confirm Password</label>
                        <input class="line-input" type="password" name="confirm_password" data-required="true">
                        <div class="field-error" data-error-for="confirm_password"></div>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%;margin-top:8px;">Update Password</button>
                </form>
            <?php else: ?>
                <form method="post" data-custom-validate="true" novalidate>
                    <input type="hidden" name="action" value="request_reset">
                    <div class="form-group">
                        <label class="small">Admin Email</label>
                        <input class="line-input" type="email" name="admin_email" data-required="true">
                        <div class="field-error" data-error-for="admin_email"></div>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%;margin-top:8px;">Send Reset Link</button>
                </form>
            <?php endif; ?>

            <a href="login_admin.php" class="forgot-link" style="display:inline-block;margin-top:12px;">Back to login</a>
        </div>
    </div>
</div>
<script src="includes/admin.js"></script>
</body>
</html>
