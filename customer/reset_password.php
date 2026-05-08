<?php
$title = "Reset Password - Petalora";
include "header.php";

// ==========================
// SECURITY CHECK (IMPORTANT)
// ==========================
if(!isset($_GET['token']) || empty($_GET['token'])){
    echo "<div class='box'><h2 style='color:red'>Invalid reset link</h2></div>";
    include "footer.php";
    exit();
}
?>

<style>

body{
    background:#f9f5f6;
    font-family:'Poppins',sans-serif;
}

.box{
    background:white;
    padding:30px;
    border-radius:10px;
    width:350px;
    margin:80px auto;
    box-shadow:0 10px 30px rgba(0,0,0,0.1);
}

h2{
    text-align:center;
    color:#e89cae;
    margin-bottom:15px;
}

input{
    width:100%;
    padding:10px;
    margin:10px 0;
    border-radius:6px;
    border:1px solid #ccc;
    transition:0.3s;
}

input.error{
    border:2px solid red;
}

input.success{
    border:2px solid green;
}

.password-box{
    position:relative;
}

.password-box span{
    position:absolute;
    right:10px;
    top:50%;
    transform:translateY(-50%);
    cursor:pointer;
    font-size:12px;
    color:#e89cae;
}

.msg{
    font-size:12px;
    margin-top:-5px;
}

.msg.error{ color:red; }
.msg.success{ color:green; }

button{
    width:100%;
    padding:10px;
    background:#e89cae;
    border:none;
    color:white;
    border-radius:6px;
    margin-top:10px;
    cursor:pointer;
}

button:hover{
    background:#d97f94;
}

.error-box{
    color:red;
    font-size:13px;
    text-align:center;
    margin-bottom:10px;
}

</style>

<div class="box">

<h2>Reset Password</h2>

<!-- ERROR MESSAGE -->
<?php if(isset($_GET['error'])): ?>
    <div class="error-box">
        <?php
            if($_GET['error'] == "expired") echo "Link expired or invalid";
            if($_GET['error'] == "missing") echo "Missing information";
            if($_GET['error'] == "notmatch") echo "Passwords do not match";
            if($_GET['error'] == "weak") echo "Password must be 8+ chars, 1 uppercase & 1 number";
        ?>
    </div>
<?php endif; ?>

<form action="php/reset_process.php" method="POST" onsubmit="return validateForm()">

    <!-- TOKEN -->
    <input type="hidden" name="token" id="token" value="<?php echo htmlspecialchars($_GET['token']); ?>">

    <!-- PASSWORD -->
    <div class="password-box">
        <input type="password"
               id="password"
               name="password"
               placeholder="New Password"
               required
               onkeyup="checkPassword()">

        <span onclick="togglePassword('password', this)">Show</span>
    </div>
    <p id="passMsg" class="msg"></p>

    <!-- CONFIRM PASSWORD -->
    <div class="password-box">
        <input type="password"
               id="confirm_password"
               name="confirm_password"
               placeholder="Confirm Password"
               required
               onkeyup="checkConfirm()">

        <span onclick="togglePassword('confirm_password', this)">Show</span>
    </div>
    <p id="confirmMsg" class="msg"></p>

    <button type="submit" id="btn">Reset Password</button>

</form>

</div>

<script>

// ==========================
// TOGGLE PASSWORD
// ==========================
function togglePassword(id, el){
    const input = document.getElementById(id);

    if(input.type === "password"){
        input.type = "text";
        el.innerText = "Hide";
    }else{
        input.type = "password";
        el.innerText = "Show";
    }
}

// ==========================
// PASSWORD VALIDATION
// ==========================
function checkPassword(){
    const pass = document.getElementById("password");
    const msg = document.getElementById("passMsg");

    const pattern = /^(?=.*[A-Z])(?=.*[0-9]).{8,}$/;

    if(!pattern.test(pass.value)){
        msg.innerText = "Must be 8+ chars, 1 uppercase & 1 number";
        msg.className = "msg error";
        pass.classList.add("error");
        pass.classList.remove("success");
    }else{
        msg.innerText = "Strong password";
        msg.className = "msg success";
        pass.classList.remove("error");
        pass.classList.add("success");
    }

    checkConfirm();
}

// ==========================
// CONFIRM PASSWORD
// ==========================
function checkConfirm(){
    const pass = document.getElementById("password");
    const confirm = document.getElementById("confirm_password");
    const msg = document.getElementById("confirmMsg");

    if(confirm.value !== pass.value){
        msg.innerText = "Password not match";
        msg.className = "msg error";
        confirm.classList.add("error");
        confirm.classList.remove("success");
    }else{
        msg.innerText = "Password match";
        msg.className = "msg success";
        confirm.classList.remove("error");
        confirm.classList.add("success");
    }
}

// ==========================
// FORM VALIDATION
// ==========================
function validateForm(){

    document.getElementById("btn").innerText = "Processing...";

    checkPassword();
    checkConfirm();

    const passError = document.getElementById("password").classList.contains("error");
    const confirmError = document.getElementById("confirm_password").classList.contains("error");

    return !(passError || confirmError);
}

</script>

<?php include "footer.php"; ?>