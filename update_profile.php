<?php
session_start();
include "db.php";

if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit();
}

$id = $_SESSION['user_id'];

// ==========================
// GET DATA
// ==========================
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone_number'] ?? '');
$address = trim($_POST['address'] ?? '');
$area = trim($_POST['area'] ?? '');
$state = trim($_POST['state'] ?? '');
$postcode = trim($_POST['postcode'] ?? '');
$district = trim($_POST['district'] ?? '');

// ==========================
// VALIDATION (IMPORTANT)
// ==========================

// email
if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
    header("Location: ../profile.php?update=invalid_email");
    exit();
}

// phone
if(!preg_match("/^[0-9]{9,11}$/", $phone)){
    header("Location: ../profile.php?update=invalid_phone");
    exit();
}

// postcode
if(!preg_match("/^[0-9]{5}$/", $postcode)){
    header("Location: ../profile.php?update=invalid_postcode");
    exit();
}

// ==========================
// AREA VALIDATION (IMPORTANT FYP PART)
// ==========================
$areaData = [
    "Melaka Tengah" => ["Kota Laksamana","Melaka Raya","Klebang","Batu Berendam","Cheng","Peringgit"],
    "Alor Gajah" => ["Masjid Tanah","Lendu","Durian Tunggal"],
    "Jasin" => ["Merlimau","Bemban","Serkam"]
];

if(!isset($areaData[$district]) || !in_array($area, $areaData[$district])){
    header("Location: ../profile.php?update=invalid_area");
    exit();
}

// ==========================
// UPDATE QUERY
// ==========================
$stmt = $conn->prepare("
    UPDATE users SET 
        email = ?, 
        phone_number = ?, 
        address = ?, 
        area = ?, 
        state = ?, 
        postcode = ?,
        district = ?
    WHERE user_id = ?
");

$stmt->bind_param(
    "sssssssi",
    $email,
    $phone,
    $address,
    $area,
    $state,
    $postcode,
    $district,
    $id
);

// EXECUTE
if($stmt->execute()){
    header("Location: ../profile.php?update=success");
}else{
    header("Location: ../profile.php?update=fail");
}

$stmt->close();
$conn->close();
?>