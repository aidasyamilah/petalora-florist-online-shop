<?php
include "db.php";

$type = $_GET['type'] ?? '';
$value = $_GET['value'] ?? '';

if($type == "email"){
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
}
else if($type == "phone"){
    $stmt = $conn->prepare("SELECT id FROM users WHERE phone_number = ?");
}
else{
    exit();
}

$stmt->bind_param("s", $value);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows > 0){
    echo "exist";
} else {
    echo "ok";
}