<?php
$pageTitle = 'Shopping Cart';
require_once '../../includes/header.php';

if (!isLoggedIn() || $_SESSION['role'] !== 'customer') {
    redirectWithMessage('login.php', 'danger', 'Please login to view your cart.');
}

$db = getDB();
$userId = $_SESSION['user_id'];

// Get cart items - FIX: Added p.product_image to SELECT
$stmt = $db->prepare("SELECT c.*, p.product_name, p.product_price, p.stock_quantity, p.availability_status, p.product_image, cat.category_name 
                      FROM cart c 
                      JOIN products p ON c.product_id = p.product_id 
                      JOIN categories cat ON p.category_id = cat.category_id 
                      WHERE c.user_id = ?");
$stmt->execute([$userId]);
$cartItems = $stmt->fetchAll();

// Calculate totals
$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += $item['product_price'] * $item['quantity'];
}
$deliveryFee = $subtotal >= 100 ? 0 : 10;
$total = $subtotal + $deliveryFee;
?>

<div class="container py-5">
    <h2 style="font-family:'Playfair Display',serif; color:var(--primary-dark); margin-bottom:30px;">
        <i class="fas fa-shopping-bag me-2"></i>Shopping Cart
    </h2>
    
    <?php if (empty($cartItems)): ?>
        <div class="text-center py-5">
            <div style="font-size:5rem;">🛒</div>
            <h4>Your cart is empty</h4>
            <p class="text-muted">Browse our collection and add some beautiful flowers!</p>
            <a href="shop.php" class="btn-primary-custom">Continue Shopping</a>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-lg-8">
                <?php foreach ($cartItems as $item): ?>
                <div class="cart-item" data-cart-id="<?php echo $item['cart_id']; ?>" data-price="<?php echo $item['product_price']; ?>">
                    
                    <!-- PRODUCT IMAGE - UPDATED -->
                    <?php if (!empty($item['product_image']) && file_exists('../../' . $item['product_image'])): ?>
                        <img src="../../<?php echo $item['product_image']; ?>" 
                             alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                             style="width:100px; height:100px; object-fit:cover; border-radius:8px; flex-shrink:0;">
                    <?php else: ?>
                        <img src="../../assets/images/products/default-product.jpg" 
                             alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                             style="width:100px; height:100px; object-fit:cover; border-radius:8px; flex-shrink:0;">
                    <?php endif; ?>
                    
                    <div class="cart-item-details">
                        <div class="cart-item-title"><?php echo htmlspecialchars($item['product_name']); ?></div>
                        <div class="text-muted mb-1"><?php echo htmlspecialchars($item['category_name']); ?></div>
                        <div class="cart-item-price"><?php echo formatPrice($item['product_price']); ?></div>
                    </div>
                    <div class="quantity-control">
                        <button class="qty-minus" onclick="updateCartItem(<?php echo $item['cart_id']; ?>, Math.max(1, <?php echo $item['quantity']; ?> - 1))">-</button>
                        <input type="number" class="qty-input" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock_quantity']; ?>" readonly style="width:50px; text-align:center;">
                        <button class="qty-plus" onclick="updateCartItem(<?php echo $item['cart_id']; ?>, Math.min(<?php echo $item['stock_quantity']; ?>, <?php echo $item['quantity']; ?> + 1))">+</button>
                    </div>
                    <div class="cart-item-price item-subtotal" style="min-width:100px; text-align:right;">
                        <?php echo formatPrice($item['product_price'] * $item['quantity']); ?>
                    </div>
                    <button class="btn btn-link text-danger" onclick="removeCartItem(<?php echo $item['cart_id']; ?>)" style="text-decoration:none;">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
                <?php endforeach; ?>
                
                <div class="mt-4">
                    <a href="shop.php" class="btn-secondary-custom"><i class="fas fa-arrow-left me-2"></i>Continue Shopping</a>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="cart-summary">
                    <h5 style="font-family:'Playfair Display',serif; margin-bottom:20px;">Order Summary</h5>
                    
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span id="cart-subtotal"><?php echo formatPrice($subtotal); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Delivery Fee</span>
                        <span id="cart-delivery"><?php echo $deliveryFee === 0 ? 'FREE' : formatPrice($deliveryFee); ?></span>
                    </div>
                    <?php if ($deliveryFee > 0): ?>
                    <div class="text-muted mb-2" style="font-size:0.85rem;">
                        <i class="fas fa-info-circle me-1"></i>Add RM<?php echo number_format(100 - $subtotal, 2); ?> more for free delivery
                    </div>
                    <?php endif; ?>
                    <div class="summary-row total">
                        <span>Total</span>
                        <span id="cart-total"><?php echo formatPrice($total); ?></span>
                    </div>
                    
                    <a href="checkout.php" class="btn-primary-custom w-100 mt-3" style="text-align:center;">
                        <i class="fas fa-credit-card me-2"></i>Proceed to Checkout
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../../includes/footer.php'; ?>
