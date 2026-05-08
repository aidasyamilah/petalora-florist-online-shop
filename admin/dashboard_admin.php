<?php
require_once 'includes/admin_auth.php';
admin_auth();
include 'config.php';

// Count all customers
$result = $conn->query("SELECT COUNT(*) as count FROM customers");
$summary['customers'] = $result ? $result->fetch_assoc()['count'] : 0;

// Count all orders
$result = $conn->query("SELECT COUNT(*) as count FROM `orders`");
$summary['orders'] = $result ? $result->fetch_assoc()['count'] : 0;

// Count pending orders only
$result = $conn->query("SELECT COUNT(*) as count FROM `orders` WHERE order_status = 'pending'");
$summary['pending_orders'] = $result ? $result->fetch_assoc()['count'] : 0;

// Count all products
$result = $conn->query("SELECT COUNT(*) as count FROM products");
$summary['products'] = $result ? $result->fetch_assoc()['count'] : 0;

// Sum up all order amounts for total sales
$result = $conn->query("SELECT COALESCE(SUM(final_amount), 0) AS total_sales FROM `orders`");
$summary['total_sales'] = $result ? $result->fetch_assoc()['total_sales'] : 0;


$page_title = "Dashboard";
include 'includes/header.php';?>

<div class="dashboard-cards" data-cards-count="4">

    <div class="dash-card">
        <p class="label">Customers</p>
        <p class="value"><?php echo $summary['customers']; ?></p>
    </div>

    <div class="dash-card">
        <p class="label">Orders</p>
        <p class="value"><?php echo $summary['orders']; ?></p>
    </div>

    <div class="dash-card">
        <p class="label">Pending</p>
        <p class="value"><?php echo $summary['pending_orders']; ?></p>
    </div>

    <div class="dash-card">
        <p class="label">Products</p>
        <p class="value"><?php echo $summary['products']; ?></p>
    </div>
</div>

<div class="table">
<h3>Recent orders</h3>
    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer ID</th>
                <th>Final Amount</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT * FROM `orders` ORDER BY order_date DESC LIMIT 5";
            $result = $conn->query($sql);

            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $status_font_color = 'black';
                    $order_status_value = strtolower($row["order_status"]);
                    if ($order_status_value == 'pending') $status_font_color = '#F59E0B';
                    if ($order_status_value == 'confirmed') $status_font_color = '#3B82F6';
                    if ($order_status_value == 'out_for_delivery' || $order_status_value == 'out_for_deleivery') $status_font_color = '#8B5CF6';
                    if ($order_status_value == 'cancelled') $status_font_color = '#EF4444';
                    if ($order_status_value == 'delivered') $status_font_color = '#10B981';
                    $display_order_status = ucwords(str_replace('_', ' ', $order_status_value));

                    echo "<tr>
                        <td>". $row["order_id"] . "</td>
                        <td>" . $row["user_id"] . "</td>
                        <td>RM " . number_format($row["final_amount"], 2) . "</td>
                        <td style='color:$status_font_color; font-weight:bold;'>" . $display_order_status . "</td>
                        <td>" . date('Y-m-d', strtotime($row["order_date"])) . "</td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='5' style='text-align:center;'>No orders found</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php 
$conn->close();
include 'includes/footer.php'; 
?>
