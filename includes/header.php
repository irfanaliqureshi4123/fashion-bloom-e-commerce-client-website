<!-- header.php -->
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#d4af37">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>Fashion Bloom - Premium Fashion Accessories</title>
    <link rel="icon" type="image/png" href="favicon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <script src="js/cart-utils.js"></script>
    <script src="js/search.js"></script>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <a href="index.php" class="logo-link">
                    <img src="assets/images/fashion_bloom_logo.png" alt="Fashion Bloom" class="logo-image">
                </a>
            </div>
            
            <!-- Hamburger Menu Button -->
            <div class="hamburger" id="hamburger">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>
            
            <ul class="nav-menu" id="nav-menu">
                <li class="nav-item">
                    <a href="index.php" class="nav-link active">Home</a>
                </li>
                <li class="nav-item">
                    <a href="index.php#products" class="nav-link">Products</a>
                </li>
                <li class="nav-item">
                    <a href="about.php" class="nav-link">About</a>
                </li>
                <li class="nav-item">
                    <a href="contact.php" class="nav-link">Contact</a>
                </li>
            </ul>

            <!-- Search Bar -->
            <div class="search-container">
                <input 
                    type="text" 
                    class="search-input" 
                    id="product-search" 
                    placeholder="Search products..."
                    autocomplete="off"
                >
                <i class="fas fa-search search-icon"></i>
                <div class="search-dropdown" id="search-dropdown"></div>
            </div>
            <div class="nav-actions">
                <a href="wishlist.php" class="nav-icon wishlist-link" title="Wishlist">
                    <i class="fas fa-heart"></i>
                    <span class="wishlist-count badge"></span>
                </a>
                <a href="cart.php" class="nav-icon cart-icon" id="cart-toggle" title="Shopping Cart">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count badge">0</span>
                </a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="user-info">
                        <span class="user-greeting">
                            <i class="fas fa-user-circle"></i>
                            <?= htmlspecialchars($_SESSION['first_name']); ?>
                        </span>
                    </div>
                    <a href="dashboard.php" class="nav-dashboard-btn">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="logout.php" class="nav-logout-btn" title="Logout">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                <?php else: ?>
                    <a href="login.php" class="btn">Login</a>
                    <a href="register.php" class="btn btn-primary">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Overlay for mobile menu -->
    <div class="overlay" id="overlay"></div>
