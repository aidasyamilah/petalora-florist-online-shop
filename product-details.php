<?php
session_start();
include "php/db.php";

$title = "Product Details - Petalora";
include "header.php";

/* ==========================
   GET PRODUCT ID
========================== */
$product_id = $_GET['product_id'] ?? 0;

/* ==========================
   GET PRODUCT DATA
========================== */
$stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

/* ==========================
   NOT FOUND CHECK
========================== */
if(!$product){
    echo "<div style='text-align:center;margin-top:100px;font-size:18px;'>Product not found</div>";
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
    max-width:1200px;
    margin:70px auto;
    padding:0 40px;
}

.product-box{
    background:white;
    border-radius:24px;
    box-shadow:0 10px 30px rgba(0,0,0,.08);
    padding:50px;
    display:flex;
    gap:60px;
}

/* LEFT */
.gallery{ width:50%; }

.main-image{
    width:100%;
    border-radius:18px;
}

.thumbs{
    display:flex;
    gap:15px;
    margin-top:20px;
}

.thumbs img{
    width:100px;
    height:100px;
    object-fit:cover;
    border-radius:12px;
    cursor:pointer;
}

/* RIGHT */
.info{ width:50%; }

.info h1{
    font-size:30px;
    font-family:'Playfair Display',serif;
    margin-bottom:18px;
}

.price{
    font-size:26px;
    color:#e89cae;
    font-weight:600;
    margin-bottom:10px;
}

.status{
    font-size:14px;
    margin-bottom:10px;
    color:#666;
}

.desc{
    color:#666;
    margin-bottom:20px;
    line-height:1.6;
}

label{
    font-weight:600;
}

textarea{
    width:100%;
    height:100px;
    padding:15px;
    border-radius:12px;
    border:1px solid #ddd;
    margin-bottom:20px;
    resize:none;
}

/* QTY */
.qty-box{
    display:flex;
    align-items:center;
    gap:15px;
    margin-bottom:25px;
}

.qty-btn{
    width:40px;
    height:40px;
    border:none;
    border-radius:50%;
    background:#e89cae;
    color:white;
    font-size:20px;
    cursor:pointer;
}

#qty{
    font-size:20px;
    font-weight:600;
}

/* BUTTON */
.add-cart{
    width:100%;
    padding:14px;
    background:#e89cae;
    color:white;
    border:none;
    border-radius:12px;
    font-size:16px;
    cursor:pointer;
}

.add-cart:hover{
    background:#d97f94;
}

/* BENEFITS */
.benefits{
    margin-top:25px;
    padding:20px;
    background:#fff6f8;
    border-radius:16px;
}
</style>

<div class="wrapper">
<div class="product-box">

<!-- LEFT -->
<div class="gallery">

    <img id="mainImage"
         src="<?php echo $product['product_image'] ?: 'image/default.png'; ?>"
         class="main-image">

</div>

<!-- RIGHT -->
<div class="info">

    <h1><?php echo $product['product_name']; ?></h1>

    <div class="price">
        RM <?php echo number_format($product['product_price'], 2); ?>
    </div>

    <div class="status">
        Status: <?php echo ucfirst($product['availability_status']); ?>
    </div>

    <div class="status">
        Stock: <?php echo $product['stock_quantity']; ?> available
    </div>

    <p class="desc">
        <?php echo nl2br($product['product_description']); ?>
    </p>

    <label>Personal Message</label>
    <textarea placeholder="Write your gift message..."></textarea>

    <label>Quantity</label>
    <div class="qty-box">
        <button class="qty-btn" onclick="decreaseQty()">-</button>
        <span id="qty">1</span>
        <button class="qty-btn" onclick="increaseQty()">+</button>
    </div>

    <button class="add-cart" onclick="addToCart()">
        ADD TO CART
    </button>

    <div class="benefits">
        <h3>Member Benefits</h3>
        <p>🚚 Free Delivery Above RM300</p>
        <p>🎁 Complimentary Gift Wrapping</p>
    </div>

</div>

</div>
</div>

<script>

let quantity = 1;

function increaseQty(){
    quantity++;
    document.getElementById("qty").innerText = quantity;
}

function decreaseQty(){
    if(quantity > 1){
        quantity--;
        document.getElementById("qty").innerText = quantity;
    }
}

function addToCart(){
    alert("Added to cart!");
}

</script>

<?php include "footer.php"; ?>
