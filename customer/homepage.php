<?php
session_start();

$isLoggedIn = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Petalora Homepage</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&family=Playfair+Display:wght@500&display=swap" rel="stylesheet">

<style>
/* ================= YOUR ORIGINAL CSS (UNCHANGED) ================= */

*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:'Poppins',sans-serif;
}

body{
background:#f9f5f6;
}

/* NAVBAR */
.navbar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:18px 80px;
    background:#fff;
    position:relative;
    z-index:9999; 
}

.logo{
color:#e89cae;
font-size:28px;
font-family:'Playfair Display',serif;
}

.nav-links a{
margin:0 20px;
text-decoration:none;
color:#333;
position:relative;
transition:.3s;
}

.nav-links a:hover{
color:#e89cae;
transform:translateY(-2px);
}

.nav-links a::after{
content:"";
position:absolute;
left:0;
bottom:-3px;
width:0;
height:2px;
background:#e89cae;
transition:.3s;
}

.nav-links a:hover::after{
width:100%;
}

/* HERO */
.hero{
position:relative;
height:500px;
overflow:hidden;
}

.slides{
position:absolute;
top:0;
left:0;
width:100%;
height:100%;
}

.slide{
position:absolute;
width:100%;
height:100%;
object-fit:cover;
opacity:0;
transition:1s ease;
}

.slide.active{
opacity:1;
}

.hero::after{
content:"";
position:absolute;
top:0;
left:0;
width:100%;
height:100%;
background:rgba(0,0,0,.35);
z-index:1;
}

.hero-content{
position:absolute;
top:50%;
left:50%;
transform:translate(-50%,-50%);
z-index:2;
text-align:center;
color:white;
}

.hero-content h1{
font-size:60px;
}

.hero-content p{
font-size:22px;
margin-top:15px;
}

.hero-content button{
margin-top:25px;
padding:14px 30px;
border:none;
background:#e89cae;
color:white;
border-radius:8px;
cursor:pointer;
transition:0.3s;
}

.hero-content button:hover{
background:#d97f94;
}

.prev,.next{
position:absolute;
top:50%;
transform:translateY(-50%);
z-index:3;
font-size:45px;
color:white;
cursor:pointer;
padding:18px;
}

.prev{left:25px;}
.next{right:25px;}

/* SECTION */
.section{
padding:80px 60px;
text-align:center;
}

.section h2{
font-family:'Playfair Display',serif;
font-size:42px;
margin-bottom:50px;
}

/* CATEGORY */
.categories{
display:flex;
justify-content:center;
gap:30px;
flex-wrap:wrap;
}

.cat-card{
width:160px;
height:200px;
background:white;
border-radius:14px;
box-shadow:0 5px 20px rgba(0,0,0,.08);
display:flex;
flex-direction:column;
justify-content:space-between;
align-items:center;
padding:12px;
cursor:pointer;
transition:0.3s;
}

.cat-card img{
width:100%;
height:130px;
object-fit:cover;
border-radius:10px;
}

.cat-card p{
margin-top:10px;
font-weight:500;
}

.cat-card:hover{
transform:translateY(-8px);
box-shadow:0 10px 25px rgba(0,0,0,0.15);
}

/* PRODUCTS */
.products{
display:flex;
justify-content:center;
gap:30px;
flex-wrap:wrap;
}

.product{
width:240px;
height:360px;
background:white;
border-radius:14px;
box-shadow:0 5px 20px rgba(0,0,0,.08);
padding:15px;
display:flex;
flex-direction:column;
justify-content:space-between;
align-items:center;
text-align:center;
cursor:pointer;
transition:0.3s;
}

.product img{
width:100%;
height:180px;
object-fit:cover;
border-radius:10px;
}

.product p:nth-child(2){
font-weight:600;
margin-top:10px;
}

.product p:nth-child(3){
color:#e89cae;
font-weight:600;
margin-top:5px;
}

.product button{
width:100%;
padding:10px;
border:none;
background:#e89cae;
color:white;
border-radius:8px;
margin-top:auto;
cursor:pointer;
transition:0.3s;
}

.product button:hover{
background:#d97f94;
}

.product:hover{
transform:translateY(-10px);
box-shadow:0 12px 30px rgba(0,0,0,0.15);
}

/* WHY */
.why{
display:flex;
justify-content:center;
gap:40px;
margin-top:20px;
flex-wrap:wrap;
}

.why div{
background:white;
padding:20px 30px;
border-radius:12px;
box-shadow:0 5px 15px rgba(0,0,0,0.08);
transition:0.3s;
}

.why div:hover{
transform:translateY(-5px);
}

/* FOOTER */
.footer{
background:#333;
color:white;
padding:45px 80px;
display:flex;
justify-content:space-between;
flex-wrap:wrap;
gap:20px;
}

.bottom{
background:#222;
color:#ccc;
text-align:center;
padding:12px;
}

/* TOAST */
.toast{
position:fixed;
top:20px;
right:20px;
background:#e89cae;
color:white;
padding:12px 18px;
border-radius:10px;
z-index:9999;
}

/* RESPONSIVE (bonus🔥) */
@media(max-width:768px){
.navbar{
    padding:15px 20px;
}

.section{
    padding:60px 20px;
}

.hero-content h1{
    font-size:36px;
}

.products, .categories{
    flex-direction:column;
    align-items:center;
}
}
</style>
</head>

<body>

<!-- TOAST -->
<?php if(isset($_GET['login']) && $_GET['login']=="success"): ?>
<div class="toast">
    Welcome back, <?php echo $_SESSION['full_name'] ?? 'User'; ?> 🌸
</div>
<?php endif; ?>

<!-- NAVBAR -->
<div class="navbar">
    <div class="logo">Petalora</div>

    <div class="nav-links">
        <a href="homepage.php">Home</a>
        <a href="category.php">Category</a>
        <a href="cart.php">Cart</a>
        <a href="about.php">About</a>

        <?php if($isLoggedIn): ?>
            <a href="profile.php">Profile</a>
            <span style="color:#e89cae;font-weight:600;">
                👤 <?php echo $_SESSION['full_name'] ?? 'User'; ?>
            </span>
            <a href="php/logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
        <?php endif; ?>
    </div>
</div>

<!-- HERO -->
<div class="hero">
    <div class="slides">
        <img src="image/background.jpg" class="slide active">
        <img src="image/background2.png" class="slide">
        <img src="image/background3.png" class="slide">
    </div>

    <div class="hero-content">
        <h1>Send Love Through Flowers 🌸</h1>
        <p>Make every moment special</p>
        <button onclick="location.href='category.php'">Shop by Occasion</button>
    </div>

    <span class="prev" onclick="changeSlide(-1)">❮</span>
    <span class="next" onclick="changeSlide(1)">❯</span>
</div>

<!-- CATEGORY -->
<div class="section">
<h2>Shop by Occasion</h2>

<div class="categories">

<div class="cat-card" onclick="location.href='category.php'">
<img src="image/anniversary/33StalksSoapPinkRoses.png">
<p>Anniversary</p>
</div>

<div class="cat-card" onclick="location.href='category.php'">
<img src="image/wedding/Fresh Mix Roses & CarnationFlower Bridal Hand Bouquet.png">
<p>Wedding</p>
</div>

<div class="cat-card" onclick="location.href='category.php'">
<img src="image/graduation/Soap Roses Mix Sunflower Bouquet.png">
<p>Graduation</p>
</div>

<div class="cat-card" onclick="location.href='category.php'">
<img src="image/mother/Fresh Mix Carnation & Matthiola.png">
<p>Mother's Day</p>
</div>

</div>
</div>

<!-- PRODUCTS -->
<div class="section">
<h2>Best Sellers</h2>

<div class="products">

<div class="product" onclick="location.href='product-details.php?id=1'">
<img src="image/anniversary/HelloKittySoapPinkRoses.png">
<p>Hello Kitty Soap Pink Roses</p>
<p>RM89</p>
<button>Add to Cart</button>
</div>

<div class="product" onclick="location.href='product-details.php?id=2'">
<img src="image/anniversary/Fresh Mix Pink Tulip.png">
<p>Fresh Pink Tulip</p>
<p>RM99</p>
<button>Add to Cart</button>
</div>

<div class="product" onclick="location.href='product-details.php?id=3'">
<img src="image/graduation/Fresh Baby Breath Bouqurt.png">
<p>Baby Breath Bouquet</p>
<p>RM79</p>
<button>Add to Cart</button>
</div>

</div>
</div>

<!-- WHY -->
<div class="section">
<h2>Why Choose Us</h2>
<div class="why">
<div>🌸 Fresh Flowers</div>
<div>🚚 Same Day Delivery</div>
<div>🎨 Custom Design</div>
</div>
</div>

<!-- FOOTER -->
<div class="footer">
    <div>
        <p>Customer Hotline</p>
        <p>info@florist.com</p>
    </div>
    <div>
        <p>Our Story</p>
        <p>Flower Source</p>
    </div>
    <div>
        <p>Return Policy</p>
        <p>FAQ</p>
    </div>
</div>

<div class="bottom">
    © Petalora
</div>

</body>

<script>
let index = 0;
const slides = document.querySelectorAll(".slide");

function showSlide(i){
    slides.forEach(s=>s.classList.remove("active"));
    slides[i].classList.add("active");
}

function changeSlide(step){
    index += step;
    if(index >= slides.length) index = 0;
    if(index < 0) index = slides.length - 1;
    showSlide(index);
}

setInterval(()=>{
    index++;
    if(index >= slides.length) index = 0;
    showSlide(index);
},4000);
</script>

</html>