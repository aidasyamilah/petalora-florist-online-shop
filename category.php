<?php
$title = "Categories - Petalora";
include "header.php";
include "php/db.php";
?>

<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:'Poppins',sans-serif;
}

body{
background:#f9f5f6;
}

/* BANNER */
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

/* GRID */
.container{
padding:40px 80px;
display:grid;
grid-template-columns:repeat(auto-fit,minmax(250px,1fr));
gap:25px;
}

/* CARD */
.card{
background:white;
border-radius:16px;
overflow:hidden;
box-shadow:0 5px 20px rgba(0,0,0,.08);
transition:.3s;
cursor:pointer;
position:relative;
}

.card:hover{
transform:translateY(-6px);
}

.card:hover .overlay{
opacity:1;
}

/* IMAGE */
.card img{
width:100%;
height:200px;
object-fit:cover;
}

/* BODY */
.card-body{
padding:15px;
}

.card-body h3{
margin-bottom:8px;
color:#333;
}

.card-body p{
font-size:14px;
color:#666;
line-height:1.4;
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
color:white;
font-size:18px;
opacity:0;
transition:0.3s;
pointer-events:none;
}

.card-link{
text-decoration:none;
color:inherit;
}

</style>

<div class="banner">
    <h1>Flower Categories</h1>
</div>

<div class="container">

<?php

$stmt = $conn->prepare("SELECT * FROM categories WHERE status='active' ORDER BY display_order ASC");
$stmt->execute();
$result = $stmt->get_result();

if($result && $result->num_rows > 0){

    while($row = $result->fetch_assoc()){
?>

<a class="card-link" href="products.php?category_id=<?php echo $row['category_id']; ?>">

<div class="card">

    <img src="<?php echo !empty($row['category_image']) ? $row['category_image'] : 'image/default.png'; ?>">

    <div class="overlay">
        View Products →
    </div>

    <div class="card-body">
        <h3><?php echo $row['category_name']; ?></h3>
        <p><?php echo $row['category_description']; ?></p>
    </div>

</div>

</a>

<?php
    }

}else{
    echo "<p style='text-align:center;'>No categories found</p>";
}
?>

</div>

<?php include "footer.php"; ?>