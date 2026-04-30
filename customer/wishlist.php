<?php
$pageTitle = 'My Wishlist';
require_once '../../includes/header.php';

if (!isLoggedIn() || $_SESSION['role'] !== 'customer') {
    redirectWithMessage('login.php', 'danger', 'Please login to view your wishlist.');
}

$db = getDB();
$userId = $_SESSION['user_id'];

// FIX: Added p.product_image to the SELECT query
$stmt = $db->prepare("SELECT w.*, p.product_name, p.product_price, p.stock_quantity, p.availability_status, p.rating_avg, p.rating_count, p.product_image, c.category_name 
                      FROM wishlist w 
                      JOIN products p ON w.product_id = p.product_id 
                      JOIN categories c ON p.category_id = c.category_id 
                      WHERE w.user_id = ? 
                      ORDER BY w.created_at DESC");
$stmt->execute([$userId]);
$wishlistItems = $stmt->fetchAll();
?>

<div class="container py-5">
    <h2 style="font-family:'Playfair Display',serif; color:var(--primary-dark); margin-bottom:30px;">
        <i class="fas fa-heart me-2"></i>My Wishlist
    </h2>
    
    <?php if (empty($wishlistItems)): ?>
        <div class="text-center py-5">
            <div style="font-size:5rem;">💝</div>
            <h4>Your wishlist is empty</h4>
            <p class="text-muted">Save your favorite bouquets for later!</p>
            <a href="shop.php" class="btn-primary-custom">Browse Products</a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($wishlistItems as $item): ?>
            <div class="col-lg-3 col-md-6">
                <div class="product-card">
                    <button class="product-wishlist active" onclick="toggleWishlist(<?php echo $item['product_id']; ?>, this)">
                        <i class="fas fa-heart"></i>
                    </button>
                    
                    <!-- PRODUCT IMAGE - UPDATED -->
                    <a href="product.php?id=<?php echo $item['product_id']; ?>">
                        <?php if (!empty($item['product_image']) && file_exists('../../' . $item['product_image'])): ?>
                            <img src="../../<?php echo $item['product_image']; ?>" 
                                 alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                 style="width:100%; height:250px; object-fit:cover; border-radius:12px 12px 0 0;">
                        <?php else: ?>
                            <img src="../../assets/images/products/default-product.jpg" 
                                 alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                 style="width:100%; height:250px; object-fit:cover; border-radius:12px 12px 0 0;">
                        <?php endif; ?>
                    </a>
                    
                    <div class="product-body">
                        <div class="product-category"><?php echo htmlspecialchars($item['category_name']); ?></div>
                        <h5 class="product-title"><?php echo htmlspecialchars($item['product_name']); ?></h5>
                        <div class="product-price"><?php echo formatPrice($item['product_price']); ?></div>
                        <div class="product-rating">
                            <?php for($i=1; $i<=5; $i++): ?>
                                <i class="fas fa-star" style="color:<?php echo $i <= $item['rating_avg'] ? '#ff9800' : '#e9ecef'; ?>"></i>
                            <?php endfor; ?>
                            <small class="text-muted">(<?php echo $item['rating_count']; ?>)</small>
                        </div>
                        <button class="btn-add-cart" onclick="addToCart(<?php echo $item['product_id']; ?>, 1)">
                            <i class="fas fa-shopping-bag me-2"></i>Add to Cart
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../../includes/footer.php'; ?>
