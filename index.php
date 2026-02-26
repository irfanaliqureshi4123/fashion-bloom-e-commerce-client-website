<?php include 'includes/header.php'; ?>
<link rel="stylesheet" href="css/product.css">
<link rel="stylesheet" href="css/pages/reviews.css">
<link rel="stylesheet" href="css/homepage.css">
<link rel="stylesheet" href="css/pages/hero.css">
<!-- Hero Section -->
<section class="hero" id="home">
    <div class="container">
        <div class="hero-content">
            <div class="hero-text">
                <h1 class="hero-title">Premium Fashion Accessories</h1>
                <p class="hero-subtitle">Discover our exclusive collection of watches, bracelets, and jewelry crafted with precision and style.</p>
                <div class="hero-buttons">
                    <a href="#products" class="btn btn-primary">Shop Now</a>
                    <a href="about.php" class="btn btn-outline">Learn More</a>
                </div>
            </div>
            <div class="hero-img">
                <img src="assets/images/hero-banner.png" width="500" alt="Hero Image">
            </div>
        </div>
    </div>
</section>

<!-- Product Categories Filter -->
<section class="section" id="products">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Our Product Collection</h2>
            <p class="section-subtitle">Explore our handpicked collection of premium fashion accessories</p>
        </div>
        
        <!-- Category Filter Buttons -->
        <div class="category-filter">
            <button class="filter-btn active" data-category="all">All Products</button>
            <button class="filter-btn" data-category="bracelets">Bracelets</button>
            <button class="filter-btn" data-category="digital_watches">Digital Watches</button>
            <button class="filter-btn" data-category="normal_watches">Watches</button>
            <button class="filter-btn" data-category="gold_chains">Gold Chains</button>
            <button class="filter-btn" data-category="silver_chains">Silver Chains</button>
        </div>
        
        <div class="products-grid" id="products-grid">
            <!-- Products will be loaded here by JavaScript -->
        </div>
    </div>
</section>

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

<!-- Checkout Modal -->
<div class="modal" id="checkout-modal">
    <div class="modal-content">
        <div class="modal-header">
            <i class="fas fa-check-circle"></i>
            <h3>Order Placed Successfully!</h3>
        </div>
        <div class="modal-body">
            <p>Thank you for your purchase! Your order has been received and will be processed shortly.</p>
            <p><strong>Order ID:</strong> FB-<span id="order-id"></span></p>
        </div>
        <div class="modal-footer">
            <button class="modal-close" onclick="closeModal()">Close</button>
        </div>
    </div>
</div>

<!-- Overlay -->
<div class="overlay" id="overlay"></div>

<!-- FAQ Section -->
<section class="faq-section">
    <div class="container">
        <h2>Frequently Asked Questions</h2>
        <div class="faq-grid">
            <!-- FAQ items preserved from your HTML -->
            <div class="faq-item"><div class="faq-question"><h3>How can I track my order?</h3><i class="fas fa-plus"></i></div><div class="faq-answer"><p>Your tracking number will be sent via email after shipping.</p></div></div>
            <div class="faq-item"><div class="faq-question"><h3>What is your return policy?</h3><i class="fas fa-plus"></i></div><div class="faq-answer"><p>Returns are accepted within 30 days of purchase.</p></div></div>
            <div class="faq-item"><div class="faq-question"><h3>Do you offer international shipping?</h3><i class="fas fa-plus"></i></div><div class="faq-answer"><p>International shipping is coming soon.</p></div></div>
            <div class="faq-item"><div class="faq-question"><h3>What payment methods do you accept?</h3><i class="fas fa-plus"></i></div><div class="faq-answer"><p>We accept all major payment methods.</p></div></div>
        </div>
    </div>
</section>

<!-- Map Section -->
<section class="map-section">
    <div class="container">
        <h2>Find Us</h2>
        <div class="map-container">
            <div class="map-placeholder">
                <i class="fas fa-map-marked-alt"></i>
                <h3>Interactive Map</h3>
                <p>123 Fashion Street, Karachi</p>
                <a href="https://share.google/xOg4E5pRiiMJvQOdr" target="_blank" class="map-link"><i class="fas fa-external-link-alt"></i> Open in Google Maps</a>
            </div>
            <div class="location-info">
                <h3>Visit Our Showroom</h3>
                <p>Experience our products firsthand at our flagship store in Karachi.</p>
                <div class="location-features">
                    <div class="location-feature"><i class="fas fa-parking"></i><span>Free Parking Available</span></div>
                    <div class="location-feature"><i class="fas fa-wheelchair"></i><span>Wheelchair Accessible</span></div>
                    <div class="location-feature"><i class="fas fa-wifi"></i><span>Free WiFi</span></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Social Section -->
<section class="social-section">
    <div class="container">
        <h2>Connect With Us</h2>
        <p>Follow us for latest updates and offers</p>
        <div class="social-grid">
            <a href="www.facebook.com" class="social-card facebook"><i class="fab fa-facebook-f"></i><div><h3>Facebook</h3><p>Daily updates and news</p></div></a>
            <a href="www.instagram.com" class="social-card instagram"><i class="fab fa-instagram"></i><div><h3>Instagram</h3><p>Style inspiration</p></div></a>
            <a href="www.twitter.com" class="social-card twitter"><i class="fab fa-twitter"></i><div><h3>X(twitter)</h3><p>News & support</p></div></a>
            <a href="www.linkedin.com" class="social-card linkedin"><i class="fab fa-linkedin-in"></i><div><h3>LinkedIn</h3><p>Business connections</p></div></a>
        </div>
    </div>
</section>


<script src="js/product.js"></script>
<script src="js/main.js"></script>
<script src="js/reviews.js"></script>
<?php include 'includes/footer.php'; ?>