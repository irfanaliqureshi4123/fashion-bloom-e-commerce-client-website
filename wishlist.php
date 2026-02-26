<?php 
session_start();
include 'includes/header.php';
include 'includes/db.php';

$user_id = $_SESSION['user_id'] ?? null;
$isGuest = $user_id === null;
$session_id = session_id();

// Get wishlist items from database
if ($isGuest) {
    // Guest user: get from database using session_id
    $sql = "SELECT * FROM wishlist WHERE session_id = ? ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$session_id]);
    $wishlist_items = $stmt->fetchAll();
} else {
    // Logged-in user: get from database
    $sql = "SELECT * FROM wishlist WHERE user_id = ? ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $wishlist_items = $stmt->fetchAll();
}
?>

<link rel="stylesheet" href="css/pages/wishlist.css">

<main class="wishlist-container">
    <div class="container">
        <div class="wishlist-header">
            <h1>My Wishlist</h1>
            <p class="wishlist-count"><?php echo count($wishlist_items); ?> items</p>
        </div>

        <?php if (empty($wishlist_items)): ?>
            <div class="empty-wishlist">
                <i class="fas fa-heart"></i>
                <h2>Your wishlist is empty</h2>
                <p>Start adding items you love!</p>
                <a href="index.php#products" class="btn btn-primary">
                    <i class="fas fa-shopping-bag"></i> Continue Shopping
                </a>
            </div>
        <?php else: ?>
            <div class="wishlist-items">
                <?php foreach ($wishlist_items as $item): ?>
                    <div class="wishlist-item" data-product-id="<?php echo $item['product_id']; ?>">
                        <div class="wishlist-item-image">
                            <img src="<?php echo htmlspecialchars($item['product_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($item['product_name']); ?>"
                                 onerror="this.src='assets/images/placeholder.jpg'">
                        </div>
                        <div class="wishlist-item-details">
                            <h3><?php echo htmlspecialchars($item['product_name']); ?></h3>
                            <p class="wishlist-item-category">
                                <?php echo htmlspecialchars(str_replace('_', ' ', ucfirst($item['category']))); ?>
                            </p>
                            <p class="wishlist-item-date">
                                Added: <?php echo date('M d, Y', strtotime($item['created_at'])); ?>
                            </p>
                            <p class="wishlist-item-price">PKR <?php echo number_format($item['product_price']); ?></p>
                        </div>
                        <div class="wishlist-item-actions">
                            <button class="btn btn-primary add-to-cart-from-wishlist" 
                                    data-product-id="<?php echo $item['product_id']; ?>"
                                    data-product-name="<?php echo htmlspecialchars($item['product_name']); ?>"
                                    data-product-price="<?php echo $item['product_price']; ?>"
                                    data-product-image="<?php echo htmlspecialchars($item['product_image']); ?>"
                                    data-category="<?php echo htmlspecialchars($item['category']); ?>">
                                <i class="fas fa-shopping-cart"></i> Add to Cart
                            </button>
                            <button class="btn btn-danger remove-from-wishlist" 
                                    data-product-id="<?php echo $item['product_id']; ?>">
                                <i class="fas fa-trash"></i> Remove
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<!-- Cart Overlay -->
<div class="cart-overlay" id="cart-overlay"></div>

<!-- Cart Sidebar -->
<div class="cart-sidebar" id="cart-sidebar">
    <div class="cart-header">
        <h3>Shopping Cart</h3>
        <button class="close-cart" id="close-cart">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div class="cart-items" id="cart-items">
        <div class="cart-empty">
            <i class="fas fa-shopping-cart"></i>
            <p>Your cart is empty</p>
            <p style="font-size: 0.85rem; color: var(--text-light);">Add items to get started</p>
        </div>
    </div>
    <div class="cart-footer">
        <div class="cart-total">
            <span class="cart-total-label">Total:</span>
            <span class="cart-total-amount">PKR <span id="cart-total">0</span></span>
        </div>
        <button class="checkout-btn" onclick="checkout()">Checkout</button>
    </div>
</div>

<script src="/js/main.js"></script>
<script src="/js/wishlist.js"></script>

<?php include 'includes/footer.php'; ?>
