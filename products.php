<?php
session_start();
include "header.php";
include "php/db.php";

// get category id
$category_id = isset($_GET['category_id']) ? $_GET['category_id'] : 0;

// get category name
$catName = "All Products";

if($category_id > 0){
    $stmt = $conn->prepare("SELECT category_name FROM categories WHERE category_id = ?");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if($row = $res->fetch_assoc()){
        $catName = $row['category_name'];
    }
}
?>

<style>
body{
    background:#f9f5f6;
    font-family:'Poppins',sans-serif;
}

.banner{
    height:250px;
    background:url('image/background2.png') center/cover;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:40px;
    color:white;
    font-family:'Playfair Display',serif;
}

.container{
    padding:40px 80px;
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:25px;
}

.card{
    background:white;
    border-radius:16px;
    overflow:hidden;
    box-shadow:0 5px 20px rgba(0,0,0,0.08);
    cursor:pointer;
    transition:0.3s;
}

.card:hover{
    transform:translateY(-5px);
}

.card img{
    width:100%;
    height:200px;
    object-fit:cover;
}

.card-body{
    padding:15px;
}

.price{
    color:#e89cae;
    font-weight:600;
    margin-top:5px;
}
</style>

<div class="banner">
    <?php echo $catName; ?>
</div>

<div class="container">

<?php

if($category_id > 0){
    $stmt = $conn->prepare("SELECT * FROM products WHERE category_id = ?");
    $stmt->bind_param("i", $category_id);
}else{
    $stmt = $conn->prepare("SELECT * FROM products");
}

$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows > 0){

    while($row = $result->fetch_assoc()){
?>

<div class="card"
onclick="location.href='product-details.php?product_id=<?php echo $row['product_id']; ?>'">

    <img src="<?php echo $row['product_image'] ?: 'image/default.png'; ?>">

    <div class="card-body">
        <h3><?php echo $row['product_name']; ?></h3>
        <div class="price">RM<?php echo $row['product_price']; ?></div>
    </div>

</div>

<?php
    }

}else{
    echo "<p>No products found</p>";
}
?>

</div>

<?php include "footer.php"; ?>