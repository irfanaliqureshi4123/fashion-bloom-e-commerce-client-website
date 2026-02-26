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

$shipping_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $city = $_POST['city'] ?? '';
    $postal_code = $_POST['postal_code'] ?? '';
    $notes = $_POST['notes'] ?? '';

    // Validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($phone) || empty($address) || empty($city) || empty($postal_code)) {
        $shipping_error = 'Please fill in all required fields.';
    } else {
        // Store shipping info in session
        $_SESSION['shipping_info'] = [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'phone' => $phone,
            'address' => $address,
            'city' => $city,
            'postal_code' => $postal_code,
            'notes' => $notes
        ];

        // Redirect to payment
        header('Location: payment.php');
        exit();
    }
}
?>

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
            <div class="step active">
                <div class="step-number">2</div>
                <span>Shipping</span>
            </div>
            <div class="step-line"></div>
            <div class="step">
                <div class="step-number">3</div>
                <span>Payment</span>
            </div>
        </div>

        <!-- Back to Cart Button -->
        <button class="back-to-cart-btn" onclick="toggleCartSidebar()">
            <i class="fas fa-shopping-cart"></i> Back to Cart
        </button>

        <!-- Main Content -->
        <div class="checkout-grid">
            <!-- Left Section - Shipping Form -->
            <div class="checkout-main">
                <div class="checkout-card">
                    <h1>Shipping Information</h1>

                    <?php if ($shipping_error): ?>
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo htmlspecialchars($shipping_error); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="shipping-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name">First Name *</label>
                                <input type="text" id="first_name" name="first_name" 
                                       value="<?php echo isset($user['first_name']) ? htmlspecialchars($user['first_name']) : ''; ?>" 
                                       autocomplete="given-name"
                                       required>
                            </div>
                            <div class="form-group">
                                <label for="last_name">Last Name *</label>
                                <input type="text" id="last_name" name="last_name" 
                                       value="<?php echo isset($user['last_name']) ? htmlspecialchars($user['last_name']) : ''; ?>" 
                                       autocomplete="family-name"
                                       required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="email">Email *</label>
                                <input type="email" id="email" name="email" 
                                       value="<?php echo isset($user['email']) ? htmlspecialchars($user['email']) : ''; ?>" 
                                       autocomplete="email"
                                       required>
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone *</label>
                                <input type="tel" id="phone" name="phone" 
                                       value="<?php echo isset($user['phone']) ? htmlspecialchars($user['phone']) : ''; ?>" 
                                       autocomplete="tel"
                                       required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="address">Address *</label>
                            <input type="text" id="address" name="address" 
                                   placeholder="Street address" 
                                   autocomplete="street-address"
                                   required>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="city">City *</label>
                                <input type="text" id="city" name="city" 
                                       autocomplete="address-level2"
                                       required>
                            </div>
                            <div class="form-group">
                                <label for="postal_code">Postal Code *</label>
                                <input type="text" id="postal_code" name="postal_code" 
                                       autocomplete="postal-code"
                                       required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="notes">Delivery Notes</label>
                            <textarea id="notes" name="notes" 
                                      placeholder="Special instructions for delivery (optional)"
                                      rows="4"></textarea>
                        </div>

                        <div class="form-actions">
                            <a href="order-summary.php" class="btn btn-secondary">
                                Back to Summary
                            </a>
                            <button type="submit" class="btn btn-primary">
                                Continue to Payment
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
                </div>
            </div>
        </div>
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

<script>
function toggleCartSidebar() {
    const sidebar = document.getElementById('cartSidebar');
    const overlay = document.getElementById('cartOverlay');
    sidebar.classList.toggle('active');
    overlay.classList.toggle('active');
}
</script>

<?php include '../includes/footer.php'; ?>
