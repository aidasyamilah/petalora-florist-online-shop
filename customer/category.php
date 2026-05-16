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

.product-container{
    padding:40px 80px;
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:25px;
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

    <!-- SEARCH (改成 GET) -->
    <form method="GET" class="search-box">

        <input type="hidden" name="filter" value="<?= $filter ?>">

        <input type="text"
               name="search"
               placeholder="Search categories..."
               value="<?= $_GET['search'] ?? '' ?>">

    </form>

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
           class="filter-btn <?= ($filter=='Sympathy')?'active':'' ?>">
           Sympathy
        </a>

        <a href="category.php?filter=Wedding"
           class="filter-btn <?= ($filter=='Wedding')?'active':'' ?>">
           Wedding
        </a>

        <a href="category.php?filter=Mother\'s Day"
           class="filter-btn <?= ($filter=='Mother\'s Day')?'active':'' ?>">
           Mother's Day
        </a>

    </div>

</div>

<?php
/* =========================
   PAGINATION + SEARCH + FILTER LOGIC
========================= */

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 9;
$offset = ($page - 1) * $limit;

$search = $_GET['search'] ?? '';

/* =========================
   QUERY (FILTER ONLY)
========================= */
if($filter == 'all'){
    $stmt = $conn->prepare("SELECT * FROM products ORDER BY product_id DESC");
} else {
    $stmt = $conn->prepare("
        SELECT * FROM products 
        WHERE category_id = (
            SELECT category_id 
            FROM categories 
            WHERE category_name = ?
        )
        ORDER BY product_id DESC
    ");
    $stmt->bind_param("s",$filter);
}

$stmt->execute();
$result = $stmt->get_result();

$allProducts = $result->fetch_all(MYSQLI_ASSOC);

/* =========================
   SEARCH FILTER (PHP)
========================= */
if($search != ''){
    $allProducts = array_filter($allProducts, function($row) use ($search){
        return stripos($row['product_name'], $search) !== false;
    });
}

/* =========================
   PAGINATION
========================= */
$total = count($allProducts);
$totalPages = ceil($total / $limit);

$products = array_slice($allProducts, $offset, $limit);
?>

<!-- =========================
     GRID
========================= -->
<div class="product-container">

<?php if(count($products) > 0){ ?>

    <?php foreach($products as $row){ ?>

        <?php
        $img = !empty($row['product_image'])
            ? $row['product_image']
            : 'image/default.png';
        ?>

        <div class="card">

            <img src="<?= $img ?>">

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

    <div class="empty">
        <h2>No products found 🌸</h2>
    </div>

<?php } ?>

</div>

<!-- =========================
     PAGINATION
========================= -->
<div style="text-align:center; margin:30px 0;">

<?php for($i = 1; $i <= $totalPages; $i++){ ?>

    <a href="category.php?filter=<?= $filter ?>&search=<?= urlencode($search) ?>&page=<?= $i ?>"
       style="
            padding:8px 14px;
            margin:3px;
            border-radius:6px;
            text-decoration:none;
            background:<?= ($page == $i) ? '#e89cae' : '#fff' ?>;
            color:<?= ($page == $i) ? '#fff' : '#e89cae' ?>;
            border:1px solid #e89cae;
       ">
       <?= $i ?>
    </a>

<?php } ?>

</div>

<?php include "footer.php"; ?>
