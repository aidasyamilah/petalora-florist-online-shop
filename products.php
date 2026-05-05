<?php
session_start();
include "header.php";
include "php/db.php";

/* =========================
   GET CATEGORY ID
========================= */
$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

/* =========================
   GET CATEGORY NAME
========================= */
$catName = "All Products";

if($category_id > 0){
    $stmt = $conn->prepare("SELECT category_name FROM categories WHERE category_id=?");
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

/* GRID */
.container{
    padding:40px 80px;
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:25px;
}

/* CARD */
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

.stock{
    font-size:12px;
    color:#777;
}
</style>

<div class="banner">
    <?= $catName ?>
</div>

<div class="container">

<?php
/* =========================
   GET PRODUCTS
========================= */
if($category_id > 0){
    $stmt = $conn->prepare("SELECT * FROM products WHERE category_id=? ORDER BY product_id DESC");
    $stmt->bind_param("i", $category_id);
} else {
    $stmt = $conn->prepare("SELECT * FROM products ORDER BY product_id DESC");
}

$stmt->execute();
$result = $stmt->get_result();

if($result && $result->num_rows > 0){

    while($row = $result->fetch_assoc()){

        $img = !empty($row['product_image']) ? $row['product_image'] : 'image/default.png';
?>

<div class="card"
onclick="location.href='product-details.php?product_id=<?= $row['product_id'] ?>'">

    <img src="<?= $img ?>" alt="<?= $row['product_name'] ?>">

    <div class="card-body">
        <h3><?= $row['product_name'] ?></h3>

        <div class="price">
            RM <?= number_format($row['product_price'],2) ?>
        </div>

        <div class="stock">
            Stock: <?= $row['stock_quantity'] ?>
        </div>
    </div>

</div>

<?php } ?>

<?php } else { ?>

    <div style="text-align:center;width:100%;">
        <h3>No products found 🌸</h3>
    </div>

<?php } ?>

</div>

<?php include "footer.php"; ?>
