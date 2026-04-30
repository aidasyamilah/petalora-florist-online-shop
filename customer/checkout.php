<?php
$pageTitle = 'Checkout';
require_once '../../includes/header.php';

if (!isLoggedIn() || $_SESSION['role'] !== 'customer') {
    redirectWithMessage('login.php', 'danger', 'Please login to checkout.');
}

$db = getDB();
$userId = $_SESSION['user_id'];

// Get cart items
$stmt = $db->prepare("SELECT c.*, p.product_name, p.product_price, p.stock_quantity FROM cart c JOIN products p ON c.product_id = p.product_id WHERE c.user_id = ?");
$stmt->execute([$userId]);
$cartItems = $stmt->fetchAll();

if (empty($cartItems)) {
    redirectWithMessage('cart.php', 'warning', 'Your cart is empty.');
}

// Get user details
$stmt = $db->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// Calculate totals
$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += $item['product_price'] * $item['quantity'];
}
$deliveryFee = $subtotal >= 100 ? 0 : 10;

$errors = [];
$discountAmount = 0;
$promoCode = '';
$discountPercent = 0;

// Apply promo code
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_promo'])) {
    $promoCode = strtoupper(sanitizeInput($_POST['promo_code'] ?? ''));
    if (!empty($promoCode)) {
        $stmt = $db->prepare("SELECT * FROM promo_codes WHERE promo_code = ? AND status = 'active' AND valid_from <= CURDATE() AND valid_until >= CURDATE()");
        $stmt->execute([$promoCode]);
        $promo = $stmt->fetch();
        
        if ($promo) {
            if ($promo['usage_limit'] && $promo['usage_count'] >= $promo['usage_limit']) {
                $errors[] = 'This promo code has reached its usage limit.';
            } elseif ($subtotal < $promo['min_order_amount']) {
                $errors[] = 'Minimum order of RM' . number_format($promo['min_order_amount'], 2) . ' required.';
            } else {
                if ($promo['discount_type'] === 'percentage') {
                    $discountAmount = $subtotal * ($promo['discount_value'] / 100);
                    if ($promo['max_discount'] && $discountAmount > $promo['max_discount']) {
                        $discountAmount = $promo['max_discount'];
                    }
                    $discountPercent = $promo['discount_value'];
                } else {
                    $discountAmount = $promo['discount_value'];
                }
                
                // Update usage count
                $stmt = $db->prepare("UPDATE promo_codes SET usage_count = usage_count + 1 WHERE promo_id = ?");
                $stmt->execute([$promo['promo_id']]);
            }
        } else {
            $errors[] = 'Invalid or expired promo code.';
        }
    }
}

$finalAmount = $subtotal + $deliveryFee - $discountAmount;

// Process order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request.';
    } else {
        $recipientName = sanitizeInput($_POST['recipient_name'] ?? '');
        $recipientPhone = sanitizeInput($_POST['recipient_phone'] ?? '');
        $deliveryAddress = sanitizeInput($_POST['delivery_address'] ?? '');
        $postcode = sanitizeInput($_POST['postcode'] ?? '');
        $city = sanitizeInput($_POST['city'] ?? 'Melaka');
        $state = sanitizeInput($_POST['state'] ?? 'Melaka');
        $deliveryDate = sanitizeInput($_POST['delivery_date'] ?? '');
        $timeSlot = sanitizeInput($_POST['time_slot'] ?? '');
        $paymentMethod = sanitizeInput($_POST['payment_method'] ?? '');
        $cardMessage = sanitizeInput($_POST['card_message'] ?? '');
        
        // Validation
        if (empty($recipientName)) $errors[] = 'Recipient name is required';
        if (empty($deliveryAddress)) $errors[] = 'Delivery address is required';
        if (empty($postcode) || !isValidPostcode($postcode)) $errors[] = 'Valid 5-digit postcode is required';
        if (empty($deliveryDate)) $errors[] = 'Delivery date is required';
        if (empty($timeSlot)) $errors[] = 'Time slot is required';
        if (empty($paymentMethod)) $errors[] = 'Please select a payment method';
        
        // Credit card validation for demo
        if ($paymentMethod === 'credit_card') {
            $cardNumber = preg_replace('/\s+/', '', $_POST['card_number'] ?? '');
            $cardExpiry = sanitizeInput($_POST['card_expiry'] ?? '');
            $cardCvv = sanitizeInput($_POST['card_cvv'] ?? '');
            
            if (strlen($cardNumber) !== 16 || !ctype_digit($cardNumber)) $errors[] = 'Invalid card number (16 digits required)';
            if (!preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $cardExpiry)) $errors[] = 'Invalid expiry date (MM/YY)';
            if (strlen($cardCvv) !== 3 || !ctype_digit($cardCvv)) $errors[] = 'Invalid CVV (3 digits)';
        }
        
        if (empty($errors)) {
            try {
                $db->beginTransaction();
                
                $orderNumber = generateOrderNumber();
                
                // Create order
                $stmt = $db->prepare("INSERT INTO orders (user_id, order_number, total_amount, delivery_fee, discount_amount, final_amount, order_status, payment_status, custom_card_message, promo_code, discount_percent) VALUES (?, ?, ?, ?, ?, ?, 'pending', 'pending', ?, ?, ?)");
                $stmt->execute([$userId, $orderNumber, $subtotal, $deliveryFee, $discountAmount, $finalAmount, $cardMessage, $promoCode, $discountPercent]);
                $orderId = $db->lastInsertId();
                
                // Create order items
                $stmt = $db->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?)");
                foreach ($cartItems as $item) {
                    $itemSubtotal = $item['product_price'] * $item['quantity'];
                    $stmt->execute([$orderId, $item['product_id'], $item['quantity'], $item['product_price'], $itemSubtotal]);
                }
                
                // Create delivery schedule
                $stmt = $db->prepare("INSERT INTO delivery_schedule (order_id, recipient_name, recipient_phone, delivery_address, postcode, city, state, delivery_date, time_slot) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$orderId, $recipientName, $recipientPhone, $deliveryAddress, $postcode, $city, $state, $deliveryDate, $timeSlot]);
                
                // Create payment record
                $stmt = $db->prepare("INSERT INTO payments (order_id, payment_method, payment_status, amount, card_last_four) VALUES (?, ?, 'completed', ?, ?)");
                $cardLastFour = $paymentMethod === 'credit_card' ? substr($cardNumber, -4) : null;
                $stmt->execute([$orderId, $paymentMethod, $finalAmount, $cardLastFour]);
                
                // Update order status to paid
                $stmt = $db->prepare("UPDATE orders SET order_status = 'paid', payment_status = 'paid' WHERE order_id = ?");
                $stmt->execute([$orderId]);
                
                // Clear cart
                $stmt = $db->prepare("DELETE FROM cart WHERE user_id = ?");
                $stmt->execute([$userId]);
                
                $db->commit();
                
                logActivity($userId, 'order_placed', "Order $orderNumber placed");
                
                // Send confirmation email (optional - won't break if XAMPP mail is off)
$subject = "Petalora - Order Confirmation #$orderNumber";
$body = "<h2>Thank you for your order!</h2><p>Your order <strong>$orderNumber</strong> has been placed successfully.</p><p>Total: " . formatPrice($finalAmount) . "</p>";
sendEmail($user['email'], $subject, $body);

// Redirect to order details
redirectWithMessage('order_details.php?id=' . $orderId, 'success', 'Order placed successfully! Order #' . $orderNumber);

            } catch (PDOException $e) {
                $db->rollBack();
                $errors[] = 'Failed to place order. Please try again.';
            }
        }
    }
}

$csrfToken = generateCSRFToken();

// Generate time slots
$timeSlots = ['9:00 AM - 12:00 PM', '12:00 PM - 3:00 PM', '3:00 PM - 6:00 PM', '6:00 PM - 9:00 PM'];
$minDate = date('Y-m-d');
$maxDate = date('Y-m-d', strtotime('+30 days'));
?>

<div class="container py-5">
    <h2 style="font-family:'Playfair Display',serif; color:var(--primary-dark); margin-bottom:30px;">
        <i class="fas fa-credit-card me-2"></i>Checkout
    </h2>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger mb-4">
            <?php foreach ($errors as $error) echo $error . '<br>'; ?>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-lg-8">
            <form method="POST" action="" data-validate>
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                
                <!-- Delivery Details -->
                <div style="background:white; border-radius:12px; padding:25px; box-shadow:0 4px 6px rgba(0,0,0,0.1); margin-bottom:25px;">
                    <h5 style="font-family:'Playfair Display',serif; margin-bottom:20px;"><span class="badge bg-primary me-2">1</span>Delivery Details</h5>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Recipient Name *</label>
                                <input type="text" name="recipient_name" class="form-control" required value="<?php echo htmlspecialchars($user['full_name']); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Recipient Phone</label>
                                <input type="text" name="recipient_phone" class="form-control" data-validate="phone" value="<?php echo htmlspecialchars($user['phone_number'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Delivery Address *</label>
                        <textarea name="delivery_address" class="form-control" rows="2" required><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Postcode *</label>
                                <input type="text" name="postcode" class="form-control" data-validate="postcode" required value="<?php echo htmlspecialchars($user['postcode'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">City</label>
                                <input type="text" name="city" class="form-control" value="<?php echo htmlspecialchars($user['city'] ?? 'Melaka'); ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">State</label>
                                <input type="text" name="state" class="form-control" value="<?php echo htmlspecialchars($user['state'] ?? 'Melaka'); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Delivery Date *</label>
                                <input type="date" name="delivery_date" class="form-control" required min="<?php echo $minDate; ?>" max="<?php echo $maxDate; ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Time Slot *</label>
                                <select name="time_slot" class="form-control" required>
                                    <option value="">Select time slot</option>
                                    <?php foreach ($timeSlots as $slot): ?>
                                    <option value="<?php echo $slot; ?>"><?php echo $slot; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Personal Message for Card (optional) </label>
                        <textarea name="card_message" class="form-control" rows="2" maxlength="200" placeholder="Write a heartfelt message (max 200 characters)"></textarea>
                        <small class="text-muted">This message will be included with your bouquet FOR FREE OF CHARGE</small>
                    </div>
                </div>
                
                <!-- Payment Method -->
                <div style="background:white; border-radius:12px; padding:25px; box-shadow:0 4px 6px rgba(0,0,0,0.1); margin-bottom:25px;">
                    <h5 style="font-family:'Playfair Display',serif; margin-bottom:20px;"><span class="badge bg-primary me-2">2</span>Payment Method</h5>
                    
                    <input type="hidden" name="payment_method" id="payment_method" value="">
                    
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="payment-method-card" data-method="credit_card" onclick="selectPaymentMethod('credit_card')">
                                <i class="fas fa-credit-card"></i>
                                <h6 class="mb-1">Credit Card</h6>
                                <small class="text-muted">Visa / Mastercard</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="payment-method-card" data-method="online_banking" onclick="selectPaymentMethod('online_banking')">
                                <i class="fas fa-university"></i>
                                <h6 class="mb-1">Online Banking</h6>
                                <small class="text-muted">FPX Transfer</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="payment-method-card" data-method="e_wallet" onclick="selectPaymentMethod('e_wallet')">
                                <i class="fas fa-wallet"></i>
                                <h6 class="mb-1">E-Wallet</h6>
                                <small class="text-muted">Touch n Go / GrabPay</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Credit Card Details (Demo) -->
                    <div id="card-details" style="display:none; margin-top:20px;">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>This is a simulated payment. Use any valid format for testing.
                        </div>
                        <div class="form-group">
                            <label class="form-label">Card Number</label>
                            <input type="text" name="card_number" class="form-control" placeholder="1234 5678 9012 3456" maxlength="19">
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Expiry Date</label>
                                    <input type="text" name="card_expiry" class="form-control" placeholder="MM/YY" maxlength="5">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">CVV</label>
                                    <input type="text" name="card_cvv" class="form-control" placeholder="123" maxlength="3">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <button type="submit" name="place_order" class="btn-primary-custom w-100" style="font-size:1.1rem; padding:16px;">
                    <i class="fas fa-lock me-2"></i>Place Order - <?php echo formatPrice($finalAmount); ?>
                </button>
            </form>
        </div>
        
        <!-- Order Summary -->
        <div class="col-lg-4">
            <div class="cart-summary">
                <h5 style="font-family:'Playfair Display',serif; margin-bottom:20px;">Order Summary</h5>
                
                <?php foreach ($cartItems as $item): ?>
                <div class="d-flex justify-content-between mb-2">
                    <span><?php echo htmlspecialchars($item['product_name']); ?> x<?php echo $item['quantity']; ?></span>
                    <span><?php echo formatPrice($item['product_price'] * $item['quantity']); ?></span>
                </div>
                <?php endforeach; ?>
                
                <hr>
                
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span><?php echo formatPrice($subtotal); ?></span>
                </div>
                <div class="summary-row">
                    <span>Delivery</span>
                    <span><?php echo $deliveryFee === 0 ? 'FREE' : formatPrice($deliveryFee); ?></span>
                </div>
                <?php if ($discountAmount > 0): ?>
                <div class="summary-row" style="color:var(--success);">
                    <span>Discount (<?php echo $promoCode; ?>)</span>
                    <span>-<?php echo formatPrice($discountAmount); ?></span>
                </div>
                <?php endif; ?>
                <div class="summary-row total">
                    <span>Total</span>
                    <span><?php echo formatPrice($finalAmount); ?></span>
                </div>
                
                <!-- Promo Code -->
                <form method="POST" action="" class="mt-3">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    <div class="input-group">
                        <input type="text" name="promo_code" class="form-control" placeholder="Enter promo code" value="<?php echo htmlspecialchars($promoCode); ?>">
                        <button type="submit" name="apply_promo" class="btn btn-outline-primary">Apply</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
