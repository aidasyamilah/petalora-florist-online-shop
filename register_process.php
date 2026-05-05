<?php
include "db.php";

// ==========================
// GET DATA
// ==========================
$full_name = trim($_POST['full_name']);
$email = trim($_POST['email']);
$raw_phone = trim($_POST['phone_number']);
$address = trim($_POST['address']);
$area = $_POST['area'];
$state = $_POST['state'];
$district = $_POST['district'];
$postcode = trim($_POST['postcode']);
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];


// ==========================
// AREA DATA (SERVER SIDE VALIDATION)
// ==========================
$areaData = [
    "Melaka Tengah" => [
        "Klebang","Batu Berendam","Cheng","Kota Laksamana","Melaka Raya",
        "Ujong Pasir","Duyong","Telok Mas","Peringgit","Krubong"
    ],
    "Alor Gajah" => [
        "Masjid Tanah","Lendu","Durian Tunggal","Alor Gajah"
    ],
    "Jasin" => [
        "Merlimau","Bemban","Serkam","Sungai Rambai"
    ]
];


// ==========================
// VALIDATION
// ==========================

// name
if (strlen($full_name) < 3) {
    die("Full name too short");
}

// email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Invalid email");
}

if (substr($email, -10) !== "@gmail.com") {
    die("Only Gmail allowed");
}

// password match
if ($password !== $confirm_password) {
    die("Password not match");
}

// password rule
if (!preg_match("/^(?=.*[A-Z])(?=.*[0-9]).{8,}$/", $password)) {
    die("Weak password");
}

// phone format cleanup
$raw_phone = ltrim($raw_phone, "0");

if (substr($raw_phone, 0, 2) != "60") {
    $phone_number = "60" . $raw_phone;
} else {
    $phone_number = $raw_phone;
}

// postcode
if (!preg_match("/^[0-9]{5}$/", $postcode)) {
    die("Invalid postcode");
}

// state check
if ($state !== "Melaka") {
    die("Only Melaka allowed");
}

// area required
if (empty($area) || empty($district)) {
    die("Area required");
}

// 🔥 CHECK AREA MATCH DISTRICT (IMPORTANT FIX)
if (!isset($areaData[$district]) || !in_array($area, $areaData[$district])) {
    die("Area does not match district");
}


// ==========================
// CHECK DUPLICATE EMAIL
// ==========================
$stmt = $conn->prepare("SELECT user_id FROM users WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    die("Email already registered");
}
$stmt->close();


// ==========================
// CHECK PHONE
// ==========================
$stmt = $conn->prepare("SELECT user_id FROM users WHERE phone_number=?");
$stmt->bind_param("s", $phone_number);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    die("Phone already registered");
}
$stmt->close();


// ==========================
// HASH PASSWORD
// ==========================
$hashed = password_hash($password, PASSWORD_DEFAULT);


// ==========================
// INSERT
// ==========================
$stmt = $conn->prepare("
    INSERT INTO users 
    (full_name, email, phone_number, address, district, area, state, postcode, password)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "sssssssss",
    $full_name,
    $email,
    $phone_number,
    $address,
    $district,
    $area,
    $state,
    $postcode,
    $hashed
);

if ($stmt->execute()) {
    header("Location: ../register.php?success=1");
    exit();
} else {
    die("Insert failed");
}

$stmt->close();
$conn->close();
?>