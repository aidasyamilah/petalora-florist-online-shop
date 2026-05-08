<?php
require_once 'includes/admin_auth.php';
$flash_message = get_admin_message();
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Petalora Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="includes/admin.css">
</head>

<body>
<div class="admin-shell">
    <aside class="sidebar">
        <div class="sidebar-header">
            <h3>Petalora Florist</h3>
            <small>Admin</small>
        </div>

        <ul class="sidebar-nav">
            
            <li class="sidebar-item">
                <a href="dashboard_admin.php" class="<?php echo $current_page === 'dashboard_admin.php' ? 'active' : ''; ?>">
                    <i class="fa fa-dashboard"></i><span>Dashboard</span></a></li>
            <li class="sidebar-item">
                <a href="products_admin.php" class="<?php echo $current_page === 'products_admin.php' ? 'active' : ''; ?>"><i class="fa fa-cube"></i><span>Products</span></a></li>
            <li class="sidebar-item">
                <a href="categories_admin.php" class="<?php echo $current_page === 'categories_admin.php' ? 'active' : ''; ?>"><i class="fa fa-tags"></i><span>Categories</span></a></li>
            <li class="sidebar-item">
                <a href="orders_admin.php" class="<?php echo $current_page === 'orders_admin.php' ? 'active' : ''; ?>"><i class="fa fa-list-alt"></i><span>Orders</span></a></li>
            <li class="sidebar-item">
                <a href="report_admin.php" class="<?php echo $current_page === 'report_admin.php' ? 'active' : ''; ?>"><i class="fa fa-bar-chart"></i><span>Reports</span></a></li>
            <li class="sidebar-item">
                <a href="customer_admin.php" class="<?php echo $current_page === 'customer_admin.php' ? 'active' : ''; ?>"><i class="fa fa-users"></i><span>Customers</span></a></li>
            <li class="sidebar-item">
                <a href="admin.php" class="<?php echo $current_page === 'admin.php' ? 'active' : ''; ?>"><i class="fa fa-user-secret"></i><span>Admins</span></a></li>
        </ul>

        <div class="sidebar-bottom">
            <a class="sidebar-profile-link <?php echo $current_page === 'admin.php' ? 'active' : ''; ?>" href="admin.php">
                <i class="fa fa-user-circle"></i>
                <span><?php echo htmlspecialchars($_SESSION['admin_email'] ?? 'admin@petalora.com'); ?></span>
            </a>
            <a class="sidebar-item" href="logout_admin.php" style="margin:0;">
                <span style="display:flex;align-items:center;gap:10px;color:#ddd;"><i class="fa fa-sign-out"></i>Logout</span>
            </a>
        </div>
    </aside>

    <main class="main-content">
        <div class="top-bar">
            <div>
                <h2><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Dashboard'; ?></h2>
            </div>
        </div>

        <div class="toast-wrap" id="toastWrap">
            <?php if ($flash_message): ?>
                <div class="notice <?php echo ($flash_message['type'] ?? 'success') === 'error' ? 'error' : ''; ?>" data-flash-notice>
                    <?php echo htmlspecialchars($flash_message['message'] ?? 'Saved.'); ?>
                </div>
            <?php endif; ?>
        </div>
