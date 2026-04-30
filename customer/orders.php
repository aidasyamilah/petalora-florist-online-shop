<?php
$pageTitle = 'My Orders';
require_once '../../includes/header.php';

if (!isLoggedIn() || $_SESSION['role'] !== 'customer') {
    redirectWithMessage('login.php', 'danger', 'Please login to view your orders.');
}

$db = getDB();
$userId = $_SESSION['user_id'];

$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$statusFilter = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';

$sql = "SELECT o.*, ds.delivery_date, ds.delivery_status FROM orders o LEFT JOIN delivery_schedule ds ON o.order_id = ds.order_id WHERE o.user_id = ?";
$params = [$userId];

if (!empty($search)) {
    $sql .= " AND (o.order_number LIKE ? OR o.order_id LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($statusFilter)) {
    // FIX: For 'delivered', check both order_status AND delivery_status
    if ($statusFilter === 'delivered') {
        $sql .= " AND (o.order_status = ? OR ds.delivery_status = ?)";
        $params[] = $statusFilter;
        $params[] = $statusFilter;
    } else {
        $sql .= " AND o.order_status = ?";
        $params[] = $statusFilter;
    }
}

$sql .= " ORDER BY o.order_date DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();
?>

<div class="container py-5">
    <h2 style="font-family:'Playfair Display',serif; color:var(--primary-dark); margin-bottom:30px;">
        <i class="fas fa-box me-2"></i>My Orders
    </h2>
    
    <!-- Search & Filter -->
    <div class="row mb-4">
        <div class="col-md-6">
            <form method="GET" action="" class="d-flex gap-2">
                <input type="text" name="search" class="form-control" placeholder="Search by order number..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
            </form>
        </div>
        <div class="col-md-6 text-md-end">
            <div class="btn-group flex-wrap">
                <a href="orders.php" class="btn btn-outline-primary <?php echo empty($statusFilter) ? 'active' : ''; ?>">All</a>
                <a href="?status=pending" class="btn btn-outline-primary <?php echo $statusFilter === 'pending' ? 'active' : ''; ?>">Pending</a>
                <a href="?status=paid" class="btn btn-outline-primary <?php echo $statusFilter === 'paid' ? 'active' : ''; ?>">Paid</a>
                <a href="?status=preparing" class="btn btn-outline-primary <?php echo $statusFilter === 'preparing' ? 'active' : ''; ?>">Preparing</a>
                <a href="?status=out_for_delivery" class="btn btn-outline-primary <?php echo $statusFilter === 'out_for_delivery' ? 'active' : ''; ?>">Out for Delivery</a>
                <a href="?status=delivered" class="btn btn-outline-primary <?php echo $statusFilter === 'delivered' ? 'active' : ''; ?>">Delivered</a>
            </div>
        </div>
    </div>
    
    <?php if (empty($orders)): ?>
        <div class="text-center py-5">
            <div style="font-size:5rem;">📦</div>
            <h4>No orders found</h4>
            <p class="text-muted">You haven't placed any orders yet.</p>
            <a href="shop.php" class="btn-primary-custom">Start Shopping</a>
        </div>
    <?php else: ?>
        <?php foreach ($orders as $order): ?>
        <div class="order-card">
            <div class="order-header">
                <div>
                    <strong>Order #<?php echo htmlspecialchars($order['order_number']); ?></strong>
                    <span class="text-muted ms-3"><?php echo date('d M Y, h:i A', strtotime($order['order_date'])); ?></span>
                </div>
                <span class="status-badge status-<?php echo $order['order_status']; ?>">
                    <?php echo ucwords(str_replace('_', ' ', $order['order_status'])); ?>
                </span>
            </div>
            <div class="order-body">
                <div class="row">
                    <div class="col-md-8">
                        <?php
                        $stmt = $db->prepare("SELECT oi.*, p.product_name FROM order_items oi JOIN products p ON oi.product_id = p.product_id WHERE oi.order_id = ?");
                        $stmt->execute([$order['order_id']]);
                        $items = $stmt->fetchAll();
                        
                        foreach ($items as $item):
                        ?>
                        <div class="order-item-row">
                            <div style="width:60px; height:60px; background: linear-gradient(135deg, #fce4ec 0%, #f8bbd9 100%); border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:2rem; flex-shrink:0;">
                                🌸
                            </div>
                            <div>
                                <div class="fw-bold"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                <small class="text-muted">Qty: <?php echo $item['quantity']; ?> x <?php echo formatPrice($item['unit_price']); ?></small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <div class="mb-2">
                            <span class="text-muted">Total:</span>
                            <span class="fw-bold" style="font-size:1.2rem; color:var(--primary-dark);"><?php echo formatPrice($order['final_amount']); ?></span>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted">Delivery: <?php echo $order['delivery_date'] ? date('d M Y', strtotime($order['delivery_date'])) : 'TBD'; ?></small>
                        </div>
                        <a href="order_details.php?id=<?php echo $order['order_id']; ?>" class="btn btn-outline-primary btn-sm">View Details</a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once '../../includes/footer.php'; ?>
