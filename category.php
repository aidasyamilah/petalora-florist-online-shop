<?php
$title = "Categories - Petalora";
include "header.php";
include "php/db.php";

/* =========================
   FILTER
========================= */
$filter = $_GET['filter'] ?? 'all';
?>

<style>

/* =========================
   GLOBAL
========================= */
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Poppins',sans-serif;
}

body{
    background:#f9f5f6;
}

/* =========================
   BANNER
========================= */
.banner{
    height:280px;
    background:url('image/background2.png') center/cover;
    display:flex;
    justify-content:center;
    align-items:center;
    color:white;
    font-size:48px;
    font-family:'Playfair Display',serif;
    position:relative;
}

.banner::after{
    content:"";
    position:absolute;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,0.35);
}

.banner h1{
    position:relative;
    z-index:2;
}

/* =========================
   SEARCH + FILTER BAR
========================= */
.top-bar{
    display:flex;
    flex-direction:column;
    align-items:center;
    gap:15px;
    margin-top:20px;
}

.search-box input{
    padding:10px 15px;
    width:300px;
    border-radius:20px;
    border:1px solid #ddd;
    outline:none;
}

.filter-bar{
    display:flex;
    justify-content:center;
    gap:12px;
    flex-wrap:wrap;
}

.filter-btn{
    padding:8px 18px;
    border-radius:25px;
    background:white;
    border:1px solid #e89cae;
    color:#e89cae;
    text-decoration:none;
    font-size:14px;
    transition:0.3s;
}

.filter-btn:hover{
    background:#e89cae;
    color:white;
}

.filter-btn.active{
    background:#e89cae;
    color:white;
    color:white;
}

/* =========================
   GRID
========================= */
.container{
    padding:40px 80px;
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(250px,1fr));
    gap:25px;
}

/* =========================
   CARD
========================= */
.card-link{
    text-decoration:none;
    color:inherit;
}

.card{
    background:white;
    border-radius:16px;
    overflow:hidden;
    box-shadow:0 5px 20px rgba(0,0,0,.08);
    transition:0.3s;
    cursor:pointer;
}

.card:hover{
    transform:translateY(-6px);
}

.card img{
    width:100%;
    height:200px;
    object-fit:cover;
    transition:0.3s;
}

.card:hover img{
    transform:scale(1.05);
}

/* OVERLAY */
.overlay{
    position:absolute;
    top:0;
    left:0;
    width:100%;
    height:200px;
    background:rgba(232,156,174,0.75);
    display:flex;
    justify-content:center;
    align-items:center;
    opacity:0;
    transition:0.3s;
}

.card:hover .overlay{
    opacity:1;
}

.overlay span{
    color:white;
    font-weight:600;
}

/* BODY */
.card-body{
    padding:15px;
}

.card-body h3{
    margin-bottom:6px;
    color:#333;
}

.card-body p{
    font-size:14px;
    color:#666;
}

/* EMPTY */
.empty{
    text-align:center;
    padding:60px;
    width:100%;
}

</style>

<!-- =========================
     BANNER
========================= -->
<div class="banner">
    <h1>Flower Categories</h1>
</div>

<!-- =========================
     SEARCH + FILTER
========================= -->
<div class="top-bar">

    <!-- SEARCH -->
    <div class="search-box">
        <input type="text" id="search" placeholder="Search categories..." onkeyup="searchCategory()">
    </div>

    <!-- FILTER -->
    <div class="filter-bar">

        <a href="category.php?filter=all"
           class="filter-btn <?= ($filter=='all')?'active':'' ?>">
           All
        </a>

        <a href="category.php?filter=Anniversary"
           class="filter-btn <?= ($filter=='Anniversary')?'active':'' ?>">
           Anniversary
        </a>

        <a href="category.php?filter=Graduation"
           class="filter-btn <?= ($filter=='Graduation')?'active':'' ?>">
           Graduation
        </a>

        <a href="category.php?filter=Sympathy"
           class="filter-btn <?= ($filter=="Sympathy")?'active':'' ?>">
           Sympathy
        </a>

        <a href="category.php?filter=Wedding"
           class="filter-btn <?= ($filter=='Wedding')?'active':'' ?>">
           Wedding
        </a>

        <a href="category.php?filter=Mother's Day"
           class="filter-btn <?= ($filter=="Mother's Day")?'active':'' ?>">
           Mother's Day
        </a>

    </div>

</div>

<!-- =========================
     GRID
========================= -->
<div class="container">

<?php

/* =========================
   QUERY
========================= */
if($filter == 'all'){
    $sql = "SELECT * FROM categories WHERE status='active' ORDER BY display_order ASC";
    $stmt = $conn->prepare($sql);
} else {
    $sql = "SELECT * FROM categories WHERE status='active' AND category_name=? ORDER BY display_order ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $filter);
}

$stmt->execute();
$result = $stmt->get_result();

/* =========================
   DISPLAY
========================= */
if($result && $result->num_rows > 0){

    while($row = $result->fetch_assoc()){

        $img = !empty($row['category_image']) 
            ? $row['category_image'] 
            : 'image/default.png';
?>

<a class="card-link"
   href="products.php?category_id=<?= $row['category_id'] ?>">

<div class="card">

    <div style="position:relative;">
        <img src="<?= $img ?>" alt="<?= $row['category_name'] ?>">

        <div class="overlay">
            <span>View Products →</span>
        </div>
    </div>

    <div class="card-body">
        <h3><?= $row['category_name'] ?></h3>
        <p><?= $row['category_description'] ?></p>
    </div>

</div>

</a>

<?php } ?>

<?php } else { ?>

<div class="empty">
    <h2>No categories found 🌸</h2>
    <p>Try selecting another filter</p>
</div>

<?php } ?>

</div>

<script>

/* =========================
   SEARCH FUNCTION
========================= */
function searchCategory(){
    let input = document.getElementById("search").value.toLowerCase();
    let cards = document.querySelectorAll(".card");

    cards.forEach(card => {
        let title = card.querySelector("h3").innerText.toLowerCase();

        if(title.includes(input)){
            card.style.display = "";
        } else {
            card.style.display = "none";
        }
    });
}

</script>

<?php include "footer.php"; ?>
