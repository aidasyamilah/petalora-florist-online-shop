<?php
$title = "Forgot Password - Petalora";
include "header.php";
?>

<style>
body{
    background:#f9f5f6;
    font-family:'Poppins',sans-serif;
}

/* CENTER */
.container{
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
}

.card{
    background:white;
    padding:50px;
    border-radius:20px;
    box-shadow:0 10px 30px rgba(0,0,0,.1);
    width:350px;
    text-align:center;
}

h2{
    margin-bottom:10px;
    font-family:'Playfair Display',serif;
    color:#e89cae;
}

p{
    font-size:13px;
    color:#777;
    margin-bottom:20px;
}

/* INPUT */
input{
    width:100%;
    padding:12px;
    margin:12px 0;
    border:1px solid #ddd;
    border-radius:10px;
}

input.error{
    border:1px solid red;
}

/* MESSAGE */
.msg{
    font-size:12px;
    text-align:left;
    margin-top:-8px;
}

.error{
    color:red;
}

.success{
    color:green;
    font-size:13px;
    margin-bottom:10px;
}

/* BUTTON */
button{
    width:100%;
    padding:12px;
    border:none;
    border-radius:10px;
    background:#e89cae;
    color:white;
    font-weight:600;
    cursor:pointer;
    margin-top:10px;
}

button:hover{
    background:#d87f94;
}

/* BACK */
.back{
    margin-top:15px;
    display:block;
    color:#999;
    text-decoration:none;
    font-size:13px;
}
</style>

<div class="container">
<div class="card">

<h2>Forgot Password</h2>
<p>Enter your email and we’ll send you a reset link</p>

<!-- 🔥 SUCCESS / ERROR MESSAGE -->
<?php if(isset($_GET['status']) && $_GET['status'] == 'success'): ?>
    <p class="success">✔ Reset link sent to your email</p>
<?php endif; ?>

<?php if(isset($_GET['status']) && $_GET['status'] == 'error'): ?>
    <p class="msg error">❌ Email not found</p>
<?php endif; ?>

<form action="php/forgot_password.php" method="POST" onsubmit="return validateEmail()">

    <input type="email" id="email" name="email" placeholder="Enter your email" required>
    <p id="emailMsg" class="msg"></p>

    <button type="submit" id="btn">Send Reset Link</button>

</form>

<a href="login.php" class="back">← Back to Login</a>

</div>
</div>

<script>
// ==========================
// EMAIL VALIDATION
// ==========================
function validateEmail(){
    const email = document.getElementById("email");
    const msg = document.getElementById("emailMsg");

    const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if(email.value.trim() === ""){
        msg.innerText = "Email is required";
        msg.className = "msg error";
        return false;
    }

    if(!pattern.test(email.value)){
        msg.innerText = "Invalid email format";
        msg.className = "msg error";
        email.classList.add("error");
        return false;
    }

    // loading effect (FYP bonus ⭐)
    document.getElementById("btn").innerText = "Sending...";
    return true;
}
</script>

<?php include "footer.php"; ?>