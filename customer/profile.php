<?php
session_start();
include "php/db.php";

$title = "My Profile - Petalora";
include "header.php";

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<style>
body{
    background:linear-gradient(135deg,#f9f5f6,#fdeff3);
    font-family:'Poppins',sans-serif;
}

.container{
    width:420px;
    margin:80px auto;
    background:white;
    padding:35px;
    border-radius:25px;
    box-shadow:0 20px 60px rgba(0,0,0,0.12);
}

h2{
    text-align:center;
    color:#e89cae;
    margin-bottom:20px;
}

/* INPUT */
input, select{
    width:100%;
    padding:12px;
    margin:8px 0 12px;
    border-radius:10px;
    border:1px solid #ddd;
}

/* BUTTON */
button{
    width:100%;
    padding:12px;
    background:#e89cae;
    color:white;
    border:none;
    border-radius:10px;
    cursor:pointer;
}

/* AVATAR */
.avatar-wrapper{
    position:relative;
    width:120px;
    margin:0 auto 15px;
    cursor:pointer;
}

.profile-img{
    width:120px;
    height:120px;
    border-radius:50%;
    object-fit:cover;
    border:4px solid #e89cae;
}

/* POPUP */
.popup{
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,0.5);
    display:flex;
    justify-content:center;
    align-items:center;
    opacity:0;
    pointer-events:none;
    transition:0.3s;
}

.popup.show{
    opacity:1;
    pointer-events:auto;
}

.popup-content{
    background:white;
    padding:40px;
    border-radius:20px;
    text-align:center;
    animation:pop 0.4s ease;
}

@keyframes pop{
    from{transform:scale(0.7); opacity:0;}
    to{transform:scale(1); opacity:1;}
}

.checkmark{
    font-size:40px;
    color:#e89cae;
}
</style>

<div class="container">

<h2>My Profile</h2>

<!-- AVATAR -->
<div class="avatar-wrapper" id="avatarClick">
    <img id="previewImg"
         class="profile-img"
         src="<?php echo !empty($user['profile_image']) ? 'uploads/'.$user['profile_image'] : 'image/default.png'; ?>">
</div>

<input type="file" id="fileInput" accept="image/*" hidden>

<form action="php/update_profile.php" method="POST">

<label>Email</label>
<input type="email" name="email" value="<?php echo $user['email']; ?>">

<label>Phone</label>
<input type="text" name="phone_number" value="<?php echo $user['phone_number']; ?>">

<label>Address</label>
<input type="text" name="address" value="<?php echo $user['address']; ?>">

<!-- DISTRICT -->
<label>District</label>
<select name="district" id="district" onchange="updateArea()" required>
    <option value="Melaka Tengah" <?php if($user['district']=="Melaka Tengah") echo "selected"; ?>>Melaka Tengah</option>
    <option value="Alor Gajah" <?php if($user['district']=="Alor Gajah") echo "selected"; ?>>Alor Gajah</option>
    <option value="Jasin" <?php if($user['district']=="Jasin") echo "selected"; ?>>Jasin</option>
</select>

<!-- AREA -->
<label>Area</label>
<select name="area" id="area" required>
    <option value="<?php echo $user['area']; ?>">
        <?php echo $user['area']; ?>
    </option>
</select>

<label>State</label>
<input type="text" name="state" value="<?php echo $user['state']; ?>">

<label>Postcode</label>
<input type="text" name="postcode" value="<?php echo $user['postcode']; ?>">

<button type="submit">Update Profile</button>

</form>

</div>

<!-- POPUP -->
<div id="updatePopup" class="popup">
    <div class="popup-content">
        <div class="checkmark">✔</div>
        <h2>Profile Updated!</h2>
        <p>Saved successfully</p>
    </div>
</div>

<script>

/* AREA DATA (SAME AS REGISTER) */
const areaData = {
    "Melaka Tengah": ["Kota Laksamana","Melaka Raya","Klebang","Batu Berendam","Cheng","Peringgit"],
    "Alor Gajah": ["Masjid Tanah","Lendu","Durian Tunggal"],
    "Jasin": ["Merlimau","Bemban","Serkam"]
};

function updateArea(){
    const d = document.getElementById("district").value;
    const a = document.getElementById("area");

    a.innerHTML = "";

    if(areaData[d]){
        areaData[d].forEach(x=>{
            let opt = document.createElement("option");
            opt.value = x;
            opt.textContent = x;
            a.appendChild(opt);
        });
    }
}

/* POPUP */
function showPopup(){
    document.getElementById("updatePopup").classList.add("show");
    setTimeout(()=>{
        document.getElementById("updatePopup").classList.remove("show");
    },2000);
}

/* CHECK SUCCESS */
window.onload = function(){
    const params = new URLSearchParams(window.location.search);
    if(params.get("update") === "success"){
        showPopup();
    }
};

</script>

<?php include "footer.php"; ?>