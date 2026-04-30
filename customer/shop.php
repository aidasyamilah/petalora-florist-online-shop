<?php
$pageTitle = 'Shop';
require_once '../../includes/header.php';

$db = getDB();

// Get filter parameters
$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$searchQuery = isset($_GET['q']) ? sanitizeInput($_GET['q']) : '';
$sort = isset($_GET['sort']) ? sanitizeInput($_GET['sort']) : 'newest';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Build query
$whereConditions = ["p.availability_status = 'available'"];
$params = [];

if ($categoryId > 0) {
    $whereConditions[] = "p.category_id = ?";
    $params[] = $categoryId;
}

if (!empty($searchQuery)) {
    $whereConditions[] = "(p.product_name LIKE ? OR p.product_description LIKE ? OR p.occasion_type LIKE ?)";
    $searchParam = "%$searchQuery%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

$whereClause = implode(' AND ', $whereConditions);

// Sort options
$orderBy = match($sort) {
    'price_low' => 'p.product_price ASC',
    'price_high' => 'p.product_price DESC',
    'name' => 'p.product_name ASC',
    'rating' => 'p.rating_avg DESC',
    default => 'p.created_at DESC'
};

// Get total count
$countSql = "SELECT COUNT(*) FROM products p WHERE $whereClause";
$stmt = $db->prepare($countSql);
$stmt->execute($params);
$totalProducts = $stmt->fetchColumn();

// Pagination
$itemsPerPage = 12;
$pagination = getPagination($totalProducts, $itemsPerPage, $page);

// Get products
$sql = "SELECT p.*, c.category_name FROM products p 
        JOIN categories c ON p.category_id = c.category_id 
        WHERE $whereClause 
        ORDER BY $orderBy 
        LIMIT ? OFFSET ?";
$stmt = $db->prepare($sql);
$stmt->execute(array_merge($params, [$itemsPerPage, $pagination['offset']]));
$products = $stmt->fetchAll();

// Get categories for filter
$stmt = $db->query("SELECT * FROM categories WHERE status = 'active' ORDER BY display_order");
$categories = $stmt->fetchAll();

// Get current category name
$currentCategory = null;
if ($categoryId > 0) {
    foreach ($categories as $cat) {
        if ($cat['category_id'] == $categoryId) {
            $currentCategory = $cat;
            break;
        }
    }
}
?>

<div class="container py-5">
    <div class="row">
        <!-- Sidebar Filters -->
        <div class="col-lg-3 mb-4">
            <div style="background:white; border-radius:12px; padding:25px; box-shadow:0 4px 6px rgba(0,0,0,0.1);">
                <h5 style="font-family:'Playfair Display',serif; margin-bottom:20px;">Categories</h5>
                <div class="list-group list-group-flush">
                    <a href="shop.php" class="list-group-item list-group-item-action <?php echo $categoryId === 0 ? 'active' : ''; ?>" style="<?php echo $categoryId === 0 ? 'background:var(--primary); border-color:var(--primary);' : ''; ?>">
                        All Products
                    </a>
                    <?php foreach ($categories as $cat): ?>
                    <a href="shop.php?category=<?php echo $cat['category_id']; ?>" class="list-group-item list-group-item-action <?php echo $categoryId == $cat['category_id'] ? 'active' : ''; ?>" style="<?php echo $categoryId == $cat['category_id'] ? 'background:var(--primary); border-color:var(--primary);' : ''; ?>">
                        <?php echo htmlspecialchars($cat['category_name']); ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- Products Grid -->
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 style="font-family:'Playfair Display',serif; color:var(--primary-dark);">
                        <?php echo $currentCategory ? htmlspecialchars($currentCategory['category_name']) : 'All Products'; ?>
                    </h3>
                    <p class="text-muted mb-0"><?php echo $totalProducts; ?> product(s) found</p>
                </div>
                <div>
                    <select class="form-select" onchange="window.location.href=this.value" style="width:auto;">
                        <option value="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'newest'])); ?>" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                        <option value="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'price_low'])); ?>" <?php echo $sort == 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'price_high'])); ?>" <?php echo $sort == 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                        <option value="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'name'])); ?>" <?php echo $sort == 'name' ? 'selected' : ''; ?>>Name A-Z</option>
                        <option value="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'rating'])); ?>" <?php echo $sort == 'rating' ? 'selected' : ''; ?>>Highest Rated</option>
                    </select>
                </div>
            </div>
            
            <?php if (empty($products)): ?>
                <div class="text-center py-5">
                    <div style="font-size:5rem;">😕</div>
                    <h4>No products found</h4>
                    <p class="text-muted">Try adjusting your search or category filter.</p>
                    <a href="shop.php" class="btn-primary-custom">View All Products</a>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($products as $product): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="product-card">
                            <?php if ($product['featured']): ?>
                            <span class="product-badge">Featured</span>
                            <?php endif; ?>
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
                                <div class="product-rating">
                                    <?php for($i=1; $i<=5; $i++): ?>
                                        <i class="fas fa-star" style="color:<?php echo $i <= $product['rating_avg'] ? '#ff9800' : '#e9ecef'; ?>"></i>
                                    <?php endfor; ?>
                                    <small class="text-muted">(<?php echo $product['rating_count']; ?>)</small>
                                </div>
                                <button class="btn-add-cart" onclick="addToCart(<?php echo $product['product_id']; ?>, 1)">
                                    <i class="fas fa-shopping-bag me-2"></i>Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($pagination['total_pages'] > 1): ?>
                <nav class="mt-5">
                    <ul class="pagination justify-content-center">
                        <?php if ($pagination['has_previous']): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $pagination['previous_page']])); ?>">Previous</a>
                        </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                        <li class="page-item <?php echo $i == $pagination['current_page'] ? 'active' : ''; ?>">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" style="<?php echo $i == $pagination['current_page'] ? 'background:var(--primary); border-color:var(--primary);' : ''; ?>"><?php echo $i; ?></a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if ($pagination['has_next']): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $pagination['next_page']])); ?>">Next</a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
