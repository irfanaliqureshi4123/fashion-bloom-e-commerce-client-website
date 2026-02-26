<?php
/**
 * Payment Processing Page
 * Handles payment method selection and order placement
 * Step 3 of the checkout process
 */

session_start();
include '../includes/header.php';
include '../includes/db.php';

// ============================================================================
// SECTION 1: AUTHENTICATION & VALIDATION
// ============================================================================

$user_id = $_SESSION['user_id'] ?? null;

// Check if user is logged in
if (!$user_id) {
    header('Location: ../login.php');
    exit();
}

// Check if user has completed shipping step
if (!isset($_SESSION['shipping_info'])) {
    header('Location: shipping.php');
    exit();
}

// ============================================================================
// SECTION 2: FETCH CART DATA
// ============================================================================

$sql = "SELECT * FROM shopping_cart WHERE user_id = ? ORDER BY updated_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();

// ============================================================================
// SECTION 3: CALCULATE TOTALS
// ============================================================================

// Calculate subtotal from all cart items
$total = 0;
foreach ($cart_items as $item) {
    $total += $item['product_price'] * $item['quantity'];
}

// Calculate tax (17% tax rate)
$tax = $total * 0.17;

// Calculate final total with tax
$grand_total = $total + $tax;

// ============================================================================
// SECTION 4: PROCESS PAYMENT FORM
// ============================================================================

$payment_error = '';
$payment_success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Get payment method from form
    $payment_method = $_POST['payment_method'] ?? '';
    
    // STEP 1: Validate payment method is selected
    if (empty($payment_method)) {
        $payment_error = 'Please select a payment method.';
    } 
    // STEP 2: Additional validation for card payments
    else if ($payment_method === 'card') {
        $card_number = $_POST['card_number'] ?? '';
        $card_name = $_POST['card_name'] ?? '';
        $card_expiry = $_POST['card_expiry'] ?? '';
        $card_cvv = $_POST['card_cvv'] ?? '';

        if (empty($card_number) || empty($card_name) || empty($card_expiry) || empty($card_cvv)) {
            $payment_error = 'Please fill in all card details.';
        }
    }
    
    // STEP 3: If validation passed, create the order
    if (empty($payment_error)) {
        
        $shipping_info = $_SESSION['shipping_info'];
        
        // Create main order record
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
            $shipping_info['first_name'],
            $shipping_info['last_name'],
            $shipping_info['email'],
            $shipping_info['phone'],
            $shipping_info['address'],
            $shipping_info['city'],
            $shipping_info['postal_code'],
            $shipping_info['notes'],
            $payment_method,
            $total,
            $tax,
            $grand_total,
            0,
            'pending'
        ]);

        // Get the newly created order ID
        $order_id = $pdo->lastInsertId();

        // Add each cart item to the order
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

        // Clear shopping cart after order is created
        $clear_sql = "DELETE FROM shopping_cart WHERE user_id = ?";
        $clear_stmt = $pdo->prepare($clear_sql);
        $clear_stmt->execute([$user_id]);

        // Clear shipping information from session
        unset($_SESSION['shipping_info']);

        // Mark payment as successful
        $payment_success = true;
    }
}
?>

<link rel="stylesheet" href="../css/checkout-new.css">

<main class="checkout-container" style="margin-top: 80px;">
    <div class="container">
        
        <!-- ===== CHECKOUT PROGRESS INDICATOR ===== -->
        <div class="step-indicator">
            <div class="step">
                <div class="step-number">1</div>
                <span>Order Summary</span>
            </div>
            <div class="step-line"></div>
            <div class="step">
                <div class="step-number">2</div>
                <span>Shipping</span>
            </div>
            <div class="step-line"></div>
            <div class="step active">
                <div class="step-number">3</div>
                <span>Payment</span>
            </div>
        </div>

        <!-- Back to Cart Button -->
        <button class="back-to-cart-btn" onclick="toggleCartSidebar()">
            <i class="fas fa-shopping-cart"></i> Back to Cart
        </button>

        <!-- ===== SUCCESS MESSAGE (shown after order is placed) ===== -->
        <?php if ($payment_success): ?>
            <div class="success-container">
                <div class="success-message">
                    <div class="success-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h2>Order Placed Successfully!</h2>
                    <p>Your order has been received and is being processed.</p>
                    <p class="order-id">Order ID: <strong><?php echo sprintf('%05d', $order_id); ?></strong></p>
                    <p>A confirmation email has been sent to <strong><?php echo htmlspecialchars($shipping_info['email']); ?></strong></p>
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
        
        <!-- ===== PAYMENT FORM (shown if order not yet placed) ===== -->
        <?php else: ?>
            <div class="checkout-grid">
                
                <!-- LEFT SECTION: Payment Selection & Card Details -->
                <div class="checkout-main">
                    <div class="checkout-card">
                        <h1>Payment Method</h1>

                        <!-- Show error message if validation failed -->
                        <?php if ($payment_error): ?>
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                <?php echo htmlspecialchars($payment_error); ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" class="payment-form" id="paymentForm">
                            
                            <!-- Payment Methods Selection -->
                            <div class="payment-methods">
                                
                                <!-- Option 1: Cash on Delivery (COD) -->
                                <div class="payment-method-group">
                                    <label for="cod-payment-new" class="payment-option">
                                        <input id="cod-payment-new" type="radio" name="payment_method" value="cod" required>
                                        <div class="payment-method-card">
                                            <div class="payment-icon">
                                                <i class="fas fa-money-bill-wave"></i>
                                            </div>
                                            <div class="payment-info">
                                                <h3>Cash on Delivery</h3>
                                                <p>Pay when your order arrives</p>
                                            </div>
                                            <div class="checkmark">
                                                <i class="fas fa-check-circle"></i>
                                            </div>
                                        </div>
                                    </label>
                                </div>

                                <!-- Option 2: Credit/Debit Card -->
                                <div class="payment-method-group">
                                    <label for="card-payment-new" class="payment-option">
                                        <input id="card-payment-new" type="radio" name="payment_method" value="card" required>
                                        <div class="payment-method-card">
                                            <div class="payment-icon">
                                                <i class="fas fa-credit-card"></i>
                                            </div>
                                            <div class="payment-info">
                                                <h3>Credit/Debit Card</h3>
                                                <p>Visa, Mastercard, or Amex</p>
                                            </div>
                                            <div class="checkmark">
                                                <i class="fas fa-check-circle"></i>
                                            </div>
                                        </div>
                                    </label>
                                </div>

                                <!-- Option 3: Bank Transfer -->
                                <div class="payment-method-group">
                                    <label for="bank-payment-new" class="payment-option">
                                        <input id="bank-payment-new" type="radio" name="payment_method" value="bank_transfer" required>
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

                            <!-- Card Details Form (Hidden until card option is selected) -->
                            <div class="card-details" id="cardDetails" style="display: none;">
                                <h3>Card Information</h3>
                                
                                <!-- Cardholder Name -->
                                <div class="form-group">
                                    <label for="card_name">Cardholder Name</label>
                                    <input type="text" id="card_name" name="card_name" 
                                           placeholder="Name on card"
                                           autocomplete="cc-name">
                                </div>

                                <!-- Card Number -->
                                <div class="form-group">
                                    <label for="card_number">Card Number</label>
                                    <input type="text" id="card_number" name="card_number" 
                                           placeholder="1234 5678 9012 3456" 
                                           maxlength="19"
                                           autocomplete="cc-number">
                                </div>

                                <!-- Expiry Date & CVV -->
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

                            <!-- Form Buttons -->
                            <div class="form-actions">
                                <a href="shipping.php" class="btn btn-secondary">
                                    Back to Shipping
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    Place Order
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- RIGHT SECTION: Order Summary (Sticky) -->
                <div class="checkout-sidebar sticky-sidebar">
                    <div class="order-summary-sticky">
                        <h3>Order Summary</h3>
                        
                        <!-- Products List -->
                        <div class="products-list">
                            <?php foreach ($cart_items as $item): ?>
                                <div class="product-row">
                                    <div class="product-info">
                                        <p class="product-name"><?php echo htmlspecialchars($item['product_name']); ?></p>
                                        <p class="product-qty">Qty: <?php echo $item['quantity']; ?></p>
                                    </div>
                                    <p class="product-price">PKR <?php echo number_format($item['product_price'] * $item['quantity']); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Price Breakdown -->
                        <div class="sidebar-totals">
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

                        <!-- Shipping Address Summary -->
                        <?php if (isset($_SESSION['shipping_info'])): ?>
                            <div class="shipping-summary">
                                <h4>Shipping To:</h4>
                                <p>
                                    <?php echo htmlspecialchars($_SESSION['shipping_info']['first_name']); ?> 
                                    <?php echo htmlspecialchars($_SESSION['shipping_info']['last_name']); ?><br>
                                    <?php echo htmlspecialchars($_SESSION['shipping_info']['address']); ?><br>
                                    <?php echo htmlspecialchars($_SESSION['shipping_info']['city']); ?>, 
                                    <?php echo htmlspecialchars($_SESSION['shipping_info']['postal_code']); ?>
                                </p>
                                <a href="shipping.php" class="edit-link">Edit</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<!-- Cart Sidebar (Mobile Friendly) -->
<div class="cart-sidebar" id="cartSidebar">
    <div class="cart-sidebar-header">
        <h2>Your Cart</h2>
        <button class="close-btn" onclick="toggleCartSidebar()">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div class="cart-sidebar-content">
        <?php foreach ($cart_items as $item): ?>
            <div class="cart-item">
                <p class="cart-item-name"><?php echo htmlspecialchars($item['product_name']); ?></p>
                <p class="cart-item-qty">Qty: <?php echo $item['quantity']; ?></p>
                <p class="cart-item-price">PKR <?php echo number_format($item['product_price']); ?></p>
            </div>
        <?php endforeach; ?>
        <div class="cart-total">
            <p>Total:</p>
            <p class="cart-total-price">PKR <?php echo number_format($grand_total); ?></p>
        </div>
        <a href="../index.php" class="btn btn-primary btn-block">
            Back to Home
        </a>
    </div>
</div>

<!-- Overlay for Sidebar -->
<div class="cart-overlay" id="cartOverlay" onclick="toggleCartSidebar()"></div>

<!-- External JavaScript for Payment Functionality -->
<script src="../js/payment.js"></script>

<?php include '../includes/footer.php'; ?>

<link rel="stylesheet" href="../css/checkout-new.css">

<main class="checkout-container" style="margin-top: 80px;">
    <div class="container">
        <!-- Step Indicator -->
        <div class="step-indicator">
            <div class="step">
                <div class="step-number">1</div>
                <span>Order Summary</span>
            </div>
            <div class="step-line"></div>
            <div class="step">
                <div class="step-number">2</div>
                <span>Shipping</span>
            </div>
            <div class="step-line"></div>
            <div class="step active">
                <div class="step-number">3</div>
                <span>Payment</span>
            </div>
        </div>

        <!-- Back to Cart Button -->
        <button class="back-to-cart-btn" onclick="toggleCartSidebar()">
            <i class="fas fa-shopping-cart"></i> Back to Cart
        </button>

        <!-- Success Message -->
        <?php if ($payment_success): ?>
            <div class="success-container">
                <div class="success-message">
                    <div class="success-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h2>Order Placed Successfully!</h2>
                    <p>Your order has been received and is being processed.</p>
                    <p class="order-id">Order ID: <strong><?php echo sprintf('%05d', $order_id); ?></strong></p>
                    <p>A confirmation email has been sent to <strong><?php echo htmlspecialchars($shipping_info['email']); ?></strong></p>
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
            <!-- Main Content -->
            <div class="checkout-grid">
                <!-- Left Section - Payment Methods -->
                <div class="checkout-main">
                    <div class="checkout-card">
                        <h1>Payment Method</h1>

                        <?php if ($payment_error): ?>
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                <?php echo htmlspecialchars($payment_error); ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" class="payment-form" id="paymentForm">
                            <!-- Payment Methods -->
                            <div class="payment-methods">
                                <!-- COD -->
                                <div class="payment-method-group">
                                    <label for="cod-payment-new" class="payment-option">
                                        <input id="cod-payment-new" type="radio" name="payment_method" value="cod" required>
                                        <div class="payment-method-card">
                                            <div class="payment-icon">
                                                <i class="fas fa-money-bill-wave"></i>
                                            </div>
                                            <div class="payment-info">
                                                <h3>Cash on Delivery</h3>
                                                <p>Pay when your order arrives</p>
                                            </div>
                                            <div class="checkmark">
                                                <i class="fas fa-check-circle"></i>
                                            </div>
                                        </div>
                                    </label>
                                </div>

                                <!-- Credit/Debit Card -->
                                <div class="payment-method-group">
                                    <label for="card-payment-new" class="payment-option">
                                        <input id="card-payment-new" type="radio" name="payment_method" value="card" required>
                                        <div class="payment-method-card">
                                            <div class="payment-icon">
                                                <i class="fas fa-credit-card"></i>
                                            </div>
                                            <div class="payment-info">
                                                <h3>Credit/Debit Card</h3>
                                                <p>Visa, Mastercard, or Amex</p>
                                            </div>
                                            <div class="checkmark">
                                                <i class="fas fa-check-circle"></i>
                                            </div>
                                        </div>
                                    </label>
                                </div>

                                <!-- Bank Transfer -->
                                <div class="payment-method-group">
                                    <label for="bank-payment-new" class="payment-option">
                                        <input id="bank-payment-new" type="radio" name="payment_method" value="bank_transfer" required>
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

                            <!-- Form Actions -->
                            <div class="form-actions">
                                <a href="shipping.php" class="btn btn-secondary">
                                    Back to Shipping
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    Place Order
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Right Section - Order Summary (Sticky) -->
                <div class="checkout-sidebar sticky-sidebar">
                    <div class="order-summary-sticky">
                        <h3>Order Summary</h3>
                        
                        <div class="products-list">
                            <?php foreach ($cart_items as $item): ?>
                                <div class="product-row">
                                    <div class="product-info">
                                        <p class="product-name"><?php echo htmlspecialchars($item['product_name']); ?></p>
                                        <p class="product-qty">Qty: <?php echo $item['quantity']; ?></p>
                                    </div>
                                    <p class="product-price">PKR <?php echo number_format($item['product_price'] * $item['quantity']); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="sidebar-totals">
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

                        <?php if (isset($_SESSION['shipping_info'])): ?>
                            <div class="shipping-summary">
                                <h4>Shipping To:</h4>
                                <p>
                                    <?php echo htmlspecialchars($_SESSION['shipping_info']['first_name']); ?> 
                                    <?php echo htmlspecialchars($_SESSION['shipping_info']['last_name']); ?><br>
                                    <?php echo htmlspecialchars($_SESSION['shipping_info']['address']); ?><br>
                                    <?php echo htmlspecialchars($_SESSION['shipping_info']['city']); ?>, 
                                    <?php echo htmlspecialchars($_SESSION['shipping_info']['postal_code']); ?>
                                </p>
                                <a href="shipping.php" class="edit-link">Edit</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<!-- Back to Cart Sidebar -->
<div class="cart-sidebar" id="cartSidebar">
    <div class="cart-sidebar-header">
        <h2>Your Cart</h2>
        <button class="close-btn" onclick="toggleCartSidebar()">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div class="cart-sidebar-content">
        <?php foreach ($cart_items as $item): ?>
            <div class="cart-item">
                <p class="cart-item-name"><?php echo htmlspecialchars($item['product_name']); ?></p>
                <p class="cart-item-qty">Qty: <?php echo $item['quantity']; ?></p>
                <p class="cart-item-price">PKR <?php echo number_format($item['product_price']); ?></p>
            </div>
        <?php endforeach; ?>
        <div class="cart-total">
            <p>Total:</p>
            <p class="cart-total-price">PKR <?php echo number_format($grand_total); ?></p>
        </div>
        <a href="../index.php" class="btn btn-primary btn-block">
            Back to Home
        </a>
    </div>
</div>

<!-- Overlay -->
<div class="cart-overlay" id="cartOverlay" onclick="toggleCartSidebar()"></div>

<!-- External JavaScript for Payment Functionality -->
<script src="../js/payment.js"></script>

<?php include '../includes/footer.php'; ?>
