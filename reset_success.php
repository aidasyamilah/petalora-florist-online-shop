<?php
$title = "Reset Success";
include "header.php";
?>

<style>
body{
    background:#f9f5f6;
    font-family:'Poppins',sans-serif;
}

.card{
    background:white;
    padding:40px;
    border-radius:15px;
    text-align:center;
    box-shadow:0 10px 30px rgba(0,0,0,0.1);
    max-width:400px;
    margin:auto;
    margin-top:100px;
}

h1{
    color:#4CAF50;
}

button{
    margin-top:20px;
    padding:10px 20px;
    background:#e89cae;
    border:none;
    color:white;
    border-radius:8px;
    cursor:pointer;
}
button:hover{
    background:#d97f94;
}
</style>

<div class="card">
    <h1>Password Reset Successful</h1>
    <p>You can now login with your new password.</p>

    <button onclick="window.location.href='login.php'">
        Go to Login
    </button>
</div>

<?php include "footer.php"; ?>