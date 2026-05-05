<?php
session_start();
include "db.php";

if(!isset($_SESSION['user_id'])){
    echo "not_login";
    exit();
}

$id = $_SESSION['user_id'];

if(!isset($_FILES['profile_image']) || $_FILES['profile_image']['error'] !== 0){
    echo "upload_error";
    exit();
}

$file = $_FILES['profile_image'];

/* =========================
   SAFE IMAGE VALIDATION
========================= */

$check = getimagesize($file['tmp_name']);
if($check === false){
    echo "invalid_image";
    exit();
}

/* extension check */
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowedExt = ['jpg','jpeg','png'];

if(!in_array($ext, $allowedExt)){
    echo "type_error";
    exit();
}

/* size limit */
if($file['size'] > 2 * 1024 * 1024){
    echo "size_error";
    exit();
}

/* =========================
   RENAME FILE
========================= */

$fileName = "user_" . $id . "_" . time() . "." . $ext;

$tmp = $file['tmp_name'];
$folder = "../uploads/" . $fileName;

/* =========================
   MOVE FILE
========================= */

if(move_uploaded_file($tmp, $folder)){

    /* GET OLD IMAGE */
    $get = $conn->prepare("SELECT profile_image FROM users WHERE user_id=?");
    $get->bind_param("i", $id);
    $get->execute();
    $old = $get->get_result()->fetch_assoc();

    if(!empty($old['profile_image']) && file_exists("../uploads/".$old['profile_image'])){
        unlink("../uploads/".$old['profile_image']);
    }

    /* UPDATE DB */
    $stmt = $conn->prepare("UPDATE users SET profile_image=? WHERE user_id=?");
    $stmt->bind_param("si", $fileName, $id);

    if($stmt->execute()){
        echo "success";
    }else{
        echo "db_error";
    }

}else{
    echo "move_error";
}
?>