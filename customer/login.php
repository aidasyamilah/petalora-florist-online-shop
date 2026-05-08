<?php
session_start();

// 如果已经登录，直接去homepage
if(isset($_SESSION['user_id'])){
    header("Location: homepage.php");
    exit();
}

$title = "Login - Petalora";
include "header.php";
?>

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Poppins', sans-serif;
}

body{
    background:#f2f2f2;
}

/* NAVBAR */
.navbar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:18px 80px;
    background:#fff;
}

.logo{
    color:#e89cae;
    font-size:26px;
    font-family:'Playfair Display', serif;
}

.nav-links a{
    margin:0 20px;
    text-decoration:none;
    color:#333;
    position:relative;
    padding:5px 0;
    transition:0.3s;
}

.nav-links a:hover{
    color:#e89cae;
    transform:translateY(-2px);
    text-shadow:0 3px 8px rgba(0,0,0,0.2);
}

.nav-links a::after{
    content:"";
    position:absolute;
    left:0;
    bottom:-3px;
    width:0;
    height:2px;
    background:#e89cae;
    transition:0.3s;
}

.nav-links a:hover::after{
    width:100%;
}

/* HERO */
.hero{
    position:relative;
    height:260px;
    display:flex;
    justify-content:center;
    align-items:center;
    flex-direction:column;
    color:white;
    overflow:hidden;
}

.hero::before{
    content:"";
    position:absolute;
    width:100%;
    height:100%;
    background:
        linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)),
        url('image/background2.png') center/cover;
    z-index:0;
}

.hero h1,
.hero p{
    position:relative;
    z-index:1;
    text-shadow:0 3px 10px rgba(0,0,0,0.7);
}

/* CONTAINER */
.container{
    display:flex;
    justify-content:center;
    align-items:center;
    margin-top:-80px;
    position:relative;
    z-index:10;
}

/* CARD */
.card{
    width:800px;
    display:flex;
    border-radius:15px;
    overflow:hidden;
    background:rgba(255,255,255,0.15);
    backdrop-filter:blur(15px);
    box-shadow:0 20px 60px rgba(0,0,0,0.3);
}

/* LEFT */
.left{
    width:50%;
    background:rgba(232,156,174,0.85);
    padding:60px 40px;
    color:white;
    display:flex;
    flex-direction:column;
    justify-content:center;
}

.left h2{
    text-align:center;
    letter-spacing:6px;
    margin-bottom:30px;
    font-family:'Playfair Display', serif;
}

/* LOGIN TYPE */
.login-type{
    display:flex;
    justify-content:center;
    margin-bottom:15px;
}

.login-type button{
    padding:8px 20px;
    border:none;
    background:white;
    color:#e89cae;
    cursor:pointer;
    font-size:13px;
    transition:0.3s;
}

.login-type .active{
    background:#e89cae;
    color:white;
}

/* INPUT */
.left input{
    width:100%;
    padding:12px;
    margin:12px 0;
    border:none;
    border-bottom:1px solid white;
    background:transparent;
    color:white;
}

.left input::placeholder{
    color:rgba(255,255,255,0.85);
}

/* PASSWORD */
.password-box{
    position:relative;
}

.show-btn{
    position:absolute;
    right:10px;
    top:50%;
    transform:translateY(-50%);
    font-size:13px;
    color:white;
    cursor:pointer;
}

.options{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-top:10px;
    font-size:13px;
}

.options label{
    display:flex;
    align-items:center;
    gap:6px;
    cursor:pointer;
}

.options input[type="checkbox"]{
    accent-color:white;
    width:14px;
    height:14px;
}

.options a{
    color:white;
    text-decoration:none;
}

/* BUTTON */
.left button{
    width:100%;
    padding:12px;
    margin-top:25px;
    border:none;
    background:white;
    color:#e89cae;
    border-radius:6px;
    cursor:pointer;
}

/* RIGHT */
.right{
    width:50%;
    padding:50px;
    text-align:center;
    background:white;
}

.right img{
    width:140px;
    margin:20px 0;
    border-radius:10px;
}

/* FOOTER */
.footer{
    margin-top:60px;
    background:#333;
    color:#fff;
    padding:40px 80px;
    display:flex;
    justify-content:space-between;
}

.footer div{
    width:22%;
}

.bottom{
    text-align:center;
    background:#222;
    color:#ccc;
    padding:10px;
}

.error-box{
    background:#ffe5e5;
    color:#d10000;
    padding:10px;
    border-radius:8px;
    margin-bottom:15px;
    font-size:13px;
    text-align:center;
    border:1px solid #ffb3b3;
    animation:shake 0.3s;
}

@keyframes shake{
    0%{transform:translateX(0);}
    25%{transform:translateX(-5px);}
    50%{transform:translateX(5px);}
    75%{transform:translateX(-5px);}
    100%{transform:translateX(0);}
}
</style>

<div class="hero">
    <h1>LOGIN</h1>
    <p>Login to your account, enjoy exclusive floral offers</p>
</div>

<div class="container">
<div class="card">

<!-- LEFT SIDE -->
<div class="left">

<div class="login-type">
    <button id="userBtn" class="active" type="button" onclick="switchLogin('user')">User</button>
</div>

<h2>LOGIN</h2>
<?php if(isset($_GET['error'])): ?>
<div class="error-box">
    <?php 
        if($_GET['error'] == "wrong_password"){
            echo "❌ Incorrect password. Please try again.";
        } else if($_GET['error'] == "not_found"){
            echo "❌ Full Name or Email not found.";
        }
    ?>
</div>
<?php endif; ?>

<form action="php/login_process.php" method="POST">

    <input type="text" name="email" placeholder="Email" required>

    <div class="password-box">
        <input type="password" name="password" id="password" placeholder="Password" required>
        <span class="show-btn" onclick="togglePassword()">Show</span>
    </div>

    <div class="options">
        <label>
            <input type="checkbox">
            Remember me
        </label>
       <a href="forgot-password.php">Forgot password?</a>
    </div>

    <button type="submit">Login</button>

</form>

</div>

<!-- RIGHT SIDE (FIXED STRUCTURE) -->
<div class="right">
    <h3>Welcome to <span style="color:#e89cae;">Petalora</span></h3>
    <p>Bring warmth, love and beauty into every special moment 🌸</p>

    <img src="image/flower2.png">

    <p style="margin-top:20px;">
        Don't have an account?
    </p>

    <button onclick="window.location.href='register.php'" style="
        margin-top:10px;
        padding:10px 20px;
        border-radius:10px;
        border:none;
        background:#e89cae;
        color:white;
        cursor:pointer;">
        Create Account
    </button>
</div>

</div>
</div>


<script>
function togglePassword(){
    const pass = document.getElementById("password");
    pass.type = pass.type === "password" ? "text" : "password";
}

</script>

<?php include "footer.php"; ?>