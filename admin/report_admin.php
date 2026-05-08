<?php
require_once 'includes/admin_auth.php';
admin_auth();
include 'config.php';

$report_view = $_GET['view'] ?? 'sales';
if (!in_array($report_view, ['sales', 'stock'], true)) {
    $report_view = 'sales';
}

$sales_conditions = [];
$search_keyword = trim($_GET['search'] ?? '');
if ($search_keyword !== '') {
    $safe_search_keyword = $conn->real_escape_string($search_keyword);
    $sales_conditions[] = "(CAST(o.order_id AS CHAR) LIKE '%{$safe_search_keyword}%' OR p.product_name LIKE '%{$safe_search_keyword}%')";
}

$filter_month = trim($_GET['month'] ?? '');
if ($filter_month !== '') {
    $safe_filter_month = $conn->real_escape_string($filter_month);
    $sales_conditions[] = "DATE_FORMAT(o.order_date, '%Y-%m') = '{$safe_filter_month}'";
}

$filter_status = trim($_GET['status'] ?? '');
if ($filter_status !== '') {
    $safe_filter_status = $conn->real_escape_string($filter_status);
    $sales_conditions[] = "o.order_status = '{$safe_filter_status}'";
}

$sales_where_sql = count($sales_conditions) > 0 ? 'WHERE ' . implode(' AND ', $sales_conditions) : '';

$result = $conn->query('SELECT COUNT(*) AS count FROM `orders`');
$order_summary['total_orders'] = $result ? $result->fetch_assoc()['count'] : 0;

$result = $conn->query('SELECT COALESCE(SUM(final_amount), 0) AS total FROM `orders`');
$sales_summary['total_sales'] = $result ? $result->fetch_assoc()['total'] : 0;

$result = $conn->query('SELECT COUNT(*) AS count FROM products');
$stock_summary['total_products'] = $result ? $result->fetch_assoc()['count'] : 0;

$stock_report_sql = "SELECT p.product_id, p.product_name, p.product_image, p.availability_status, c.category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.category_id
    ORDER BY p.product_name ASC";
$stock_report_result = $conn->query($stock_report_sql);

$sales_report_sql = "SELECT o.order_id, o.order_date, o.order_status, o.final_amount, p.product_name, oi.quantity
    FROM `orders` o
    LEFT JOIN order_items oi ON oi.order_id = o.order_id
    LEFT JOIN products p ON p.product_id = oi.product_id
    {$sales_where_sql}
    ORDER BY o.order_date DESC";
$sales_report_result = $conn->query($sales_report_sql);

$page_title = "Reports";
include 'includes/header.php';
?>

<!-- Tab Navigation -->
<div class="report-toolbar no-print" style="margin-bottom: 20px; border-bottom: 1px solid #ddd; padding-bottom: 10px;">
    <a href="report_admin.php?view=sales<?php echo $search_keyword ? '&search=' . urlencode($search_keyword) : ''; ?>" 
       class="btn" 
       style="
       background: <?php echo $report_view==='sales'?'#333':'#777'; ?>; 
       color: #fff; 
       text-decoration: none; 
       padding: 10px 20px; 
       border-radius: 4px; 
       margin-right: 5px;"
       >Sales Report</a>
    
    <a href="report_admin.php?view=stock" 
       class="btn" 
       style="
       background: <?php echo $report_view==='stock'?'#333':'#777'; ?>; 
       color: #fff; 
       text-decoration: none; 
       padding: 10px 20px; 
       border-radius: 4px;"
       >Stock Report</a>
</div>

<!-- Toolbar -->
<?php if ($report_view === 'sales'): ?>
<div class="list-toolbar">
    <form class="search-form" method="get">
        <input type="hidden" name="view" value="sales">
        <input class="form-control" type="text" name="search" placeholder="Search order id or product" value="<?php echo htmlspecialchars($search_keyword); ?>">
        <input class="form-control" type="month" name="month" value="<?php echo htmlspecialchars($filter_month); ?>">
        <select class="form-control" name="status">
            <option value="">All Statuses</option>
            <option value="pending" <?php echo $filter_status === 'pending' ? 'selected' : ''; ?>>Pending</option>
            <option value="confirmed" <?php echo $filter_status === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
            <option value="out_for_delivery" <?php echo $filter_status === 'out_for_delivery' ? 'selected' : ''; ?>>Out for Delivery</option>
            <option value="delivered" <?php echo $filter_status === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
            <option value="cancelled" <?php echo $filter_status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
        </select>
        <button class="btn btn-ghost" type="submit">Search</button>
    </form>
    <button class="btn btn-primary" type="button" onclick="window.print()">Print Report</button>
</div>
<?php endif; ?>

<!-- STOCK VIEW -->
<?php if ($report_view === 'stock'): ?>
    <div class="dashboard-cards" data-cards-count="2">
        <div class="dash-card">
            <p class="label">Total Products</p>
            <p class="value"><?php echo (int)($stock_summary['total_products'] ?? 0); ?></p>
        </div>
        <div class="dash-card">
            <p class="label">Total Sales</p>
            <p class="value">RM <?php echo number_format((float)($sales_summary['total_sales'] ?? 0), 2); ?></p>
        </div>
    </div>

    <div class="table print-content-only">
        <h3>Stock Report (<?php echo (int)($stock_summary['total_products'] ?? 0); ?> total products)</h3>
        <?php if (!$stock_report_result): ?>
            <div style="padding: 20px; text-align: center; color: #d00;">
                <p>Database Error: <?php echo htmlspecialchars($conn->error); ?></p>
            </div>
        <?php elseif ($stock_report_result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Availability</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($stock = $stock_report_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo (int)$stock['product_id']; ?></td>
                            <td>
                                <?php if (!empty($stock['product_image'])): ?>
                                    <img src="<?php echo htmlspecialchars($stock['product_image']); ?>" alt="Product Image" style="width:48px;height:48px;object-fit:cover;border-radius:4px;">
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($stock['product_name']); ?></td>
                            <td><?php echo htmlspecialchars($stock['category_name'] ?? 'Uncategorized'); ?></td>
                            <td>
                                <span style="color: <?php echo $stock['availability_status'] === 'In Stock' ? '#0a0' : '#d00'; ?>">
                                    <?php echo htmlspecialchars($stock['availability_status']); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="padding: 20px; text-align: center;">
                <p>No products found in database.</p>
                <p><a href="products_admin.php">Add products here</a></p>
            </div>
        <?php endif; ?>
    </div>

<!-- SALES VIEW -->
<?php elseif ($report_view === 'sales'): ?>
    <div class="dashboard-cards" data-cards-count="2">
        <div class="dash-card">
            <p class="label">Total Orders</p>
            <p class="value"><?php echo (int)$order_summary['total_orders']; ?></p>
        </div>
        <div class="dash-card">
            <p class="label">Total Sales</p>
            <p class="value">RM <?php echo number_format((float)$sales_summary['total_sales'], 2); ?></p>
        </div>
    </div>

    <div class="table print-content-only">
        <h3>Sales Report <?php echo $search_keyword ? '(Filtered: ' . htmlspecialchars($search_keyword) . ')' : ''; ?></h3>
        <?php if (!$sales_report_result): ?>
            <div style="padding: 20px; text-align: center; color: #d00;">
                <p>Database Error: <?php echo htmlspecialchars($conn->error); ?></p>
            </div>
        <?php elseif ($sales_report_result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Date</th>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Status</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($sales = $sales_report_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo (int)$sales['order_id']; ?></td>
                            <td><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($sales['order_date']))); ?></td>
                            <td><?php echo htmlspecialchars($sales['product_name'] ?? '-'); ?></td>
                            <td><?php echo (int)($sales['quantity'] ?? 0); ?></td>
                            <td><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $sales['order_status']))); ?></td>
                            <td>RM <?php echo number_format((float)$sales['final_amount'], 2); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="padding: 20px; text-align: center;">
                <?php if ($search_keyword): ?>
                    <p>No sales found matching "<?php echo htmlspecialchars($search_keyword); ?>".</p>
                <?php else: ?>
                    <p>No sales data found.</p>
                <?php endif; ?>
                <p><a href="report_admin.php?view=sales">Clear search</a></p>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php
$conn->close();
include 'includes/footer.php';
?>
