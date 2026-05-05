<?php
include "db.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../phpmailer/src/PHPMailer.php';
require '../phpmailer/src/SMTP.php';
require '../phpmailer/src/Exception.php';

// ==========================
// GET EMAIL
// ==========================
$email = trim($_POST['email'] ?? '');

if (empty($email)) {
    header("Location: ../forgot-password.php?status=error");
    exit();
}

$email = filter_var($email, FILTER_SANITIZE_EMAIL);

// ==========================
// CHECK USER
// ==========================
$stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {

    // ==========================
    // TOKEN
    // ==========================
    $token = bin2hex(random_bytes(50));
    $expiry = date("Y-m-d H:i:s", strtotime("+24 hours"));

    $update = $conn->prepare("
        UPDATE users 
        SET reset_token=?, reset_expires=? 
        WHERE email=?
    ");
    $update->bind_param("sss", $token, $expiry, $email);
    $update->execute();

    // ==========================
    // LINK
    // ==========================
    $base = "http://localhost/petalora/online_florist/";
    $link = $base . "reset_password.php?token=" . urlencode($token);

    // ==========================
    // EMAIL
    // ==========================
    $mail = new PHPMailer(true);

    try {

        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;

        // 🔥 YOUR GMAIL
        $mail->Username = 'yourgmail@gmail.com';
        $mail->Password = 'YOUR_APP_PASSWORD'; // 16 chars, no spaces

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // FIX XAMPP SSL ISSUE
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];

        // IMPORTANT SETTINGS
        $mail->CharSet = "UTF-8";
        $mail->setFrom('yourgmail@gmail.com', 'Petalora');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Password Reset - Petalora';

        $mail->Body = "
        <div style='font-family:Arial;text-align:center'>
            <h2 style='color:#e89cae'>🌸 Reset Password</h2>
            <p>Click below to reset your password:</p>

            <a href='$link' style='padding:12px 20px;background:#e89cae;color:white;text-decoration:none;border-radius:8px;'>
                Reset Password
            </a>

            <p style='font-size:12px;color:#888;margin-top:20px'>
                Link expires in 24 hours
            </p>
        </div>
        ";

        $mail->send();

        header("Location: ../forgot-password.php?status=success");
        exit();

    } catch (Exception $e) {
        echo "Email Error: " . $mail->ErrorInfo;
        exit();
    }

} else {
    header("Location: ../forgot-password.php?status=error");
    exit();
}
?>