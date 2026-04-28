<?php
require_once __DIR__ . '/functions.php';
startSecureSession();

$cartCount = 0;
$wishlistCount = 0;

if (isLoggedIn() && $_SESSION['role'] === 'customer') {
    $cartCount = getCartCount($_SESSION['user_id']);
    $wishlistCount = getWishlistCount($_SESSION['user_id']);
}

$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Petalora - Premium Online Florist in Melaka. Fresh flowers for all occasions.">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' | ' : ''; ?>Petalora Florist</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <style>body{font-family:'Poppins',sans-serif;}h1,h2,h3,h4,h5,h6{font-family:'Playfair Display',serif;}</style>
</head>
<body>
<header class="main-header">
    <div class="header-top">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <small><i class="fas fa-truck me-2"></i>Free delivery for orders above RM100 in Melaka</small>
                </div>
                <div class="col-md-6 text-end">
                    <small><i class="fas fa-phone me-2"></i>+60 12-345 6789 | <i class="fas fa-envelope ms-3 me-2"></i>petalora.support@gmail.com</small>
                </div>
            </div>
        </div>
    </div>
    <div class="header-main">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-3 col-md-4 col-6">
                    <a href="<?php echo SITE_URL; ?>/pages/customer/index.php" class="brand">
                        <span class="brand-icon">🌸</span>
                        <div class="brand-text">
                            <h1>Petalora</h1>
                            <span>Florist Online Shop </span>
                        </div>
                    </a>
                </div>
                <div class="col-lg-6 col-md-4 d-none d-md-block">
                    <form action="<?php echo SITE_URL; ?>/pages/customer/search.php" method="GET" class="search-bar">
                        <input type="text" name="q" placeholder="Search for flowers, bouquets..." value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
                        <button type="submit"><i class="fas fa-search"></i></button>
                    </form>
                </div>
                <div class="col-lg-3 col-md-4 col-6">
                    <div class="header-actions">
                        <a href="<?php echo SITE_URL; ?>/pages/customer/wishlist.php" class="header-action">
                            <i class="far fa-heart"></i>
                            <?php if ($wishlistCount > 0): ?>
                                <span class="badge" id="wishlist-count"><?php echo $wishlistCount; ?></span>
                            <?php endif; ?>
                            <span class="d-none d-lg-inline">Wishlist</span>
                        </a>
                        <a href="<?php echo SITE_URL; ?>/pages/customer/cart.php" class="header-action">
                            <i class="fas fa-shopping-bag"></i>
                            <?php if ($cartCount > 0): ?>
                                <span class="badge" id="cart-count"><?php echo $cartCount; ?></span>
                            <?php endif; ?>
                            <span class="d-none d-lg-inline">Cart</span>
                        </a>
                        <?php if (isLoggedIn() && $_SESSION['role'] === 'customer'): ?>
                            <div class="dropdown">
                                <a href="#" class="header-action dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="far fa-user-circle"></i>
                                    <span class="d-none d-lg-inline">Account</span>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/pages/customer/profile.php"><i class="fas fa-user me-2"></i>My Profile</a></li>
                                    <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/pages/customer/orders.php"><i class="fas fa-box me-2"></i>My Orders</a></li>
                                    <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/pages/customer/wishlist.php"><i class="fas fa-heart me-2"></i>Wishlist</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="<?php echo SITE_URL; ?>/pages/customer/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                                </ul>
                            </div>
                        <?php else: ?>
    <div class="dropdown">
        <a href="#" class="header-action dropdown-toggle" data-bs-toggle="dropdown">
            <i class="far fa-user"></i>
            <span class="d-none d-lg-inline">Login</span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/pages/customer/login.php"><i class="fas fa-user me-2"></i>Customer Login</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/pages/admin/login.php"><i class="fas fa-user-shield me-2"></i>Admin Login</a></li>
        </ul>
    </div>
<?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <nav class="nav-menu">
        <div class="container">
            <ul class="nav justify-content-center">
                <li class="nav-item"><a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/pages/customer/index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'shop.php' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/pages/customer/shop.php">Shop</a></li>
                <li class="nav-item"><a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/pages/customer/categories.php">Categories</a></li>
                <li class="nav-item"><a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/pages/customer/about.php">About</a></li>
                <li class="nav-item"><a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/pages/customer/contact.php">Contact Us</a></li>
            </ul>
        </div>
    </nav>
</header>

<?php if ($flash): ?>
<div class="container mt-4">
    <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show">
        <i class="fas fa-<?php echo $flash['type'] == 'success' ? 'check-circle' : ($flash['type'] == 'danger' ? 'exclamation-circle' : 'info-circle'); ?> me-2"></i>
        <?php echo $flash['message']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
</div>
<?php endif; ?>
