<?php
$title = "Petalora Register";
include "header.php";
?>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Poppins',sans-serif;
}

body{
    background:#f9f5f6;
    color:#333;
}

/* =========================
   CENTER CONTAINER
========================= */
.container{
    display:flex;
    justify-content:center;
    align-items:center;
    min-height:100vh;
    padding:20px;
}

/* =========================
   CARD (SOFT UI STYLE)
========================= */
.card{
    width:380px;
    background:white;
    padding:28px;
    border-radius:18px;
    box-shadow:0 10px 30px rgba(0,0,0,0.08);
}

/* =========================
   HEADER
========================= */
.card-header{
    text-align:center;
    background:linear-gradient(135deg,#f8c8d8,#e89cae);
    color:white;
    padding:18px;
    border-radius:16px;
    margin-bottom:20px;
}

.card-header h2{
    font-size:22px;
    font-weight:700;
}

.card-header p{
    font-size:12px;
    opacity:0.9;
}

/* =========================
   INPUT + SELECT (UNIFIED STYLE)
========================= */
input, select{
    width:100%;
    padding:12px;
    margin:10px 0;
    border:1px solid #e0e0e0;
    border-radius:10px;
    font-size:14px;
    background:#fff;
    transition:0.2s;
}

input:focus, select:focus{
    border-color:#e89cae;
    outline:none;
    box-shadow:0 0 0 3px rgba(232,156,174,0.15);
}

/* =========================
   PHONE INPUT (CLEAN + SOFT)
========================= */
.phone-box{
    display:flex;
    align-items:center;
    width:100%;
    margin:10px 0;
}

.country-code{
    background:#f3f3f3;
    padding:12px 14px;
    border:1px solid #e0e0e0;
    border-right:none;
    border-radius:10px 0 0 10px;
    font-size:14px;
    color:#555;
}

.phone-box input{
    flex:1;
    padding:12px;
    border:1px solid #e0e0e0;
    border-radius:0 10px 10px 0;
    font-size:14px;
}

/* =========================
   BUTTON
========================= */
button{
    width:100%;
    padding:12px;
    margin-top:15px;
    border:none;
    background:#e89cae;
    color:white;
    border-radius:10px;
    cursor:pointer;
    font-weight:600;
    transition:0.2s;
}

button:hover{
    background:#d97f94;
}

/* =========================
   MESSAGE TEXT
========================= */
.msg{
    font-size:12px;
    margin-top:-5px;
    margin-bottom:5px;
}

.msg.error{
    color:#e74c3c;
}

.msg.success{
    color:#27ae60;
}

/* =========================
   PASSWORD BOX
========================= */
.password-box{
    position:relative;
}

.password-box span{
    position:absolute;
    right:12px;
    top:50%;
    transform:translateY(-50%);
    font-size:12px;
    cursor:pointer;
    color:#e89cae;
}

.phone-box{
    display:flex;
    width:100%;
    margin:10px 0;
}

.country-code{
    display:flex;
    align-items:center;
    justify-content:center;

    padding:12px;
    min-width:55px;

    background:#f3f3f3;
    border:1px solid #e0e0e0;
    border-right:none;

    border-radius:10px 0 0 10px;

    font-size:14px;
    color:#555;
}

.phone-box input{
    flex:1;
    padding:12px;

    border:1px solid #e0e0e0;
    border-radius:0 10px 10px 0;

    font-size:14px;
}

/* =========================
   VALIDATION STATES
========================= */

input.error, select.error{
    border:1px solid #e74c3c !important;
    box-shadow:0 0 0 3px rgba(231,76,60,0.15);
}

input.success, select.success{
    border:1px solid #27ae60 !important;
    box-shadow:0 0 0 3px rgba(39,174,96,0.15);
}

.msg.error{
    color:#e74c3c;
    font-weight:500;
}

.msg.success{
    color:#27ae60;
    font-weight:500;
}

/* =========================
   POPUP (CLEAN)
========================= */
.popup{
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,0.4);
    display:flex;
    justify-content:center;
    align-items:center;
    opacity:0;
    pointer-events:none;
    transition:0.25s;
}

.popup.show{
    opacity:1;
    pointer-events:auto;
}

.popup-content{
    background:white;
    padding:30px;
    border-radius:14px;
    text-align:center;
    animation:pop 0.25s ease;
}

@keyframes pop{
    from{transform:scale(0.9); opacity:0;}
    to{transform:scale(1); opacity:1;}
}
</style>

<div class="container">
<div class="card">

<div class="card-header">
    <h2>Petalora</h2>
    <p>Fresh Flowers, Delivered with Love 🌸</p>
</div>

<form action="php/register_process.php" method="POST" onsubmit="return validateForm()">

<!-- NAME -->
<input type="text" name="full_name" id="full_name" placeholder="Full Name" required onkeyup="checkFullName()">
<p id="fullnameMsg" class="msg"></p>

<!-- EMAIL -->
<input type="email" name="email" id="email" placeholder="Email" required onkeyup="validateEmail(); checkEmailExist();">
<p id="emailMsg" class="msg"></p>

<!-- PHONE -->
<div class="phone-box">

    <span class="country-code">+60</span>

    <input type="text"
           id="phone_number"
           name="phone_number"
           placeholder="Phone number"
           required
           onkeyup="validatePhone(); checkPhoneExist();">

</div>

<p id="phoneMsg" class="msg"></p>

<!-- ADDRESS -->
<input type="text" name="address" placeholder="Address" required>

<p style="font-weight:600;color:#e89cae;margin-top:15px;">
Delivery Information (Melaka Only)
</p>

<!-- DISTRICT + AREA -->
<div style="display:flex; gap:10px;">

    <div style="flex:1;">
        <select name="district" id="district" required onchange="updateArea()">
            <option value="">District</option>
            <option value="Melaka Tengah">Melaka Tengah</option>
            <option value="Alor Gajah">Alor Gajah</option>
            <option value="Jasin">Jasin</option>
        </select>
    </div>

    <div style="flex:1;">
        <select name="area" id="area" required onchange="validateArea()">
            <option value="">Area</option>
        </select>
        <p id="areaMsg" class="msg"></p>
    </div>

</div>

<!-- POSTCODE -->
<input type="text" name="postcode" id="postcode" placeholder="Postcode" required onkeyup="validatePostcode()">
<p id="postcodeMsg" class="msg"></p>

<!-- PASSWORD -->
<div class="password-box">
    <input type="password"
           id="password"
           name="password"
           placeholder="Password"
           required
           onkeyup="checkPassword()">

    <span onclick="togglePassword('password', this)">Show</span>
</div>

<p id="passMsg" class="msg"></p>

<!-- CONFIRM PASSWORD (ONLY ONE - FIXED) -->
<div class="password-box">
    <input type="password"
           id="confirmPassword"
           name="confirm_password"
           placeholder="Confirm Password"
           required
           onkeyup="checkConfirm()">

    <span onclick="togglePassword('confirmPassword', this)">Show</span>
</div>

<p id="confirmMsg" class="msg"></p>

<button type="submit">Register</button>

</form>
</div>
</div>

<script>

/* =========================
   FULL NAME
========================= */
function checkFullName(){
    const el = document.getElementById("full_name");
    const msg = document.getElementById("fullnameMsg");

    if(el.value.trim().length < 3){
        msg.innerText = "Name must be at least 3 characters";
        msg.className = "msg error";
        el.classList.add("error");
        el.classList.remove("success");
        return false;
    }

    msg.innerText = "✔ Valid";
    msg.className = "msg success";
    el.classList.add("success");
    el.classList.remove("error");
    return true;
}

/* =========================
   EMAIL
========================= */
function validateEmail(){
    const el = document.getElementById("email");
    const msg = document.getElementById("emailMsg");

    const val = el.value.trim();

    const format = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const gmail = /@gmail\.com$/;

    if(!format.test(val)){
        msg.innerText = "Invalid email format";
        msg.className = "msg error";
        el.classList.add("error");
        return false;
    }

    if(!gmail.test(val)){
        msg.innerText = "Only Gmail allowed";
        msg.className = "msg error";
        el.classList.add("error");
        return false;
    }

    msg.innerText = "✔ Valid email";
    msg.className = "msg success";
    el.classList.add("success");
    return true;
}

/* =========================
   PHONE
========================= */
function validatePhone(){
    const phone = document.getElementById("phone_number");
    const msg = document.getElementById("phoneMsg");

    let value = phone.value.trim();

    // ❌ remove spaces just in case
    value = value.replace(/\s+/g, '');

    phone.value = value;

    // ❌ must NOT start with 0
    if(value.startsWith("0")){
        msg.innerText = "❌ Do not include leading 0 (Malaysia format with +60)";
        msg.className = "msg error";
        phone.classList.add("error");
        phone.classList.remove("success");
        return false;
    }

    // ❌ digits only
    if(!/^[0-9]+$/.test(value)){
        msg.innerText = "❌ Numbers only";
        msg.className = "msg error";
        phone.classList.add("error");
        phone.classList.remove("success");
        return false;
    }

    // ❌ length rule (Malaysia mobile after +60)
    if(value.length < 9 || value.length > 10){
        msg.innerText = "❌ Phone number must be 9–10 digits (after +60)";
        msg.className = "msg error";
        phone.classList.add("error");
        phone.classList.remove("success");
        return false;
    }

    // ✅ valid
    msg.innerText = "✔ Valid Malaysia number";
    msg.className = "msg success";
    phone.classList.remove("error");
    phone.classList.add("success");

    return true;
}

/* =========================
   POSTCODE
========================= */
function validatePostcode(){
    const el = document.getElementById("postcode");
    const msg = document.getElementById("postcodeMsg");

    if(!/^[0-9]{5}$/.test(el.value)){
        msg.innerText = "Postcode must be 5 digits";
        msg.className = "msg error";
        el.classList.add("error");
        return false;
    }

    msg.innerText = "✔ Valid";
    msg.className = "msg success";
    el.classList.add("success");
    return true;
}

/* =========================
   PASSWORD
========================= */
function checkPassword(){
    const el = document.getElementById("password");
    const msg = document.getElementById("passMsg");

    const pattern = /^(?=.*[A-Z])(?=.*[0-9]).{8,}$/;

    if(!pattern.test(el.value)){
        msg.innerText = "at least 8 characters, 1 uppercase letter and 1 number";
        msg.className = "msg error";
        el.classList.add("error");
        return false;
    }

    msg.innerText = "✔ Strong password";
    msg.className = "msg success";
    el.classList.add("success");
    return true;
}

/* =========================
   CONFIRM PASSWORD
========================= */
function checkConfirm(){
    const p = document.getElementById("password").value;
    const c = document.getElementById("confirmPassword");
    const msg = document.getElementById("confirmMsg");

    if(c.value === ""){
        msg.innerText = "";
        c.classList.remove("error","success");
        return false;
    }

    if(c.value !== p){
        msg.innerText = "Password not match";
        msg.className = "msg error";
        c.classList.add("error");
        c.classList.remove("success");
        return false;
    }

    msg.innerText = "✔ Match";
    msg.className = "msg success";
    c.classList.add("success");
    c.classList.remove("error");
    return true;
}

function togglePassword(id, el){
    const input = document.getElementById(id);

    if(!input) return;

    if(input.type === "password"){
        input.type = "text";
        el.innerText = "Hide";
    }else{
        input.type = "password";
        el.innerText = "Show";
    }
}

/* =========================
   AREA VALIDATION
========================= */
function validateArea(){
    const d = document.getElementById("district").value;
    const a = document.getElementById("area");
    const msg = document.getElementById("areaMsg");

    if(!d || !a.value){
        msg.innerText = "Select area";
        msg.className = "msg error";
        a.classList.add("error");
        return false;
    }

    if(!areaData[d].includes(a.value)){
        msg.innerText = "Invalid area";
        msg.className = "msg error";
        a.classList.add("error");
        return false;
    }

    msg.innerText = "✔ Valid";
    msg.className = "msg success";
    a.classList.add("success");
    return true;
}

function scrollToError(){
    const errorField = document.querySelector(".error");

    if(errorField){
        errorField.scrollIntoView({
            behavior: "smooth",
            block: "center"
        });
    }
}

/* =========================
   FINAL CHECK
========================= */
function validateForm(){
    const v1 = checkFullName();
    const v2 = validateEmail();
    const v3 = validatePhone();
    const v4 = validatePostcode();
    const v5 = checkPassword();
    const v6 = checkConfirm();
    const v7 = validateArea();

    if(!(v1 && v2 && v3 && v4 && v5 && v6 && v7)){
        scrollToError();   // ⭐重点
        return false;
    }

    return true;
}


</script>

<div id="successPopup" class="popup">
    <div class="popup-content">
        <h2>✔ Success</h2>
        <p>Account created</p>
    </div>
</div>

<?php include "footer.php"; ?>