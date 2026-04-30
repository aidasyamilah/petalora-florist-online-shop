<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <title><?php echo $title ?? "Petalora"; ?></title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        body{
            margin:0;
            font-family:'Poppins',sans-serif;
        }

        .navbar{
            display:flex;
            justify-content:space-between;
            align-items:center;
            padding:15px 30px;
            background:#e89cae;
            color:white;
        }

        .logo{
            font-weight:700;
            font-size:20px;
        }

        .nav-links a{
            color:white;
            text-decoration:none;
            margin-left:15px;
            font-weight:500;
        }

        .nav-links a:hover{
            text-decoration:underline;
        }
    </style>
</head>

<body>

<div class="navbar">
    <div class="logo">Petalora</div>

    <div class="nav-links">
        <a href="homepage.php">Home</a>
        <a href="category.php">Category</a>
        <a href="cart.php">Cart</a>
        <a href="about.php">About</a>

        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="profile.php">Profile</a>
            <a href="php/logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
        <?php endif; ?>
    </div>
</div>