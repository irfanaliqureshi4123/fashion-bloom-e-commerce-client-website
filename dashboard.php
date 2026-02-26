<?php
session_start();

// Protect dashboard
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include "includes/db.php";

$user_id = $_SESSION['user_id'];

// Fetch user data and statistics
try {
    $stmt = $pdo->prepare("SELECT first_name, last_name, email, phone, created_at FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception("User not found");
    }

    // Get total orders (excluding cancelled) with sum of spending (only paid orders)
    $stmt = $pdo->prepare("SELECT COUNT(*) as count, SUM(total_price) as total_spent FROM orders WHERE user_id = ? AND status != 'cancelled'");
    $stmt->execute([$user_id]);
    $orders = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Handle null values
    $orders['count'] = $orders['count'] ?? 0;
    $orders['total_spent'] = $orders['total_spent'] ?? 0;

    // Get total items in cart (sum of quantities)
    $stmt = $pdo->prepare("SELECT SUM(quantity) as total_items, COUNT(*) as unique_items FROM shopping_cart WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $cart = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Handle null values
    $cart['total_items'] = $cart['total_items'] ?? 0;
    $cart['unique_items'] = $cart['unique_items'] ?? 0;

    // Get wishlist items count
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM wishlist WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $wishlist = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Handle null values
    $wishlist['count'] = $wishlist['count'] ?? 0;

    // Get recent orders with status and updated_at (excluding cancelled orders from recent view)
    $stmt = $pdo->prepare("SELECT id, order_number, total_price, created_at, updated_at, status, payment_status FROM orders WHERE user_id = ? ORDER BY updated_at DESC LIMIT 5");
    $stmt->execute([$user_id]);
    $recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Handle empty result
    if (!$recent_orders) {
        $recent_orders = [];
    }

    // Get frequently purchased categories (only from non-cancelled orders)
    $stmt = $pdo->prepare("SELECT category, COUNT(*) as purchase_count FROM order_items WHERE order_id IN (SELECT id FROM orders WHERE user_id = ? AND status != 'cancelled') GROUP BY category ORDER BY purchase_count DESC LIMIT 3");
    $stmt->execute([$user_id]);
    $favorite_categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Handle empty result
    if (!$favorite_categories) {
        $favorite_categories = [];
    }

} catch (PDOException $e) {
    // Log database errors silently or display generic message
    // Database error occurred, using default empty values
} catch (Exception $e) {
    // Generic exception handling
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Fashion Bloom</title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/pages/dashboard.css">
</head>
<body>
   
<?php include "includes/header.php"; ?>
    <!-- Mobile Menu Toggle Button -->
    <button class="mobile-toggle" id="sidebarToggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <div class="dashboard-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-brand">
                    <a href="index.php" class="dashboard-logo-link">
                        <img src="/assets/images/fashion_bloom_logo.png" alt="Fashion Bloom" class="dashboard-logo-image">
                    </a>
                </div>
            </div>

            <ul class="sidebar-nav">
                <li class="sidebar-nav-item">
                    <a href="dashboard.php" class="sidebar-nav-link active">
                        <i class="fas fa-home"></i>
                        Dashboard
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="profile.php" class="sidebar-nav-link">
                        <i class="fas fa-user"></i>
                        My Profile
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="wishlist.php" class="sidebar-nav-link">
                        <i class="fas fa-heart"></i>
                        Wishlist
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="index.php#products" class="sidebar-nav-link">
                        <i class="fas fa-shopping-bag"></i>
                        Shop
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="about.php" class="sidebar-nav-link">
                        <i class="fas fa-info-circle"></i>
                        About
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="contact.php" class="sidebar-nav-link">
                        <i class="fas fa-envelope"></i>
                        Contact
                    </a>
                </li>
            </ul>

            <div class="sidebar-footer">
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Back Button -->
            <div class="back-button-wrapper">
                <a href="index.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Back to Home
                </a>
            </div>

            <!-- Welcome Message (if just logged in) -->
            <?php if (isset($_SESSION['dashboard_success'])): ?>
                <div class="welcome-message">
                    <h2>
                        <i class="fas fa-smile-wink"></i>
                        <?= htmlspecialchars($_SESSION['dashboard_success']); ?>
                    </h2>
                    <p>Your dashboard is ready. Let's explore Fashion Bloom!</p>
                </div>
                <?php unset($_SESSION['dashboard_success']); ?>
            <?php endif; ?>

            <!-- Header Section -->
            <div class="header-section">
                <div class="header-content">
                    <h1>Dashboard</h1>
                    <p>Welcome to your personal shopping hub</p>
                </div>
                <div class="header-actions">
                    <a href="index.php#products" class="action-btn primary">
                        <i class="fas fa-shopping-bag"></i> Continue Shopping
                    </a>
                    <a href="profile.php" class="action-btn secondary">
                        <i class="fas fa-edit"></i> Edit Profile
                    </a>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <div class="stat-label">Total Orders</div>
                    <div class="stat-value"><?= $orders['count'] ?? 0; ?></div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-coins"></i>
                    </div>
                    <div class="stat-label">Total Spent</div>
                    <div class="stat-value">PKR <?= $orders['total_spent'] ? number_format($orders['total_spent'], 0) : 0; ?></div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-label">Cart Items</div>
                    <div class="stat-value"><?= $cart['total_items'] ?? 0; ?></div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <div class="stat-label">Wishlist</div>
                    <div class="stat-value"><?= $wishlist['count'] ?? 0; ?></div>
                </div>
            </div>

            <!-- Recent Orders Section -->
            <div class="content-section">
                <h3 class="section-title">
                    <i class="fas fa-history"></i>
                    Recent Orders
                </h3>
                <?php if (!empty($recent_orders)): ?>
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Amount</th>
                                <th>Order Status</th>
                                <th>Payment Status</th>
                                <th>Last Updated</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_orders as $order): ?>
                                <tr>
                                    <td>
                                        <span class="order-number"><?= htmlspecialchars($order['order_number']); ?></span>
                                    </td>
                                    <td>PKR <?= number_format($order['total_price'], 0); ?></td>
                                    <td>
                                        <span class="status-badge status-<?= strtolower($order['status']); ?>">
                                            <?= ucfirst($order['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?= strtolower($order['payment_status']); ?>">
                                            <?= ucfirst($order['payment_status']); ?>
                                        </span>
                                    </td>
                                    <td><?= date('M d, Y g:i A', strtotime($order['updated_at'] ?? $order['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-inbox"></i>
                        </div>
                        <div class="empty-state-text">No Orders Yet</div>
                        <p class="empty-state-subtext">Start shopping to see your order history here</p>
                        <a href="index.php#products" class="action-btn primary" style="display: inline-flex; margin-top: 15px;">
                            <i class="fas fa-shopping-bag"></i> Start Shopping
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Favorite Categories -->
            <?php if (!empty($favorite_categories)): ?>
                <div class="content-section">
                    <h3 class="section-title">
                        <i class="fas fa-star"></i>
                        Your Favorite Categories
                    </h3>
                    <div class="quick-actions">
                        <?php foreach ($favorite_categories as $cat): ?>
                            <a href="index.php#products" class="quick-action-card">
                                <div class="quick-action-icon">
                                    <i class="fas fa-tag"></i>
                                </div>
                                <div class="quick-action-label"><?= htmlspecialchars($cat['category']); ?></div>
                                <small style="color: #888; font-size: 0.8rem;"><?= $cat['purchase_count']; ?> purchases</small>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Quick Actions -->
            <div class="content-section">
                <h3 class="section-title">
                    <i class="fas fa-zap"></i>
                    Quick Actions
                </h3>
                <div class="quick-actions">
                    <a href="profile.php" class="quick-action-card">
                        <div class="quick-action-icon">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <div class="quick-action-label">My Profile</div>
                    </a>

                    <a href="cart.php" class="quick-action-card">
                        <div class="quick-action-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="quick-action-label">View Cart</div>
                    </a>

                    <a href="wishlist.php" class="quick-action-card">
                        <div class="quick-action-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <div class="quick-action-label">Wishlist</div>
                    </a>

                    <a href="index.php#products" class="quick-action-card">
                        <div class="quick-action-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <div class="quick-action-label">Browse Products</div>
                    </a>
                </div>
            </div>

            <!-- Empty Cart Suggestion -->
            <?php if ($cart['total_items'] == 0): ?>
                <div class="content-section" style="text-align: center;">
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                        <div class="empty-state-text">Your Cart is Empty!</div>
                        <p class="empty-state-subtext">Discover our amazing collection of premium accessories and start adding to your cart.</p>
                        <a href="index.php#products" class="action-btn primary" style="display: inline-flex; margin-top: 15px;">
                            <i class="fas fa-shopping-bag"></i> Start Shopping Now
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Help Banner -->
            <div class="help-banner">
                <div class="help-banner-icon">
                    <i class="fas fa-lightbulb"></i>
                </div>
                <div class="help-banner-content">
                    <h4>Welcome to Fashion Bloom!</h4>
                    <p>Need assistance? Visit our <a href="about.php">About page</a> for information or <a href="contact.php">Contact us</a> for dedicated support.</p>
                </div>
            </div>
        </main>
    </div>

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
    <script>
        // Responsive sidebar toggle for mobile
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const toggle = document.getElementById('sidebarToggle');
            
            if (sidebar) {
                sidebar.classList.toggle('active');
                
                // Change icon when sidebar is open
                if (sidebar.classList.contains('active')) {
                    toggle.innerHTML = '<i class="fas fa-times"></i>';
                    toggle.style.left = 'auto';
                    toggle.style.right = '15px';
                } else {
                    toggle.innerHTML = '<i class="fas fa-bars"></i>';
                    toggle.style.left = '15px';
                    toggle.style.right = 'auto';
                }
            }
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            const sidebar = document.querySelector('.sidebar');
            const toggle = document.getElementById('sidebarToggle');
            
            if (window.innerWidth < 768 && sidebar && sidebar.classList.contains('active')) {
                if (!sidebar.contains(e.target) && !toggle.contains(e.target)) {
                    sidebar.classList.remove('active');
                    toggle.innerHTML = '<i class="fas fa-bars"></i>';
                    toggle.style.left = '15px';
                    toggle.style.right = 'auto';
                }
            }
        });

        // Close sidebar when clicking a link on mobile
        document.querySelectorAll('.sidebar-nav-link').forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth < 768) {
                    const sidebar = document.querySelector('.sidebar');
                    const toggle = document.getElementById('sidebarToggle');
                    
                    sidebar.classList.remove('active');
                    toggle.innerHTML = '<i class="fas fa-bars"></i>';
                    toggle.style.left = '15px';
                    toggle.style.right = 'auto';
                }
            });
        });

        // Handle window resize
        window.addEventListener('resize', function() {
            const sidebar = document.querySelector('.sidebar');
            const toggle = document.getElementById('sidebarToggle');
            
            if (window.innerWidth >= 768) {
                sidebar.classList.remove('active');
                toggle.style.display = 'none';
            } else {
                toggle.style.display = 'block';
            }
        });
    </script>
</body>
</html>
