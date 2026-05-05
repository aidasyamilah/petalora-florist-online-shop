<?php
session_start();
include "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // ==========================
    // INPUT (EMAIL LOGIN ONLY)
    // ==========================
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        header("Location: ../login.php?error=not_found");
        exit();
    }

    // ==========================
    // FIND USER BY EMAIL ONLY
    // ==========================
    $stmt = $conn->prepare("
        SELECT * FROM users 
        WHERE email = ?
        LIMIT 1
    ");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {

        // ==========================
        // CHECK STATUS (NO DB CHANGE)
        // ==========================
        if ($row['status'] !== 'active') {
            header("Location: ../login.php?error=not_found");
            exit();
        }

        // ==========================
        // PASSWORD VERIFY
        // ==========================
        if (password_verify($password, $row['password'])) {

            // ==========================
            // SESSION SET
            // ==========================
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['full_name'] = $row['full_name'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['role'] = $row['role'];

            // ==========================
            // UPDATE LAST LOGIN
            // ==========================
            $update = $conn->prepare("
                UPDATE users 
                SET last_login = NOW() 
                WHERE user_id = ?
            ");
            $update->bind_param("i", $row['user_id']);
            $update->execute();

            // ==========================
            // SUCCESS
            // ==========================
            header("Location: ../homepage.php?login=success");
            exit();

        } else {
            header("Location: ../login.php?error=wrong_password");
            exit();
        }

    } else {
        header("Location: ../login.php?error=not_found");
        exit();
    }
}

$conn->close();
?>