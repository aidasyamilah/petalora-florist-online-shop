<?php
include "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // ==========================
    // GET INPUT
    // ==========================
    $token = trim($_POST['token'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // ==========================
    // BASIC VALIDATION
    // ==========================
    if (empty($token) || empty($password) || empty($confirm_password)) {
        header("Location: ../reset_password.php?error=missing");
        exit();
    }

    if ($password !== $confirm_password) {
        header("Location: ../reset_password.php?error=notmatch");
        exit();
    }

    if (strlen($password) < 8) {
        header("Location: ../reset_password.php?error=weak");
        exit();
    }

    // ==========================
    // CHECK TOKEN + EXPIRY
    // ==========================
    $stmt = $conn->prepare("
        SELECT id 
        FROM users 
        WHERE reset_token = ? 
        AND reset_expires > NOW()
    ");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {

        $user_id = $row['id'];
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // ==========================
        // UPDATE PASSWORD + CLEAR TOKEN
        // ==========================
        $update = $conn->prepare("
            UPDATE users 
            SET password = ?, reset_token = NULL, reset_expires = NULL 
            WHERE id = ?
        ");
        $update->bind_param("si", $hashed_password, $user_id);

        if ($update->execute()) {

            // ==========================
            // SUCCESS PAGE
            // ==========================
            echo "
            <html>
            <head>
                <title>Success</title>
                <style>
                    body{
                        margin:0;
                        font-family:Poppins, sans-serif;
                        background:#f9f5f6;
                        display:flex;
                        justify-content:center;
                        align-items:center;
                        height:100vh;
                    }

                    .box{
                        background:white;
                        padding:40px;
                        border-radius:20px;
                        text-align:center;
                        box-shadow:0 10px 30px rgba(0,0,0,0.1);
                        animation:pop 0.3s ease;
                    }

                    h2{
                        color:#e89cae;
                    }

                    p{
                        color:#666;
                        font-size:14px;
                    }

                    @keyframes pop{
                        from{transform:scale(0.8); opacity:0;}
                        to{transform:scale(1); opacity:1;}
                    }
                </style>
            </head>

            <body>
                <div class='box'>
                    <h2>✔ Password Reset Successful</h2>
                    <p>Redirecting to login page...</p>
                </div>

                <script>
                    setTimeout(function(){
                        window.location.href = '../login.php?reset=success';
                    }, 2000);
                </script>
            </body>
            </html>
            ";

        } else {
            header("Location: ../reset_password.php?error=update_failed");
        }

    } else {
        header("Location: ../reset_password.php?error=expired");
    }

    $stmt->close();
}

$conn->close();
?>