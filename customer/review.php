<?php
require_once '../../includes/functions.php';

if (!isLoggedIn() || $_SESSION['role'] !== 'customer') {
    redirectWithMessage('login.php', 'danger', 'Please login to submit a review.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithMessage('orders.php', 'danger', 'Invalid request.');
}

if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    redirectWithMessage('orders.php', 'danger', 'Invalid request.');
}

$orderId = (int)($_POST['order_id'] ?? 0);
$productId = (int)($_POST['product_id'] ?? 0);
$rating = (int)($_POST['rating'] ?? 0);
$comment = sanitizeInput($_POST['review_comment'] ?? '');
$userId = $_SESSION['user_id'];

if ($rating < 1 || $rating > 5) {
    redirectWithMessage("product.php?id=$productId", 'danger', 'Please select a rating between 1 and 5 stars.');
}

try {
    $db = getDB();
    
    // Verify user purchased this product
    $stmt = $db->prepare("SELECT o.order_id FROM orders o JOIN order_items oi ON o.order_id = oi.order_id WHERE o.user_id = ? AND oi.product_id = ? AND o.order_status = 'delivered' AND o.order_id = ?");
    $stmt->execute([$userId, $productId, $orderId]);
    
    if (!$stmt->fetch()) {
        redirectWithMessage("product.php?id=$productId", 'danger', 'You can only review products you have purchased and received.');
    }
    
    // Check if already reviewed
    $stmt = $db->prepare("SELECT review_id FROM reviews WHERE order_id = ? AND user_id = ? AND product_id = ?");
    $stmt->execute([$orderId, $userId, $productId]);
    if ($stmt->fetch()) {
        redirectWithMessage("product.php?id=$productId", 'danger', 'You have already reviewed this product.');
    }
    
    // Insert review
    $stmt = $db->prepare("INSERT INTO reviews (order_id, user_id, product_id, rating, review_comment) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$orderId, $userId, $productId, $rating, $comment]);
    
    // Update product rating
    $stmt = $db->prepare("UPDATE products SET rating_avg = (SELECT AVG(rating) FROM reviews WHERE product_id = ? AND status = 'approved'), rating_count = (SELECT COUNT(*) FROM reviews WHERE product_id = ? AND status = 'approved') WHERE product_id = ?");
    $stmt->execute([$productId, $productId, $productId]);
    
    logActivity($userId, 'review_submitted', "Review submitted for product #$productId");
    redirectWithMessage("product.php?id=$productId", 'success', 'Thank you for your review!');
    
} catch (PDOException $e) {
    redirectWithMessage("product.php?id=$productId", 'danger', 'Failed to submit review. Please try again.');
}
?>
