<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Back to admin login page if session is invalid
function admin_auth(): void
{
    if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
        $_SESSION['admin_redirect_after_login'] = basename($_SERVER['REQUEST_URI'] ?? 'dashboard_admin.php');
        header("Location: login_admin.php");
        exit;
    }
}

function set_admin_message(string $message, string $type = 'success'): void
{
    $_SESSION['admin_message'] = [
        'message' => $message,
        'type' => $type
    ];
}

function get_admin_message(): ?array
{
    if (!isset($_SESSION['admin_message'])) {
        return null;
    }

    $flash = $_SESSION['admin_message'];
    unset($_SESSION['admin_message']);
    return $flash;
}

function redirect_with_admin_message(string $url, string $message = '', string $type = 'success'): void
{
    if ($message !== '') {
        set_admin_message($message, $type);
    }
    header("Location: " . $url);
    exit;
}
?>
