<?php 
session_start();
include 'includes/header.php';
include 'includes/db.php';

$user_id = $_SESSION['user_id'] ?? null;
$order_id = $_GET['order_id'] ?? null;

// Redirect to login if not logged in
if (!$user_id) {
    header('Location: login.php');
    exit();
}

// Redirect if no order ID provided
if (!$order_id) {
    header('Location: index.php');
    exit();
}

// Get order details
$sql_order = "SELECT * FROM orders WHERE id = ? AND user_id = ?";
$stmt_order = $pdo->prepare($sql_order);
$stmt_order->execute([$order_id, $user_id]);
$order = $stmt_order->fetch();

// Redirect if order not found
if (!$order) {
    header('Location: index.php');
    exit();
}

// Get order items
$sql_items = "SELECT * FROM order_items WHERE order_id = ?";
$stmt_items = $pdo->prepare($sql_items);
$stmt_items->execute([$order_id]);
$order_items = $stmt_items->fetchAll();

// Payment method labels
$payment_methods = [
    'cod' => 'Cash on Delivery',
    'card' => 'Credit Card',
    'bank' => 'Bank Transfer'
];
?>

<link rel="stylesheet" href="/css/pages/checkout-shared.css">
<link rel="stylesheet" href="/css/confirmation.css">

<main class="confirmation-page">
    <div class="container">
        <!-- Progress Indicator -->
        <div class="checkout-progress" style="--progress-width: 100%; margin-top: 2rem;">
            <div class="progress-step completed">
                <div class="progress-step-circle">
                    <i class="fas fa-check"></i>
                </div>
                <span class="progress-step-label">Cart</span>
            </div>
            <div class="progress-step completed">
                <div class="progress-step-circle">
                    <i class="fas fa-check"></i>
                </div>
                <span class="progress-step-label">Shipping</span>
            </div>
            <div class="progress-step completed">
                <div class="progress-step-circle">
                    <i class="fas fa-check"></i>
                </div>
                <span class="progress-step-label">Payment</span>
            </div>
            <div class="progress-step completed">
                <div class="progress-step-circle">
                    <i class="fas fa-check"></i>
                </div>
                <span class="progress-step-label">Confirmation</span>
            </div>
        </div>

        <!-- Success Header -->
        <div class="success-header">
            <div class="success-icon-wrapper">
                <div class="success-icon">
                    <i class="fas fa-check"></i>
                </div>
            </div>
            <h1>Order Confirmed!</h1>
            <p>Thank you for your order! We'll send you shipping updates at <strong><?php echo htmlspecialchars($order['email']); ?></strong></p>
        </div>

        <!-- Order Details Card -->
        <div class="order-card">
            <div class="order-header">
                <div class="order-info">
                    <h2>Order #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></h2>
                    <p class="order-date"><?php echo date('F d, Y', strtotime($order['created_at'])); ?></p>
                </div>
                <div class="order-status">
                    <span class="status-badge status-<?php echo $order['status']; ?>">
                        <?php echo ucfirst($order['status']); ?>
                    </span>
                </div>
            </div>

            <!-- Order Items -->
            <div class="order-items">
                <h3>Items Ordered</h3>
                <?php foreach ($order_items as $item): ?>
                    <div class="order-item">
                        <div class="item-info">
                            <h4><?php echo htmlspecialchars($item['product_name']); ?></h4>
                            <p class="item-category"><?php echo ucfirst($item['category']); ?></p>
                        </div>
                        <div class="item-details">
                            <span class="item-qty">Qty: <?php echo $item['quantity']; ?></span>
                            <span class="item-price">PKR <?php echo number_format($item['product_price'] * $item['quantity']); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Order Summary -->
            <div class="order-summary">
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>PKR <?php echo number_format($order['subtotal']); ?></span>
                </div>
                <div class="summary-row">
                    <span>Shipping</span>
                    <span>Free</span>
                </div>
                <div class="summary-row">
                    <span>Tax</span>
                    <span>PKR <?php echo number_format($order['tax']); ?></span>
                </div>
                <div class="summary-row total">
                    <span>Total</span>
                    <span>PKR <?php echo number_format($order['total_price']); ?></span>
                </div>
            </div>
        </div>

        <!-- Shipping & Payment Info -->
        <div class="info-grid">
            <div class="info-card">
                <h3>Shipping Address</h3>
                <div class="address-info">
                    <p><strong><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></strong></p>
                    <p><?php echo htmlspecialchars($order['address']); ?></p>
                    <p><?php echo htmlspecialchars($order['city'] . ', ' . $order['postal_code']); ?></p>
                    <p><?php echo htmlspecialchars($order['phone']); ?></p>
                </div>
            </div>

            <div class="info-card">
                <h3>Payment Method</h3>
                <p><?php echo $payment_methods[$order['payment_method']] ?? ucfirst($order['payment_method']); ?></p>
                <?php if ($order['payment_status'] === 'completed'): ?>
                    <span class="payment-status paid">✓ Paid</span>
                <?php else: ?>
                    <span class="payment-status pending">⏳ <?php echo ucfirst($order['payment_status']); ?></span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="index.php" class="btn btn-primary">Continue Shopping</a>
            <a href="profile.php" class="btn btn-secondary">View All Orders</a>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
