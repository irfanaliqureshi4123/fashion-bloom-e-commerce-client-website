<?php 
session_start();
include 'includes/header.php';
include 'includes/db.php';

$user_id = $_SESSION['user_id'] ?? null;

// Redirect to login if not logged in
if (!$user_id) {
    header('Location: login.php');
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
?>

<link rel="stylesheet" href="css/pages/checkout-shared.css">
<link rel="stylesheet" href="css/pages/cart.css">

<main class="cart-container" style="margin-top: 80px;">
    <div class="container">
        <!-- Progress Indicator -->
        <div class="checkout-progress" style="--progress-width: 0%;">
            <div class="progress-step active">
                <div class="progress-step-circle">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <span class="progress-step-label">Cart</span>
            </div>
            <div class="progress-step">
                <div class="progress-step-circle">2</div>
                <span class="progress-step-label">Shipping</span>
            </div>
            <div class="progress-step">
                <div class="progress-step-circle">3</div>
                <span class="progress-step-label">Payment</span>
            </div>
            <div class="progress-step">
                <div class="progress-step-circle">4</div>
                <span class="progress-step-label">Confirmation</span>
            </div>
        </div>

        <div class="cart-header">
            <h1>Shopping Cart</h1>
            <p class="cart-count"><?php echo count($cart_items); ?> items</p>
        </div>

        <?php if (empty($cart_items)): ?>
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <h2>Your cart is empty</h2>
                <p>Add some products to get started!</p>
                <a href="index.php#products" class="btn-modern btn-modern-primary">Continue Shopping</a>
            </div>
        <?php else: ?>
            <div class="cart-layout">
                <!-- Cart Items -->
                <div class="cart-items-section">
                    <div class="cart-items-list">
                        <?php foreach ($cart_items as $item): ?>
                            <div class="cart-item" data-product-id="<?php echo $item['product_id']; ?>">
                                <div class="cart-item-image">
                                    <img src="<?php echo htmlspecialchars($item['product_image']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['product_name']); ?>"
                                         onerror="this.src='assets/images/placeholder.jpg'">
                                </div>
                                <div class="cart-item-details">
                                    <h3><?php echo htmlspecialchars($item['product_name']); ?></h3>
                                    <p class="cart-item-category">
                                        <?php echo htmlspecialchars(str_replace('_', ' ', ucfirst($item['category']))); ?>
                                    </p>
                                    <p class="cart-item-price">PKR <?php echo number_format($item['product_price']); ?></p>
                                </div>
                                <div class="cart-item-quantity">
                                    <label for="qty-<?php echo $item['product_id']; ?>">Quantity:</label>
                                    <div class="quantity-control">
                                        <button class="qty-minus" data-product-id="<?php echo $item['product_id']; ?>">−</button>
                                        <input type="number" id="qty-<?php echo $item['product_id']; ?>" value="<?php echo $item['quantity']; ?>" 
                                               class="qty-input" data-product-id="<?php echo $item['product_id']; ?>" min="1">
                                        <button class="qty-plus" data-product-id="<?php echo $item['product_id']; ?>">+</button>
                                    </div>
                                </div>
                                <div class="cart-item-total">
                                    <span class="item-total">PKR <?php echo number_format($item['product_price'] * $item['quantity']); ?></span>
                                </div>
                                <div class="cart-item-actions">
                                    <button class="remove-btn" data-product-id="<?php echo $item['product_id']; ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Cart Summary Below Items -->
            <div class="cart-summary-below">
                <div class="summary-card">
                    <h2>Order Summary</h2>
                    
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span id="subtotal">PKR <?php echo number_format($total); ?></span>
                    </div>
                    
                    <div class="summary-row">
                        <span>Shipping:</span>
                        <span id="shipping">Free</span>
                    </div>
                    
                    <div class="summary-row">
                        <span>Tax:</span>
                        <span id="tax">Calculated at checkout</span>
                    </div>
                    
                    <div class="summary-divider"></div>
                    
                    <div class="summary-total">
                        <span>Total:</span>
                        <span id="cart-total">PKR <?php echo number_format($total); ?></span>
                    </div>

                    <button class="checkout-btn btn-modern btn-modern-primary" onclick="proceedToCheckout()">
                        <i class="fas fa-lock"></i> Proceed to Checkout
                    </button>
                    <a href="index.php#products" class="continue-shopping-btn">
                        <i class="fas fa-arrow-left"></i> Continue Shopping
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<script>
function proceedToCheckout() {
    window.location.href = 'checkout.php';
}

// Handle quantity changes
document.querySelectorAll('.qty-plus').forEach(btn => {
    btn.addEventListener('click', function() {
        const productId = this.dataset.productId;
        const input = document.querySelector(`.qty-input[data-product-id="${productId}"]`);
        input.value = parseInt(input.value) + 1;
        updateCartItem(productId, parseInt(input.value));
    });
});

document.querySelectorAll('.qty-minus').forEach(btn => {
    btn.addEventListener('click', function() {
        const productId = this.dataset.productId;
        const input = document.querySelector(`.qty-input[data-product-id="${productId}"]`);
        if (parseInt(input.value) > 1) {
            input.value = parseInt(input.value) - 1;
            updateCartItem(productId, parseInt(input.value));
        }
    });
});

// Handle quantity input change
document.querySelectorAll('.qty-input').forEach(input => {
    input.addEventListener('change', function() {
        const productId = this.dataset.productId;
        const quantity = parseInt(this.value);
        if (quantity > 0) {
            updateCartItem(productId, quantity);
        }
    });
});

// Handle remove item
document.querySelectorAll('.remove-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const productId = this.dataset.productId;
        removeCartItem(productId);
    });
});

function updateCartItem(productId, quantity) {
    const formData = new FormData();
    formData.append('action', 'update');
    formData.append('product_id', productId);
    formData.append('quantity', quantity);

    fetch('includes/cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => {});
}

function removeCartItem(productId) {
    if (confirm('Remove this item from cart?')) {
        const formData = new FormData();
        formData.append('action', 'remove');
        formData.append('product_id', productId);

        fetch('includes/cart.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        })
        .catch(error => {});
    }
}
</script>

<?php include 'includes/footer.php'; ?>
