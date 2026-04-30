<?php
$pageTitle = 'Order Details';
require_once '../../includes/header.php';

if (!isLoggedIn() || $_SESSION['role'] !== 'customer') {
    redirectWithMessage('login.php', 'danger', 'Please login to view order details.');
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirectWithMessage('orders.php', 'danger', 'Invalid order.');
}

$orderId = (int)$_GET['id'];
$userId = $_SESSION['user_id'];
$db = getDB();

// Get order details
$stmt = $db->prepare("SELECT o.*, ds.* FROM orders o LEFT JOIN delivery_schedule ds ON o.order_id = ds.order_id WHERE o.order_id = ? AND o.user_id = ?");
$stmt->execute([$orderId, $userId]);
$order = $stmt->fetch();

if (!$order) {
    redirectWithMessage('orders.php', 'danger', 'Order not found.');
}

// Get order items
$stmt = $db->prepare("SELECT oi.*, p.product_name FROM order_items oi JOIN products p ON oi.product_id = p.product_id WHERE oi.order_id = ?");
$stmt->execute([$orderId]);
$items = $stmt->fetchAll();

// Get payment
$stmt = $db->prepare("SELECT * FROM payments WHERE order_id = ?");
$stmt->execute([$orderId]);
$payment = $stmt->fetch();

$pageTitle = 'Order #' . $order['order_number'];
?>

<div class="container py-5">
    <div class="mb-4">
        <a href="orders.php" class="text-decoration-none" style="color:var(--primary);"><i class="fas fa-arrow-left me-2"></i>Back to Orders</a>
    </div>
    
    <div class="row">
        <div class="col-lg-8">
            <div style="background:white; border-radius:12px; padding:25px; box-shadow:0 4px 6px rgba(0,0,0,0.1); margin-bottom:20px;">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h3 style="font-family:'Playfair Display',serif; color:var(--primary-dark);">Order #<?php echo htmlspecialchars($order['order_number']); ?></h3>
                        <p class="text-muted mb-0">Placed on <?php echo date('d M Y, h:i A', strtotime($order['order_date'])); ?></p>
                    </div>
                    <span class="status-badge status-<?php echo $order['order_status']; ?>">
                        <?php echo ucwords(str_replace('_', ' ', $order['order_status'])); ?>
                    </span>
                </div>
                
                <!-- Progress Bar -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <?php 
                        $statuses = ['pending' => 1, 'paid' => 2, 'preparing' => 3, 'out_for_delivery' => 4, 'delivered' => 5];
                        $currentStep = $statuses[$order['order_status']] ?? 1;
                        $stepLabels = ['Order Placed', 'Payment Confirmed', 'Preparing', 'Out for Delivery', 'Delivered'];
                        ?>
                        <?php foreach ($stepLabels as $i => $label): ?>
                        <div class="text-center" style="flex:1;">
                            <div style="width:30px; height:30px; border-radius:50%; background:<?php echo $i < $currentStep ? 'var(--success)' : 'var(--gray-light)'; ?>; color:<?php echo $i < $currentStep ? 'white' : 'var(--gray)'; ?>; display:flex; align-items:center; justify-content:center; margin:0 auto 5px; font-size:0.8rem;">
                                <?php echo $i < $currentStep ? '✓' : ($i + 1); ?>
                            </div>
                            <small style="font-size:0.7rem; color:<?php echo $i < $currentStep ? 'var(--success)' : 'var(--gray)'; ?>;"><?php echo $label; ?></small>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="progress" style="height:5px;">
                        <div class="progress-bar bg-success" style="width:<?php echo (($currentStep - 1) / 4) * 100; ?>%"></div>
                    </div>
                </div>
                
                <h6 class="mb-3">Order Items</h6>
                <?php foreach ($items as $item): ?>
                <div class="order-item-row">
                    <div style="width:80px; height:80px; background: linear-gradient(135deg, #fce4ec 0%, #f8bbd9 100%); border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:2.5rem; flex-shrink:0;">
                        🌸
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-bold"><?php echo htmlspecialchars($item['product_name']); ?></div>
                        <small class="text-muted">Quantity: <?php echo $item['quantity']; ?></small>
                    </div>
                    <div class="text-end">
                        <div><?php echo formatPrice($item['unit_price']); ?> each</div>
                        <div class="fw-bold"><?php echo formatPrice($item['subtotal']); ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <?php if ($order['custom_card_message']): ?>
                <div class="mt-3 p-3" style="background:#fff3cd; border-radius:8px;">
                    <strong><i class="fas fa-envelope me-2"></i>Card Message:</strong>
                    <p class="mb-0 fst-italic">"<?php echo nl2br(htmlspecialchars($order['custom_card_message'])); ?>"</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Order Summary -->
            <div style="background:white; border-radius:12px; padding:25px; box-shadow:0 4px 6px rgba(0,0,0,0.1); margin-bottom:20px;">
                <h6 style="font-family:'Playfair Display',serif;">Order Summary</h6>
                <div class="d-flex justify-content-between mb-2"><span>Subtotal</span><span><?php echo formatPrice($order['total_amount']); ?></span></div>
                <div class="d-flex justify-content-between mb-2"><span>Delivery Fee</span><span><?php echo formatPrice($order['delivery_fee']); ?></span></div>
                <?php if ($order['discount_amount'] > 0): ?>
                <div class="d-flex justify-content-between mb-2" style="color:var(--success);"><span>Discount</span><span>-<?php echo formatPrice($order['discount_amount']); ?></span></div>
                <?php endif; ?>
                <hr>
                <div class="d-flex justify-content-between fw-bold" style="font-size:1.1rem; color:var(--primary-dark);">
                    <span>Total</span><span><?php echo formatPrice($order['final_amount']); ?></span>
                </div>
            </div>
            
            <!-- Delivery Info -->
            <div style="background:white; border-radius:12px; padding:25px; box-shadow:0 4px 6px rgba(0,0,0,0.1); margin-bottom:20px;">
                <h6 style="font-family:'Playfair Display',serif;"><i class="fas fa-truck me-2"></i>Delivery Information</h6>
                <p class="mb-1"><strong><?php echo htmlspecialchars($order['recipient_name']); ?></strong></p>
                <p class="mb-1 text-muted"><?php echo nl2br(htmlspecialchars($order['delivery_address'])); ?></p>
                <p class="mb-1 text-muted"><?php echo htmlspecialchars($order['postcode'] . ', ' . $order['city']); ?></p>
                <p class="mb-0 text-muted"><i class="fas fa-calendar me-1"></i><?php echo $order['delivery_date'] ? date('d M Y', strtotime($order['delivery_date'])) : 'TBD'; ?></p>
                <p class="mb-0 text-muted"><i class="fas fa-clock me-1"></i><?php echo htmlspecialchars($order['time_slot'] ?? 'TBD'); ?></p>
            </div>
            
            <!-- Payment Info -->
            <div style="background:white; border-radius:12px; padding:25px; box-shadow:0 4px 6px rgba(0,0,0,0.1);">
                <h6 style="font-family:'Playfair Display',serif;"><i class="fas fa-credit-card me-2"></i>Payment</h6>
                <p class="mb-1"><strong>Method:</strong> <?php echo ucwords(str_replace('_', ' ', $payment['payment_method'] ?? 'N/A')); ?></p>
                <p class="mb-1"><strong>Status:</strong> <span class="status-badge status-<?php echo $payment['payment_status'] ?? 'pending'; ?>"><?php echo ucwords($payment['payment_status'] ?? 'Pending'); ?></span></p>
                <?php if ($payment['card_last_four']): ?>
                <p class="mb-0"><strong>Card:</strong> **** **** **** <?php echo $payment['card_last_four']; ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
