<?php 
include 'includes/header.php'; 

// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Product data (same as in product.js for now - we'll fetch it from JS)
// In a real app, this would come from a database
?>

<link rel="stylesheet" href="css/pages/reviews.css">
<style>
    .product-detail-page {
        max-width: 1400px;
        margin: 80px auto 50px;
        padding: 0 20px;
    }

    .breadcrumb-nav {
        display: flex;
        gap: 10px;
        margin-bottom: 30px;
        font-size: 14px;
        color: #666;
    }

    .breadcrumb-nav a {
        color: #e91e63;
        text-decoration: none;
        transition: color 0.3s;
    }

    .breadcrumb-nav a:hover {
        color: #c2185b;
        text-decoration: underline;
    }

    .breadcrumb-nav span {
        color: #999;
    }

    .product-detail-wrapper {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 50px;
        margin-bottom: 60px;
        align-items: start;
    }

    /* Product Gallery Section */
    .product-gallery {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .product-main-image {
        width: 100%;
        aspect-ratio: 1;
        background: linear-gradient(135deg, #f5f5f5 0%, #e8e8e8 100%);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        position: sticky;
        top: 100px;
    }

    .product-main-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .product-main-image:hover img {
        transform: scale(1.05);
    }

    .product-thumbnails {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .product-thumbnail {
        width: 90px;
        height: 90px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        cursor: pointer;
        overflow: hidden;
        transition: all 0.3s;
        flex-shrink: 0;
    }

    .product-thumbnail:hover,
    .product-thumbnail.active {
        border-color: #e91e63;
        transform: scale(1.05);
    }

    .product-thumbnail img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* Product Info Section */
    .product-info-section {
        padding-right: 20px;
    }

    .product-header {
        margin-bottom: 20px;
    }

    .product-title {
        font-size: 32px;
        font-weight: 700;
        color: #333;
        margin-bottom: 10px;
        line-height: 1.3;
    }

    .product-category {
        color: #999;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 15px;
    }

    .product-rating-bar {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 20px;
        padding-bottom: 20px;
        border-bottom: 1px solid #e0e0e0;
    }

    .stars-display {
        display: flex;
        gap: 3px;
        font-size: 18px;
        color: #ffc107;
    }

    .stars-display .star {
        color: #e0e0e0;
    }

    .stars-display .star.active {
        color: #ffc107;
    }

    .rating-info {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #666;
        font-size: 14px;
    }

    .rating-info-value {
        font-weight: 600;
        color: #333;
    }

    .product-price-section {
        margin-bottom: 30px;
    }

    .product-price {
        font-size: 36px;
        font-weight: 700;
        color: #e91e63;
        margin-bottom: 10px;
    }

    .price-note {
        font-size: 13px;
        color: #999;
    }

    .product-description {
        color: #666;
        line-height: 1.6;
        margin-bottom: 30px;
        font-size: 15px;
    }

    .product-features {
        margin-bottom: 30px;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 8px;
    }

    .feature-item {
        display: flex;
        gap: 12px;
        margin-bottom: 12px;
        font-size: 14px;
        color: #666;
    }

    .feature-item:last-child {
        margin-bottom: 0;
    }

    .feature-icon {
        color: #e91e63;
        font-size: 18px;
        min-width: 20px;
    }

    .product-actions {
        display: flex;
        gap: 15px;
        margin-bottom: 30px;
        flex-wrap: wrap;
    }

    .quantity-selector {
        display: flex;
        align-items: center;
        gap: 10px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        padding: 8px 12px;
        width: fit-content;
    }

    .qty-btn {
        background: none;
        border: none;
        cursor: pointer;
        font-size: 18px;
        color: #333;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: color 0.3s;
    }

    .qty-btn:hover {
        color: #e91e63;
    }

    .qty-input {
        width: 50px;
        text-align: center;
        border: none;
        font-size: 16px;
        font-weight: 600;
    }

    .qty-input:focus {
        outline: none;
    }

    .add-to-cart-btn {
        flex: 1;
        min-width: 200px;
        padding: 14px 30px;
        background: linear-gradient(135deg, #e91e63 0%, #c2185b 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

    .add-to-cart-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(233, 30, 99, 0.3);
    }

    .wishlist-add-btn {
        padding: 14px 30px;
        background: white;
        color: #e91e63;
        border: 2px solid #e91e63;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

    .wishlist-add-btn:hover {
        background: #e91e63;
        color: white;
    }

    .wishlist-add-btn.active {
        background: #e91e63;
        color: white;
    }

    /* Reviews Section */
    .reviews-section {
        display: grid;
        grid-template-columns: 1fr;
        gap: 40px;
        margin-top: 80px;
        padding-top: 40px;
        border-top: 2px solid #e0e0e0;
    }

    .reviews-header {
        font-size: 28px;
        font-weight: 700;
        color: #333;
        margin-bottom: 30px;
    }

    .reviews-container {
        display: flex;
        flex-direction: column;
        gap: 40px;
    }

    /* Responsive Design */
    @media (max-width: 1024px) {
        .product-detail-wrapper {
            grid-template-columns: 1fr;
            gap: 40px;
        }

        .product-info-section {
            padding-right: 0;
        }

        .product-main-image {
            position: relative;
            top: auto;
        }

        .product-title {
            font-size: 28px;
        }

        .product-price {
            font-size: 32px;
        }

        .add-to-cart-btn {
            min-width: auto;
        }
    }

    @media (max-width: 768px) {
        .product-detail-page {
            margin: 70px auto 30px;
            padding: 0 15px;
        }

        .product-title {
            font-size: 24px;
        }

        .product-price {
            font-size: 28px;
        }

        .product-actions {
            flex-direction: column;
        }

        .add-to-cart-btn,
        .wishlist-add-btn {
            min-width: 100%;
        }

        .product-thumbnails {
            gap: 8px;
        }

        .product-thumbnail {
            width: 70px;
            height: 70px;
        }
    }

    @media (max-width: 480px) {
        .product-detail-page {
            margin: 60px auto 20px;
        }

        .product-title {
            font-size: 20px;
        }

        .product-price {
            font-size: 24px;
        }

        .quantity-selector,
        .add-to-cart-btn,
        .wishlist-add-btn {
            width: 100%;
        }

        .product-features {
            padding: 15px;
        }

        .feature-item {
            font-size: 13px;
        }
    }

    /* Loading State */
    .product-loading {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 500px;
        font-size: 18px;
        color: #999;
    }

    /* Toast Notification Styles - FIXED LAYOUT BUG */
    .toast-notification {
        position: fixed;
        top: 20px;
        right: 20px;
        width: auto;
        max-width: 320px;
        height: fit-content;
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        padding: 10px 12px;
        z-index: 10000;
        opacity: 0;
        transform: translateY(-20px);
        transition: all 0.3s ease;
        animation: slideDownIn 0.3s ease forwards;
        pointer-events: auto;
        display: block;
    }

    @keyframes slideDownIn {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .toast-notification.show {
        opacity: 1;
        transform: translateY(0);
    }

    .toast-content {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #333;
        font-weight: 500;
        font-size: 13px;
        line-height: 1.4;
        word-wrap: break-word;
        white-space: normal;
    }

    .toast-content i {
        color: #4caf50;
        font-size: 16px;
        flex-shrink: 0;
        min-width: 16px;
        text-align: center;
    }

</style>

<div class="product-detail-page">
    <!-- Breadcrumb Navigation -->
    <div class="breadcrumb-nav">
        <a href="index.php">Home</a>
        <span>/</span>
        <span id="breadcrumb-category">Products</span>
        <span>/</span>
        <span id="breadcrumb-name">Product</span>
    </div>

    <!-- Product Loading State -->
    <div class="product-loading" id="loading-state">
        <i class="fas fa-spinner"></i>
        <p>Loading product details...</p>
    </div>

    <!-- Product Detail Container (Hidden initially) -->
    <div id="product-container" style="display: none;">
        <!-- Product Gallery & Info Section -->
        <div class="product-detail-wrapper">
            <!-- Product Gallery -->
            <div class="product-gallery">
                <div class="product-main-image">
                    <img id="main-image" src="" alt="Product Image">
                </div>
                <div class="product-thumbnails" id="thumbnails-container">
                    <!-- Thumbnails will be added here -->
                </div>
            </div>

            <!-- Product Information -->
            <div class="product-info-section">
                <div class="product-header">
                    <h1 class="product-title" id="product-name"></h1>
                    <p class="product-category" id="product-category"></p>
                </div>

                <!-- Product Rating -->
                <div class="product-rating-bar" id="product-rating-bar">
                    <div class="stars-display" id="product-stars">
                        <!-- Stars will be rendered here -->
                    </div>
                    <div class="rating-info">
                        <span class="rating-info-value" id="avg-rating">0</span>
                        <span id="rating-separator">/</span>
                        <span id="rating-max">5</span>
                        <span id="reviews-count">(<span id="total-reviews">0</span> reviews)</span>
                    </div>
                </div>

                <!-- Price Section -->
                <div class="product-price-section">
                    <div class="product-price" id="product-price">PKR 0</div>
                    <p class="price-note">Inclusive of all taxes</p>
                </div>

                <!-- Description -->
                <p class="product-description" id="product-description">
                    Premium quality fashion accessory with exceptional craftsmanship and attention to detail.
                </p>

                <!-- Features -->
                <div class="product-features" id="product-features">
                    <div class="feature-item">
                        <span class="feature-icon">✓</span>
                        <span>Authentic & Genuine Product</span>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">✓</span>
                        <span>Free Shipping on Orders Above PKR 5000</span>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">✓</span>
                        <span>30-Day Easy Returns & Exchanges</span>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">✓</span>
                        <span>Secure Payment Options</span>
                    </div>
                </div>

                <!-- Actions -->
                <div class="product-actions">
                    <div class="quantity-selector">
                        <button class="qty-btn" id="qty-decrease">−</button>
                        <input type="number" class="qty-input" id="qty-input" value="1" min="1">
                        <button class="qty-btn" id="qty-increase">+</button>
                    </div>
                    <button class="add-to-cart-btn" id="add-to-cart-btn">
                        <i class="fas fa-shopping-cart"></i> Add to Cart
                    </button>
                    <button class="wishlist-add-btn" id="wishlist-btn" title="Add to Wishlist">
                        <i class="far fa-heart"></i> Wishlist
                    </button>
                </div>
            </div>
        </div>

        <!-- Reviews Section -->
        <div class="reviews-section">
            <h2 class="reviews-header">Customer Reviews & Ratings</h2>
            <div id="reviews-container" class="reviews-container"></div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="js/reviews.js"></script>
<script>
    // Get product ID from URL
    const urlParams = new URLSearchParams(window.location.search);
    const productId = parseInt(urlParams.get('id')) || 0;

    // Product data from product.js
    const allProductCategories = {
        bracelets: [
            { id: 1, name: "Premium Gold Bracelet", price: 12500, image: "/assets/images/products/bracelets/bracelet_1.jpg" },
            { id: 2, name: "Silver Chain Bracelet", price: 4500, image: "/assets/images/products/bracelets/bracelet_2.jpg" },
            { id: 3, name: "Diamond Tennis Bracelet", price: 28000, image: "/assets/images/products/bracelets/bracelet_3.jpg" },
            { id: 4, name: "Platinum Bracelet", price: 35000, image: "/assets/images/products/bracelets/bracelet_4.jpg" },
            { id: 5, name: "Gold Bracelet", price: 15000, image: "/assets/images/products/bracelets/bracelet_5.jpg" },
            { id: 6, name: "Diamond Bracelet", price: 45000, image: "/assets/images/products/bracelets/bracelet_6.jpg" },
            { id: 7, name: "Diamond Bracelet", price: 55000, image: "/assets/images/products/bracelets/bracelet_7.jpg" },
            { id: 8, name: "Diamond Bracelet", price: 75000, image: "/assets/images/products/bracelets/bracelet_8.jpg" },
        ],
        digital_watches: [
            { id: 101, name: "Smart Digital Watch", price: 8500, image: "/assets/images/products/digital_watches/digital_watch_1.jpg" },
            { id: 102, name: "Sports Digital Watch", price: 4200, image: "/assets/images/products/digital_watches/digital_watch_2.jpg" },
            { id: 103, name: "LED Digital Watch", price: 2500, image: "/assets/images/products/digital_watches/digital_watch_3.jpg" },
            { id: 104, name: "Retro Digital Watch", price: 5500, image: "/assets/images/products/digital_watches/digital_watch_4.jpg" },
            { id: 105, name: "Retro Digital Watch", price: 6500, image: "/assets/images/products/digital_watches/digital_watch_5.png" },
            { id: 106, name: "Retro Digital Watch", price: 8000, image: "/assets/images/products/digital_watches/digital_watch_6.jpg" },
            { id: 107, name: "Retro Digital Watch", price: 9500, image: "/assets/images/products/digital_watches/digital_watch_7.jpg" },
            { id: 108, name: "Retro Digital Watch", price: 11500, image: "/assets/images/products/digital_watches/digital_watch_8.png" },
        ],
        normal_watches: [
            { id: 201, name: "Classic Analog Watch", price: 7500, image: "/assets/images/products/normal_watches/normal_watch_1.jpg" },
            { id: 202, name: "Luxury Business Watch", price: 18000, image: "/assets/images/products/normal_watches/normal_watch_2.jpg" },
            { id: 203, name: "Casual Day Watch", price: 5500, image: "/assets/images/products/normal_watches/normal_watch_3.png" },
            { id: 204, name: "Luxury Day Watch", price: 9500, image: "/assets/images/products/normal_watches/normal_watch_4.jpg" },
            { id: 205, name: "Luxury Day Watch", price: 12000, image: "/assets/images/products/normal_watches/normal_watch_5.png" },
            { id: 206, name: "Luxury Day Watch", price: 15000, image: "/assets/images/products/normal_watches/normal_watch_6.png" },
            { id: 207, name: "Luxury Day Watch", price: 18500, image: "/assets/images/products/normal_watches/normal_watch_7.png" },
            { id: 208, name: "Luxury Day Watch", price: 22000, image: "/assets/images/products/normal_watches/normal_watch_8.jpg" },
        ],
        gold_chains: [
            { id: 301, name: "18K Gold Chain", price: 32000, image: "/assets/images/products/gold_chains/gold_chain_1.jpg" },
            { id: 302, name: "Gold Rope Chain", price: 26500, image: "/assets/images/products/gold_chains/gold_chain_2.jpg" },
            { id: 303, name: "Cuban Link Gold Chain", price: 42000, image: "/assets/images/products/gold_chains/gold_chain_3.jpg" },
            { id: 304, name: "Gold Chain", price: 18500, image: "/assets/images/products/gold_chains/gold_chain_4.jpg" },
            { id: 305, name: "Gold Chain", price: 22000, image: "/assets/images/products/gold_chains/gold_chain_5.jpg" },
            { id: 306, name: "Gold Chain", price: 28000, image: "/assets/images/products/gold_chains/gold_chain_6.jpg" },
            { id: 307, name: "Gold Chain", price: 35000, image: "/assets/images/products/gold_chains/gold_chain_7.jpg" },
            { id: 308, name: "Gold Chain", price: 45000, image: "/assets/images/products/gold_chains/gold_chain_8.jpg" },
        ],
        silver_chains: [
            { id: 401, name: "Sterling Silver Chain", price: 6500, image: "/assets/images/products/silver_chains/silver_chain_1.jpg" },
            { id: 402, name: "Silver Box Chain", price: 4500, image: "/assets/images/products/silver_chains/silver_chain_2.jpg" },
            { id: 403, name: "Silver Snake Chain", price: 5500, image: "/assets/images/products/silver_chains/silver_chain_3.jpg" },
            { id: 404, name: "Silver Chain", price: 8000, image: "/assets/images/products/silver_chains/silver_chain_4.jpg" },
            { id: 405, name: "Silver Chain", price: 10000, image: "/assets/images/products/silver_chains/silver_chain_5.jpg" },
            { id: 406, name: "Silver Chain", price: 12500, image: "/assets/images/products/silver_chains/silver_chain_6.jpg" },
            { id: 407, name: "Silver Chain", price: 15000, image: "/assets/images/products/silver_chains/silver_chain_7.jpg" },
            { id: 408, name: "Silver Chain", price: 18500, image: "/assets/images/products/silver_chains/silver_chain_8.jpg" },
        ]
    };

    // Find product
    let currentProduct = null;
    let currentCategory = null;

    for (let category in allProductCategories) {
        const found = allProductCategories[category].find(p => p.id === productId);
        if (found) {
            currentProduct = { ...found, category };
            currentCategory = category;
            break;
        }
    }

    if (!currentProduct) {
        document.getElementById('loading-state').innerHTML = '<p style="color: #e91e63; font-size: 18px;"><i class="fas fa-exclamation-circle"></i> Product not found</p>';
        document.getElementById('product-container').style.display = 'none';
    } else {
        // Load product details
        loadProductDetails();
    }

    function loadProductDetails() {
        // Update breadcrumb
        document.getElementById('breadcrumb-category').textContent = formatCategoryName(currentCategory);
        document.getElementById('breadcrumb-name').textContent = currentProduct.name;

        // Update product info
        document.getElementById('main-image').src = currentProduct.image;
        document.getElementById('main-image').alt = currentProduct.name;
        document.getElementById('product-name').textContent = currentProduct.name;
        document.getElementById('product-category').textContent = formatCategoryName(currentCategory);
        document.getElementById('product-price').textContent = 'PKR ' + currentProduct.price.toLocaleString('en-PK');
        document.getElementById('product-description').textContent = getProductDescription(currentProduct.name);

        // Create thumbnail
        const thumbContainer = document.getElementById('thumbnails-container');
        thumbContainer.innerHTML = `<div class="product-thumbnail active" onclick="changeImage(this)">
            <img src="${currentProduct.image}" alt="${currentProduct.name}">
        </div>`;

        // Show product container and hide loading
        document.getElementById('loading-state').style.display = 'none';
        document.getElementById('product-container').style.display = 'block';

        // Load and display product rating in header
        loadProductRating(productId);

        // Initialize review system
        initReviewSystem(productId, 'reviews-container');

        // Setup action buttons
        setupProductActions();
    }

    function loadProductRating(productId) {
        fetch(`api/reviews.php?action=get_rating_summary&product_id=${productId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.summary) {
                    const summary = data.summary;
                    const avgRating = parseFloat(summary.average_rating || 0).toFixed(1);
                    const totalReviews = parseInt(summary.total_reviews) || 0;

                    // Update rating display
                    document.getElementById('total-reviews').textContent = totalReviews;
                    
                    if (totalReviews > 0) {
                        // Show rating with stars
                        document.getElementById('avg-rating').textContent = avgRating;
                        const starsHtml = generateStars(avgRating);
                        document.getElementById('product-stars').innerHTML = starsHtml;
                        document.getElementById('product-rating-bar').style.display = 'flex';
                    } else {
                        // Hide rating bar when no reviews
                        document.getElementById('product-rating-bar').style.display = 'none';
                    }
                }
            })
            .catch(error => console.error('Error loading rating:', error));
    }

    function generateStars(rating) {
        let starsHtml = '';
        const fullStars = Math.floor(rating);
        const hasHalfStar = rating % 1 !== 0;
        
        for (let i = 0; i < 5; i++) {
            if (i < fullStars) {
                starsHtml += '<span class="star active">★</span>';
            } else {
                starsHtml += '<span class="star">★</span>';
            }
        }
        return starsHtml;
    }

    function formatCategoryName(category) {
        return category.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    }

    function getProductDescription(productName) {
        return `Our ${productName} is crafted with precision and attention to detail. Made from premium materials, this piece combines elegance with durability, making it a perfect addition to any collection.`;
    }

    function changeImage(thumbnail) {
        const oldActive = document.querySelector('.product-thumbnail.active');
        if (oldActive) oldActive.classList.remove('active');
        thumbnail.classList.add('active');

        const img = thumbnail.querySelector('img');
        const mainImage = document.getElementById('main-image');
        mainImage.src = img.src;
        mainImage.alt = img.alt;
    }

    function setupProductActions() {
        const qtyInput = document.getElementById('qty-input');
        const decreaseBtn = document.getElementById('qty-decrease');
        const increaseBtn = document.getElementById('qty-increase');
        const addToCartBtn = document.getElementById('add-to-cart-btn');
        const wishlistBtn = document.getElementById('wishlist-btn');

        // Quantity controls
        decreaseBtn.addEventListener('click', () => {
            let qty = parseInt(qtyInput.value) - 1;
            if (qty < 1) qty = 1;
            qtyInput.value = qty;
        });

        increaseBtn.addEventListener('click', () => {
            let qty = parseInt(qtyInput.value) + 1;
            qtyInput.value = qty;
        });

        // Add to cart
        addToCartBtn.addEventListener('click', () => {
            const quantity = parseInt(qtyInput.value);
            addToCartDetail(quantity);
        });

        // Wishlist button
        wishlistBtn.addEventListener('click', () => {
            toggleWishlistDetail();
        });

        // Check if product is in wishlist
        checkWishlistStatus();

        // Load wishlist counter in navbar
        loadWishlistCounterInNavbar();
    }

    function loadWishlistCounterInNavbar() {
        fetch('includes/wishlist.php?action=count')
            .then(response => response.json())
            .then(data => {
                const count = document.querySelector('.wishlist-count');
                if (count) {
                    if (data.count > 0) {
                        count.textContent = data.count;
                    } else {
                        count.textContent = '';
                    }
                }
            })
            .catch(error => console.error('Error loading wishlist count:', error));
    }

    function addToCartDetail(quantity) {
        const formData = new FormData();
        formData.append('action', 'add');
        formData.append('product_id', productId);
        formData.append('category', currentCategory);
        formData.append('product_name', currentProduct.name);
        formData.append('product_price', currentProduct.price);
        formData.append('product_image', currentProduct.image);
        formData.append('quantity', quantity);

        fetch('includes/cart.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (response.redirected) {
                window.location.href = response.url;
                return;
            }
            return response.json();
        })
        .then(data => {
            if (data && data.success) {
                showAddToCartNotification(currentProduct.name);
                fetchCartCountFromServer();
            } else if (data && data.message) {
                alert(data.message || 'Failed to add item to cart');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error adding item to cart');
        });
    }

    function showAddToCartNotification(productName) {
        const toast = document.createElement('div');
        toast.className = 'toast-notification';
        toast.innerHTML = `
            <div class="toast-content">
                <i class="fas fa-check-circle"></i>
                <span>${productName} added to cart!</span>
            </div>
        `;
        
        document.body.appendChild(toast);
        setTimeout(() => toast.classList.add('show'), 10);
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    function fetchCartCountFromServer() {
        const formData = new FormData();
        formData.append('action', 'count');

        fetch('includes/cart.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (response.redirected) return;
            return response.json();
        })
        .then(data => {
            if (data && data.success) {
                const badge = document.querySelector('.cart-count');
                if (badge) {
                    badge.textContent = data.count.toString();
                }
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function toggleWishlistDetail() {
        const btn = document.getElementById('wishlist-btn');
        const isInWishlist = btn.classList.contains('active');

        const formData = new FormData();
        formData.append('action', isInWishlist ? 'remove' : 'add');
        formData.append('product_id', productId);
        formData.append('category', currentCategory);
        formData.append('product_name', currentProduct.name);
        formData.append('product_price', currentProduct.price);
        formData.append('product_image', currentProduct.image);

        fetch('includes/wishlist.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                btn.classList.toggle('active');
                if (isInWishlist) {
                    btn.innerHTML = '<i class="far fa-heart"></i> Wishlist';
                } else {
                    btn.innerHTML = '<i class="fas fa-heart"></i> Wishlist';
                }
                showNotification(data.message);
                // Update wishlist counter in navbar
                updateWishlistCounterInNavbar();
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating wishlist');
        });
    }

    function checkWishlistStatus() {
        fetch('includes/wishlist.php?action=get')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.items) {
                    const isInWishlist = data.items.some(item => item.product_id === productId);
                    if (isInWishlist) {
                        const btn = document.getElementById('wishlist-btn');
                        btn.classList.add('active');
                        btn.innerHTML = '<i class="fas fa-heart"></i> Wishlist';
                    }
                }
            });
    }

    function showNotification(message) {
        const toast = document.createElement('div');
        toast.className = 'toast-notification';
        toast.innerHTML = `
            <div class="toast-content">
                <i class="fas fa-check-circle"></i>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(toast);
        setTimeout(() => toast.classList.add('show'), 10);
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    function updateWishlistCounterInNavbar() {
        fetch('includes/wishlist.php?action=count')
            .then(response => response.json())
            .then(data => {
                const count = document.querySelector('.wishlist-count');
                if (count) {
                    if (data.count > 0) {
                        count.textContent = data.count;
                    } else {
                        count.textContent = '';
                    }
                }
            })
            .catch(error => console.error('Error updating wishlist count:', error));
    }
</script>
