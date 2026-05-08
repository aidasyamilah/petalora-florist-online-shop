<?php
require_once 'includes/admin_auth.php';
admin_auth();
include 'config.php';

if (isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $orderStatus = trim($_POST['order_status']);
    $validOrderStatuses = ['pending', 'confirmed', 'out_for_delivery', 'delivered', 'cancelled'];

    if (in_array($orderStatus, $validOrderStatuses, true)) {
        $update_order_status = $conn->prepare("UPDATE `orders` SET order_status=? WHERE order_id=?");
        $update_order_status->bind_param("si", $orderStatus, $order_id);
        $update_order_status->execute();
        $update_order_status->close();
        set_admin_message("Order status updated.");
    }
    redirect_with_admin_message("orders_admin.php");
}

if (isset($_GET['delete'])) {
    $order_id = intval($_GET['delete']);
    $delete_order = $conn->prepare("DELETE FROM `orders` WHERE order_id=?");
    $delete_order->bind_param("i", $order_id);
    $delete_order->execute();
    $delete_order->close();
    redirect_with_admin_message("orders_admin.php", "Order deleted.");
}

$page_title = "Orders";
$search_keyword = trim($_GET['search'] ?? '');
include 'includes/header.php';
?>

<div class="list-toolbar">
    <form class="search-form" method="get">
        <input class="form-control" type="text" name="search" placeholder="Search order id, customer, or status" value="<?php echo htmlspecialchars($search_keyword); ?>">
        <button class="btn btn-ghost" type="submit">Search</button>
    </form>
</div>

<div class="table">
    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Order Date</th>
                <th>Total Amount</th>
                <th>Final Amount</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
            <?php
            $orderListSql = "SELECT o.*, c.full_name, c.email FROM `orders` o LEFT JOIN customers c ON o.user_id = c.customer_id";
            if ($search_keyword !== '') {
                $undo_search = $conn->real_escape_string($search_keyword);
                $orderListSql .= " WHERE CAST(o.order_id AS CHAR) LIKE '%{$undo_search}%' OR c.full_name LIKE '%{$undo_search}%' OR c.email LIKE '%{$undo_search}%' OR o.order_status LIKE '%{$undo_search}%'";
            }
            $orderListSql .= " ORDER BY o.order_id DESC";
            $orderListResult = $conn->query($orderListSql);

            if ($orderListResult && $orderListResult->num_rows > 0) {
                while ($orderRow = $orderListResult->fetch_assoc()) {
                    echo "<tr>
                        <td>{$orderRow['order_id']}</td>
                        <td>" . htmlspecialchars($orderRow['full_name'] ?? 'Unknown') . "<br><small>" . htmlspecialchars($orderRow['email'] ?? '') . "</small></td>
                        <td>" . date('Y-m-d H:i', strtotime($orderRow['order_date'])) . "</td>
                        <td>RM " . number_format($orderRow['total_amount'], 2) . "</td>
                        <td>RM " . number_format($orderRow['final_amount'], 2) . "</td>
                        <td>
                            <form method='post' style='display:inline;'>
                                <input type='hidden' name='order_id' value='{$orderRow['order_id']}'>
                                <select class='form-control' name='order_status' onchange='this.form.submit()' style='min-width:190px; padding:8px;'>
                                    <option value='pending' " . ($orderRow['order_status'] === 'pending' ? 'selected' : '') . ">Pending</option>
                                    <option value='confirmed' " . ($orderRow['order_status'] === 'confirmed' ? 'selected' : '') . ">Confirmed</option>
                                    <option value='out_for_delivery' " . ($orderRow['order_status'] === 'out_for_delivery' ? 'selected' : '') . ">Out for Delivery</option>
                                    <option value='cancelled' " . ($orderRow['order_status'] === 'cancelled' ? 'selected' : '') . ">Cancelled</option>
                                    <option value='delivered' " . ($orderRow['order_status'] === 'delivered' ? 'selected' : '') . ">Delivered</option>
                                </select>
                                
                                <input type='hidden' name='update_status' value='1'>
                            </form>
                        </td>

                        <td>
                            <a href='orders_admin.php?delete={$orderRow['order_id']}' class='icon-btn delete' data-confirm-delete='true'><i class='fa fa-trash'></i></a>
                        </td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='7' style='text-align:center;'>No orders found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php
$conn->close();
include 'includes/footer.php';
?>
