<?php
session_start();
include 'includes/db.php';

$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

$results = [];
$totalResults = 0;

if (strlen($query) >= 2) {
    try {
        // Search products from database with all fields needed for display
        $searchTerm = '%' . strtolower($query) . '%';
        $sql = "SELECT id, name, category, price, image_url, description FROM products 
                WHERE LOWER(name) LIKE ? OR LOWER(category) LIKE ? OR LOWER(description) LIKE ?";
        $params = [$searchTerm, $searchTerm, $searchTerm];
        
        // Filter by category if provided
        if (!empty($category)) {
            $sql .= " AND category = ?";
            $params[] = $category;
        }
        
        $sql .= " ORDER BY name ASC LIMIT 50";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $totalResults = count($results);
        
    } catch (PDOException $e) {
        $error = 'Search error: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - Fashion Bloom</title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/product.css">
    <link rel="stylesheet" href="css/pages/reviews.css">
    <style>
        .search-page-header {
            margin-top: 100px;
            padding: 40px 20px;
            background: linear-gradient(135deg, rgba(212, 175, 55, 0.1) 0%, rgba(212, 175, 55, 0.05) 100%);
            text-align: center;
        }

        .search-page-header h1 {
            font-size: 2rem;
            color: #d4af37;
            margin-bottom: 10px;
        }

        .search-page-header p {
            color: #666;
            font-size: 1.1rem;
        }

        .results-count {
            color: #999;
            font-size: 0.95rem;
            margin-top: 15px;
        }

        .search-results-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .search-results {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }

        .no-results {
            text-align: center;
            padding: 60px 20px;
        }

        .no-results-icon {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 20px;
        }

        .no-results-text {
            font-size: 1.3rem;
            color: #666;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .no-results-suggestion {
            color: #999;
            font-size: 1rem;
            margin-bottom: 30px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #d4af37;
            font-weight: 600;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .back-link:hover {
            gap: 12px;
            color: #e5c158;
        }

        /* Cart and overlay styles */
        .cart-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 998;
        }

        .cart-overlay.active {
            display: block;
        }

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            z-index: 997;
        }

        .overlay.active {
            display: block;
        }

        @media (max-width: 768px) {
            .search-results {
                grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
                gap: 15px;
            }

            .search-page-header h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Search Results Page -->
    <div class="search-page-header">
        <h1>Search Results</h1>
        <?php if ($query): ?>
            <p>Search: <strong><?= htmlspecialchars($query) ?></strong></p>
            <?php if ($totalResults > 0): ?>
                <p class="results-count">Found <strong><?= $totalResults ?></strong> product<?= $totalResults !== 1 ? 's' : '' ?></p>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <div class="search-results-container">
        <?php if ($totalResults > 0): ?>
            <a href="index.php" class="back-link">
                <i class="fas fa-chevron-left"></i> Back to Home
            </a>
            
            <div class="search-results">
                <?php foreach ($results as $product): ?>
                    <div class="product-card" data-category="<?= htmlspecialchars($product['category']) ?>" data-product-id="<?= htmlspecialchars($product['id']) ?>">
                        <div class="product-img">
                            <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" onerror="this.src='/assets/images/placeholder.png'">
                            <div class="product-overlay">
                                <button class="quick-view-btn" onclick="quickView(<?= htmlspecialchars($product['id']) ?>)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <button class="wishlist-btn" onclick="toggleWishlist(<?= htmlspecialchars($product['id']) ?>, '<?= htmlspecialchars($product['category']) ?>', '<?= htmlspecialchars(str_replace("'", "\\'", $product['name'])) ?>', <?= htmlspecialchars($product['price']) ?>, '<?= htmlspecialchars(str_replace("'", "\\'", $product['image_url'])) ?>')">
                                <i class="far fa-heart"></i>
                            </button>
                        </div>
                        <div class="product-info">
                            <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>
                            <p class="product-category"><?= htmlspecialchars(formatCategory($product['category'])) ?></p>
                            <div class="product-price">PKR <?= number_format($product['price'], 0) ?></div>
                            <div class="quantity-addcart-container">
                                <label for="quantity-<?= htmlspecialchars($product['id']) ?>" class="quantity-label">Quantity:</label>
                                <input type="number" id="quantity-<?= htmlspecialchars($product['id']) ?>" name="quantity-<?= htmlspecialchars($product['id']) ?>" value="1" min="1" class="quantity-input">
                                <button class="add-to-cart-btn" onclick="addToCart(<?= htmlspecialchars($product['id']) ?>, '<?= htmlspecialchars($product['category']) ?>')">
                                    <i class="fas fa-shopping-cart"></i> Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-results">
                <div class="no-results-icon">
                    <i class="fas fa-search"></i>
                </div>
                <p class="no-results-text">No products found</p>
                <?php if ($query): ?>
                    <p class="no-results-suggestion">
                        We couldn't find any products matching "<strong><?= htmlspecialchars($query) ?></strong>"
                    </p>
                <?php endif; ?>
                <a href="index.php" class="btn btn-primary">Back to Home</a>
            </div>
        <?php endif; ?>
    </div>

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

    <!-- Cart Overlay -->
    <div class="cart-overlay" id="cart-overlay"></div>

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

    <?php include 'includes/footer.php'; ?>

    <?php
    function formatCategory($category) {
        return str_replace('_', ' ', ucwords(str_replace('_', ' ', $category)));
    }
    ?>

    <script src="js/product.js"></script>
    <script src="js/reviews.js"></script>
    <script src="js/cart-utils.js"></script>
    <script src="js/wishlist.js"></script>
</body>
</html>
