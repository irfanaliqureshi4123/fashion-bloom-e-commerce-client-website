<?php
session_start();
include '../includes/header.php';
include '../includes/db.php';

$user_id = $_SESSION['user_id'] ?? null;

// Redirect to login if not logged in
if (!$user_id) {
    header('Location: ../login.php');
    exit();
}

// Get cart items from database
$sql = "SELECT * FROM shopping_cart WHERE user_id = ? ORDER BY updated_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();

// Calculate total
$total = 0;
foreach ($cart_items as $item) {
    $total += $item['product_price'] * $item['quantity'];
}

$tax = $total * 0.17;
$grand_total = $total + $tax;

// Get user data if exists
$userQuery = "SELECT * FROM users WHERE id = ?";
$userStmt = $pdo->prepare($userQuery);
$userStmt->execute([$user_id]);
$user = $userStmt->fetch();

$checkout_error = '';
$checkout_success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $city = $_POST['city'] ?? '';
    $postal_code = $_POST['postal_code'] ?? '';
    $notes = $_POST['notes'] ?? '';
    $payment_method = $_POST['payment_method'] ?? '';

    // Validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($phone) || empty($address) || empty($city) || empty($postal_code)) {
        $checkout_error = 'Please fill in all required fields.';
    } elseif (empty($payment_method)) {
        $checkout_error = 'Please select a payment method.';
    } else {
        // Additional validation for card payments
        if ($payment_method === 'card') {
            $card_number = $_POST['card_number'] ?? '';
            $card_name = $_POST['card_name'] ?? '';
            $card_expiry = $_POST['card_expiry'] ?? '';
            $card_cvv = $_POST['card_cvv'] ?? '';

            if (empty($card_number) || empty($card_name) || empty($card_expiry) || empty($card_cvv)) {
                $checkout_error = 'Please fill in all card details.';
            }
        }

        if (empty($checkout_error)) {
            // Create order in database
            $order_sql = "INSERT INTO orders (
                user_id, 
                first_name, 
                last_name, 
                email, 
                phone, 
                address, 
                city, 
                postal_code, 
                notes, 
                payment_method, 
                subtotal, 
                tax, 
                total, 
                shipping_cost,
                order_status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $order_stmt = $pdo->prepare($order_sql);
            $order_stmt->execute([
                $user_id,
                $first_name,
                $last_name,
                $email,
                $phone,
                $address,
                $city,
                $postal_code,
                $notes,
                $payment_method,
                $total,
                $tax,
                $grand_total,
                0,
                'pending'
            ]);

            $order_id = $pdo->lastInsertId();

            // Add order items
            foreach ($cart_items as $item) {
                $item_sql = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
                $item_stmt = $pdo->prepare($item_sql);
                $item_stmt->execute([
                    $order_id,
                    $item['product_id'],
                    $item['quantity'],
                    $item['product_price']
                ]);
            }

            // Clear cart
            $clear_sql = "DELETE FROM shopping_cart WHERE user_id = ?";
            $clear_stmt = $pdo->prepare($clear_sql);
            $clear_stmt->execute([$user_id]);

            $checkout_success = true;
        }
    }
}
?>

<link rel="stylesheet" href="../css/checkout-new.css">

<main class="checkout-container" style="margin-top: 80px;">
    <div class="container">
        <!-- Page Title -->
        <div class="checkout-header">
            <h1>Checkout</h1>
            <p>Review your order and complete payment</p>
        </div>

        <?php if ($checkout_success): ?>
            <!-- Success Message -->
            <div class="success-container">
                <div class="success-message">
                    <div class="success-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h2>Order Placed Successfully!</h2>
                    <p>Your order has been received and is being processed.</p>
                    <p class="order-id">Order ID: <strong><?php echo sprintf('%05d', $order_id); ?></strong></p>
                    <p>A confirmation email has been sent to <strong><?php echo htmlspecialchars($email); ?></strong></p>
                    <div class="success-actions">
                        <a href="../index.php" class="btn btn-primary">
                            Continue Shopping
                        </a>
                        <a href="../profile.php" class="btn btn-secondary">
                            View Orders
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Main Content Grid -->
            <div class="checkout-grid-unified">
                <!-- Left Section - Order Summary + Shipping Info + Payment -->
                <div class="checkout-main-unified">
                    <form method="POST" class="checkout-form" id="checkoutForm">
                        <!-- Order Summary Section -->
                        <div class="checkout-card">
                            <h2>Order Summary</h2>
                            <div class="products-section">
                                <?php foreach ($cart_items as $item): ?>
                                    <div class="product-item-minimal">
                                        <div class="product-info-minimal">
                                            <p class="product-name"><?php echo htmlspecialchars($item['product_name']); ?></p>
                                            <p class="product-qty">Qty: <?php echo $item['quantity']; ?></p>
                                        </div>
                                        <p class="product-price">PKR <?php echo number_format($item['product_price'] * $item['quantity']); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="totals-section-minimal">
                                <div class="total-row">
                                    <span>Subtotal:</span>
                                    <span>PKR <?php echo number_format($total); ?></span>
                                </div>
                                <div class="total-row">
                                    <span>Shipping:</span>
                                    <span class="free">Free</span>
                                </div>
                                <div class="total-row">
                                    <span>Tax (17%):</span>
                                    <span>PKR <?php echo number_format($tax); ?></span>
                                </div>
                                <div class="total-row grand-total">
                                    <span>Total:</span>
                                    <span>PKR <?php echo number_format($grand_total); ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Shipping Information Section -->
                        <div class="checkout-card">
                            <h2>Shipping Information</h2>

                            <?php if ($checkout_error): ?>
                                <div class="error-message">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <?php echo htmlspecialchars($checkout_error); ?>
                                </div>
                            <?php endif; ?>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="first_name">First Name</label>
                                    <input type="text" id="first_name" name="first_name" 
                                           value="<?php echo isset($user['first_name']) ? htmlspecialchars($user['first_name']) : ''; ?>" 
                                           required>
                                </div>
                                <div class="form-group">
                                    <label for="last_name">Last Name</label>
                                    <input type="text" id="last_name" name="last_name" 
                                           value="<?php echo isset($user['last_name']) ? htmlspecialchars($user['last_name']) : ''; ?>" 
                                           required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" id="email" name="email" 
                                           value="<?php echo isset($user['email']) ? htmlspecialchars($user['email']) : ''; ?>" 
                                           required>
                                </div>
                                <div class="form-group">
                                    <label for="phone">Phone Number</label>
                                    <input type="tel" id="phone" name="phone" 
                                           value="<?php echo isset($user['phone']) ? htmlspecialchars($user['phone']) : ''; ?>" 
                                           required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="address">Address</label>
                                <input type="text" id="address" name="address" 
                                       placeholder="Street address" 
                                       required>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="city">City</label>
                                    <input type="text" id="city" name="city" 
                                           required>
                                </div>
                                <div class="form-group">
                                    <label for="postal_code">Postal Code</label>
                                    <input type="text" id="postal_code" name="postal_code" 
                                           required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="notes">Order Notes (Optional)</label>
                                <textarea id="notes" name="notes" 
                                          placeholder="Add special instructions..."
                                          rows="3"></textarea>
                            </div>
                        </div>

                        <!-- Continue to Payment Button -->
                        <div class="section-actions">
                            <button type="button" class="btn btn-primary" onclick="scrollToPayment()">
                                Continue to Payment Method
                            </button>
                        </div>

                        <!-- Payment Method Section -->
                        <div class="checkout-card">
                            <h2>Payment Method</h2>

                            <div class="payment-methods">
                                <!-- COD -->
                                <div class="payment-method-group">
                                    <label for="cod-summary" class="payment-option">
                                        <input id="cod-summary" type="radio" name="payment_method" value="cod" required>
                                        <div class="payment-method-card">
                                            <div class="payment-icon">
                                                <i class="fas fa-money-bill-wave"></i>
                                            </div>
                                            <div class="payment-info">
                                                <h3>Cash on Delivery</h3>
                                                <p>Pay when you receive your order</p>
                                            </div>
                                            <div class="checkmark">
                                                <i class="fas fa-check-circle"></i>
                                            </div>
                                        </div>
                                    </label>
                                </div>

                                <!-- Credit/Debit Card -->
                                <div class="payment-method-group">
                                    <label for="card-summary" class="payment-option">
                                        <input id="card-summary" type="radio" name="payment_method" value="card" required>
                                        <div class="payment-method-card">
                                            <div class="payment-icon">
                                                <i class="fas fa-credit-card"></i>
                                            </div>
                                            <div class="payment-info">
                                                <h3>Credit/Debit Card</h3>
                                                <p>Secure online payment</p>
                                            </div>
                                            <div class="checkmark">
                                                <i class="fas fa-check-circle"></i>
                                            </div>
                                        </div>
                                    </label>
                                </div>

                                <!-- Bank Transfer -->
                                <div class="payment-method-group">
                                    <label for="bank-summary" class="payment-option">
                                        <input id="bank-summary" type="radio" name="payment_method" value="bank_transfer" required>
                                        <div class="payment-method-card">
                                            <div class="payment-icon">
                                                <i class="fas fa-university"></i>
                                            </div>
                                            <div class="payment-info">
                                                <h3>Bank Transfer</h3>
                                                <p>Direct bank transfer</p>
                                            </div>
                                            <div class="checkmark">
                                                <i class="fas fa-check-circle"></i>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Card Details (Hidden by default) -->
                            <div class="card-details" id="cardDetails" style="display: none;">
                                <h3>Card Information</h3>
                                <div class="form-group">
                                    <label for="card_name">Cardholder Name</label>
                                    <input type="text" id="card_name" name="card_name" 
                                           placeholder="Name on card"
                                           autocomplete="cc-name">
                                </div>

                                <div class="form-group">
                                    <label for="card_number">Card Number</label>
                                    <input type="text" id="card_number" name="card_number" 
                                           placeholder="1234 5678 9012 3456" 
                                           maxlength="19"
                                           autocomplete="cc-number">
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="card_expiry">Expiry Date</label>
                                        <input type="text" id="card_expiry" name="card_expiry" 
                                               placeholder="MM/YY" 
                                               maxlength="5"
                                               autocomplete="cc-exp">
                                    </div>
                                    <div class="form-group">
                                        <label for="card_cvv">CVV</label>
                                        <input type="text" id="card_cvv" name="card_cvv" 
                                               placeholder="123" 
                                               maxlength="4"
                                               autocomplete="cc-csc">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="checkout-actions">
                            <a href="../index.php" class="btn btn-secondary">
                                Edit Cart
                            </a>
                            <button type="submit" class="btn btn-primary">
                                Place Order
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<script>
// Scroll to Payment Method Section
function scrollToPayment() {
    const paymentSection = document.querySelector('.payment-methods').closest('.checkout-card');
    paymentSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// Payment method toggle
const paymentRadios = document.querySelectorAll('input[name="payment_method"]');
const cardDetails = document.getElementById('cardDetails');

paymentRadios.forEach(radio => {
    radio.addEventListener('change', function() {
        if (this.value === 'card') {
            cardDetails.style.display = 'block';
            document.getElementById('card_name').required = true;
            document.getElementById('card_number').required = true;
            document.getElementById('card_expiry').required = true;
            document.getElementById('card_cvv').required = true;
        } else {
            cardDetails.style.display = 'none';
            document.getElementById('card_name').required = false;
            document.getElementById('card_number').required = false;
            document.getElementById('card_expiry').required = false;
            document.getElementById('card_cvv').required = false;
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>
