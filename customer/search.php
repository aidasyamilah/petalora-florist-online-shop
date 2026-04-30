<?php
$pageTitle = 'Search Results';
require_once '../../includes/header.php';

$db = getDB();
$query = isset($_GET['q']) ? sanitizeInput($_GET['q']) : '';

if (empty($query)) {
    redirectWithMessage('shop.php', 'info', 'Please enter a search term.');
}

$searchParam = "%$query%";
$stmt = $db->prepare("SELECT p.*, c.category_name FROM products p JOIN categories c ON p.category_id = c.category_id WHERE (p.product_name LIKE ? OR p.product_description LIKE ? OR p.occasion_type LIKE ?) AND p.availability_status = 'available'");
$stmt->execute([$searchParam, $searchParam, $searchParam]);
$products = $stmt->fetchAll();
?>

<div class="container py-5">
    <h2 style="font-family:'Playfair Display',serif; color:var(--primary-dark); margin-bottom:10px;">
        <i class="fas fa-search me-2"></i>Search Results
    </h2>
    <p class="text-muted mb-4"><?php echo count($products); ?> result(s) for "<?php echo htmlspecialchars($query); ?>"</p>
    
    <?php if (empty($products)): ?>
        <div class="text-center py-5">
            <div style="font-size:5rem;">🔍</div>
            <h4>No products found</h4>
            <p class="text-muted">Try different keywords or browse our categories.</p>
            <a href="shop.php" class="btn-primary-custom">Browse All Products</a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($products as $product): ?>
            <div class="col-lg-3 col-md-6">
                <div class="product-card">
                    <button class="product-wishlist" onclick="toggleWishlist(<?php echo $product['product_id']; ?>, this)">
                        <i class="far fa-heart"></i>
                    </button>
                    
                    <!-- PRODUCT IMAGE - UPDATED -->
                    <a href="product.php?id=<?php echo $product['product_id']; ?>">
                        <?php if (!empty($product['product_image']) && file_exists('../../' . $product['product_image'])): ?>
                            <img src="../../<?php echo $product['product_image']; ?>" 
                                 alt="<?php echo htmlspecialchars($product['product_name']); ?>" 
                                 style="width:100%; height:250px; object-fit:cover; border-radius:12px 12px 0 0;">
                        <?php else: ?>
                            <img src="../../assets/images/products/default-product.jpg" 
                                 alt="<?php echo htmlspecialchars($product['product_name']); ?>" 
                                 style="width:100%; height:250px; object-fit:cover; border-radius:12px 12px 0 0;">
                        <?php endif; ?>
                    </a>
                    
                    <div class="product-body">
                        <div class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></div>
                        <h5 class="product-title"><?php echo htmlspecialchars($product['product_name']); ?></h5>
                        <div class="product-price"><?php echo formatPrice($product['product_price']); ?></div>
                        <button class="btn-add-cart" onclick="addToCart(<?php echo $product['product_id']; ?>, 1)">
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
