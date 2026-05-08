<?php
session_start();
include "php/db.php";

$title = "Product Details - Petalora";
include "header.php";

/* =========================
   VALIDATE ID
========================= */
$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;

if($product_id <= 0){
    die("Invalid product");
}

/* =========================
   GET PRODUCT
========================= */
$stmt = $conn->prepare("SELECT * FROM products WHERE product_id=?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

/* =========================
   NOT FOUND
========================= */
if(!$product){
    echo "<div style='text-align:center;margin-top:100px;'>Product not found 🌸</div>";
    exit();
}

/* =========================
   OUT OF STOCK CHECK
========================= */
if($product['stock_quantity'] <= 0){
    echo "<div style='text-align:center;margin-top:100px;'>Out of Stock ❌</div>";
    exit();
}
?>

<style>
body{
    background:#f9f5f6;
    font-family:'Poppins',sans-serif;
    color:#333;
}

.wrapper{
    max-width:1100px;
    margin:60px auto;
    padding:0 20px;
}

.box{
    background:white;
    border-radius:20px;
    box-shadow:0 10px 30px rgba(0,0,0,0.08);
    display:flex;
    gap:40px;
    padding:40px;
}

/* LEFT */
.left{
    width:50%;
}

.left img{
    width:100%;
    border-radius:16px;
}

/* RIGHT */
.right{
    width:50%;
}

.right h1{
    font-family:'Playfair Display',serif;
    margin-bottom:10px;
}

.price{
    color:#e89cae;
    font-size:24px;
    font-weight:600;
    margin-bottom:10px;
}

.desc{
    color:#666;
    margin-bottom:20px;
    line-height:1.6;
}

/* QTY */
.qty{
    display:flex;
    align-items:center;
    gap:10px;
    margin:20px 0;
}

.qty button{
    width:35px;
    height:35px;
    border:none;
    border-radius:50%;
    background:#e89cae;
    color:white;
    font-size:18px;
    cursor:pointer;
}

/* BUTTON */
.cart-btn{
    width:100%;
    padding:12px;
    background:#e89cae;
    color:white;
    border:none;
    border-radius:10px;
    cursor:pointer;
}

.cart-btn:hover{
    background:#d97f94;
}

.badge{
    display:inline-block;
    padding:4px 10px;
    background:#eee;
    border-radius:20px;
    font-size:12px;
    margin-bottom:10px;
}
</style>

<div class="wrapper">
<div class="box">

<!-- LEFT -->
<div class="left">
    <img src="<?= $product['product_image'] ?: 'image/default.png' ?>">
</div>

<!-- RIGHT -->
<div class="right">

    <div class="badge">
        Stock: <?= $product['stock_quantity'] ?>
    </div>

    <h1><?= $product['product_name'] ?></h1>

    <div class="price">
        RM <?= number_format($product['product_price'],2) ?>
    </div>

    <p class="desc">
        <?= nl2br($product['product_description']) ?>
    </p>

    <label>Quantity</label>
    <div class="qty">
        <button onclick="minus()">-</button>
        <span id="qty">1</span>
        <button onclick="plus()">+</button>
    </div>

    <button class="cart-btn" onclick="addToCart()">
        ADD TO CART
    </button>

</div>

</div>
</div>

<script>

let qty = 1;

function plus(){
    qty++;
    document.getElementById("qty").innerText = qty;
}

function minus(){
    if(qty > 1){
        qty--;
        document.getElementById("qty").innerText = qty;
    }
}

function addToCart(){
    window.location.href =
    "cart_add.php?product_id=<?= $product['product_id'] ?>&qty=" + qty;
}

</script>

<?php include "footer.php"; ?>
