<?php
session_start();
include "includes/db.php";

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch admin profile photo
$admin_profile_photo = '';
try {
    $stmt = $pdo->prepare("SELECT profile_photo FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $admin_user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($admin_user && $admin_user['profile_photo']) {
        $admin_profile_photo = $admin_user['profile_photo'];
    }
} catch (PDOException $e) {
    // Profile photo not available
}

// Create uploads directory if it doesn't exist
$uploadDir = "uploads/products/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Initialize variables
$message = '';
$error = '';
$products = [];
$orders = [];
$users = [];
$messages = [];
$reviews = [];
$recentOrders = [];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update Order Status
    if (isset($_POST['action']) && $_POST['action'] === 'update_order_status') {
        $id = $_POST['id'];
        $status = $_POST['status'];

        // Get order details before updating (for email)
        $sql = "SELECT o.*, u.first_name, u.email FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            $error = "Order not found!";
        } else {
            // Update order status
            $sql = "UPDATE orders SET status=? WHERE id=?";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([$status, $id])) {
                $message = "Order status updated successfully!";

                // Send email notification to customer
                try {
                    require_once('includes/email.php');
                    $email_sent = sendOrderStatusUpdateEmail(
                        $order['email'],
                        $order['first_name'],
                        $order['order_number'],
                        $order['id'],
                        $status,
                        $order['total_price']
                    );

                    if ($email_sent) {
                        $message .= " Customer has been notified via email.";
                    } else {
                        $message .= " (Note: Email notification failed to send)";
                    }
                } catch (Exception $e) {
                    $message .= " (Note: Email notification failed: " . $e->getMessage() . ")";
                }
            } else {
                $error = "Error updating order status!";
            }
        }
    }
    
    // Delete User
    if (isset($_POST['action']) && $_POST['action'] === 'delete_user') {
        $id = $_POST['id'];
        
        $sql = "DELETE FROM users WHERE id=? AND role='user'";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$id])) {
            $message = "User deleted successfully!";
        } else {
            $error = "Error deleting user!";
        }
    }
    
    // Update User
    if (isset($_POST['action']) && $_POST['action'] === 'update_user') {
        $id = $_POST['id'];
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        
        $sql = "UPDATE users SET first_name=?, last_name=?, email=?, phone=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$first_name, $last_name, $email, $phone, $id])) {
            $message = "User updated successfully!";
        } else {
            $error = "Error updating user!";
        }
    }
    
    // Delete Review
    if (isset($_POST['action']) && $_POST['action'] === 'delete_review') {
        $id = $_POST['id'];
        
        $sql = "DELETE FROM reviews WHERE id=?";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$id])) {
            $message = "Review deleted successfully!";
        } else {
            $error = "Error deleting review!";
        }
    }
    
    // Update Message Status
    if (isset($_POST['action']) && $_POST['action'] === 'update_message_status') {
        $id = $_POST['id'];
        $status = $_POST['status'];
        
        $sql = "UPDATE contact_messages SET status=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$status, $id])) {
            $message = "Message status updated successfully!";
        } else {
            $error = "Error updating message status!";
        }
    }
    
    // Delete Message
    if (isset($_POST['action']) && $_POST['action'] === 'delete_message') {
        $id = $_POST['id'];
        
        $sql = "DELETE FROM contact_messages WHERE id=?";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$id])) {
            $message = "Message deleted successfully!";
        } else {
            $error = "Error deleting message!";
        }
    }
    
    
    // Add Product
    if (isset($_POST['action']) && $_POST['action'] === 'add_product') {
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $category = $_POST['category'];
        $stock_quantity = $_POST['stock_quantity'];
        
        // Handle image upload
        $image_url = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imageFile = $_FILES['image'];
            $fileName = uniqid() . '_' . basename($imageFile['name']);
            $targetPath = $uploadDir . $fileName;
            
            // Check if image file is actual image
            $check = getimagesize($imageFile['tmp_name']);
            if ($check !== false) {
                if (move_uploaded_file($imageFile['tmp_name'], $targetPath)) {
                    $image_url = $targetPath;
                } else {
                    $error = "Sorry, there was an error uploading your file.";
                }
            } else {
                $error = "File is not an image.";
            }
        } else {
            $error = "Product image is required.";
        }
        
        if (empty($error) && !empty($image_url)) {
            $sql = "INSERT INTO products (name, description, price, image_url, category, stock_quantity) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([$name, $description, $price, $image_url, $category, $stock_quantity])) {
                $message = "Product added successfully!";
            } else {
                $error = "Error adding product!";
            }
        }
    }
    
    // Delete Product
    if (isset($_POST['action']) && $_POST['action'] === 'delete_product') {
        $id = $_POST['id'];
        
        // Get product image path to delete file
        $sql = "SELECT image_url FROM products WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($product && !empty($product['image_url']) && file_exists($product['image_url'])) {
            unlink($product['image_url']);
        }
        
        $sql = "DELETE FROM products WHERE id=?";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$id])) {
            $message = "Product deleted successfully!";
        } else {
            $error = "Error deleting product!";
        }
    }
    
    // Update Product
    if (isset($_POST['action']) && $_POST['action'] === 'update_product') {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $category = $_POST['category'];
        $stock_quantity = $_POST['stock_quantity'];
        $current_image_url = $_POST['current_image_url'];
        
        $image_url = $current_image_url;
        
        // Handle new image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imageFile = $_FILES['image'];
            $fileName = uniqid() . '_' . basename($imageFile['name']);
            $targetPath = $uploadDir . $fileName;
            
            // Check if image file is actual image
            $check = getimagesize($imageFile['tmp_name']);
            if ($check !== false) {
                if (move_uploaded_file($imageFile['tmp_name'], $targetPath)) {
                    // Delete old image if exists
                    if (!empty($current_image_url) && file_exists($current_image_url)) {
                        unlink($current_image_url);
                    }
                    $image_url = $targetPath;
                } else {
                    $error = "Sorry, there was an error uploading your file.";
                }
            } else {
                $error = "File is not an image.";
            }
        }
        
        if (empty($error)) {
            $sql = "UPDATE products SET name=?, description=?, price=?, image_url=?, category=?, stock_quantity=? WHERE id=?";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([$name, $description, $price, $image_url, $category, $stock_quantity, $id])) {
                $message = "Product updated successfully!";
            } else {
                $error = "Error updating product!";
            }
        }
    }
}

// Fetch data for dashboard
try {
    // Total Users
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role='user'");
    $totalUsers = $stmt->fetchColumn();
    
    // Total Revenue - Sum of all completed orders (delivered or processing)
    $stmt = $pdo->query("SELECT SUM(total_price) FROM orders WHERE status IN ('delivered', 'processing', 'shipped') OR payment_status = 'completed'");
    $totalRevenue = $stmt->fetchColumn() ?: 0;
    
    // Total Orders
    $stmt = $pdo->query("SELECT COUNT(*) FROM orders");
    $totalOrders = $stmt->fetchColumn();
    
    // Total Messages
    $stmt = $pdo->query("SELECT COUNT(*) FROM contact_messages");
    $totalMessages = $stmt->fetchColumn();
    
    // Total Products
    $stmt = $pdo->query("SELECT COUNT(*) FROM products");
    $totalProducts = $stmt->fetchColumn();
    
    // Recent Orders
    $stmt = $pdo->query("SELECT o.*, u.first_name, u.last_name, u.email FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 5");
    $recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Recent Messages
    $stmt = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 5");
    $recentMessages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // All Orders for orders section
    $stmt = $pdo->query("SELECT o.*, u.first_name, u.last_name, u.email FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // All Users for users section
    $stmt = $pdo->query("SELECT * FROM users WHERE role='user' ORDER BY id DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // All Messages for messages section
    $stmt = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Reviews
    $stmt = $pdo->query("SELECT r.*, u.first_name, u.last_name, p.name as product_name FROM reviews r JOIN users u ON r.user_id = u.id JOIN products p ON r.product_id = p.id ORDER BY r.created_at DESC");
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    
    // Products - Order by ID if created_at doesn't exist
    try {
        $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        $products = [];
    }
    
} catch(PDOException $e) {
    $error = "Error fetching data: " . $e->getMessage();
    $products = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Fashion Bloom</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/product-filters.css">
    <link rel="stylesheet" href="css/admin/index.css">
</head>
<body>
<!-- Top Navbar -->
<nav class="admin-navbar">
    <div class="navbar-container">
        <div class="navbar-brand">
            <button class="navbar-toggle" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
                <a href="/admin.php" class="admin-logo-link">
                    <img src="/assets/images/fashion_bloom_logo.png" alt="Fashion Bloom Admin" class="admin-logo-image">
                </a>
        </div>
        
        <div class="navbar-right">
            <div class="navbar-user">
                <?php if ($admin_profile_photo): ?>
                    <img src="/uploads/admin/<?= htmlspecialchars($admin_profile_photo); ?>" alt="Admin" class="navbar-profile-photo" title="Admin Profile">
                <?php else: ?>
                    <i class="fas fa-user-circle"></i>
                <?php endif; ?>
                <span><?php echo htmlspecialchars($_SESSION['first_name'] ?? 'Admin'); ?></span>
                <div class="user-dropdown">
                    <a href="admin-profile.php"><i class="fas fa-user"></i> Admin Profile</a>
                    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
        </div>
    </div>
</nav>

<!-- Main Admin Wrapper -->
<div class="admin-wrapper">
    <!-- Sidebar -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-header">
            <div class="sidebar-brand">
                <i class="fas fa-crown"></i>
                <span>Admin</span>
            </div>
        </div>
        
        <nav class="sidebar-nav">
            <ul>
                <li class="sidebar-nav-item">
                    <a href="#dashboard" class="sidebar-nav-link active" data-section="dashboard">
                        <i class="fas fa-chart-line"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="#orders" class="sidebar-nav-link" data-section="orders">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Orders</span>
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="#products" class="sidebar-nav-link" data-section="products">
                        <i class="fas fa-box"></i>
                        <span>Products</span>
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="#users" class="sidebar-nav-link" data-section="users">
                        <i class="fas fa-users"></i>
                        <span>Users</span>
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="#reviews" class="sidebar-nav-link" data-section="reviews">
                        <i class="fas fa-star"></i>
                        <span>Reviews</span>
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="#messages" class="sidebar-nav-link" data-section="messages">
                        <i class="fas fa-envelope"></i>
                        <span>Messages</span>
                    </a>
                </li>

            </ul>
        </nav>
        
        <div class="sidebar-footer">
            <a href="admin-profile.php" class="logout-btn" style="margin-bottom: 10px;">
                <i class="fas fa-user-circle"></i>
                <span>Edit Profile</span>
            </a>
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </aside>

    <!-- Mobile Overlay -->
    <div class="admin-sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Main Content -->
    <main class="admin-main">
        <!-- Page Title -->
        <div class="admin-page-title">
            <i class="fas fa-chart-line" id="pageIcon"></i>
            <span id="pageTitle">Dashboard</span>
        </div>

        <!-- Messages -->
        <?php if ($message): ?>
            <div class="message success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="message error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Dashboard Section -->
        <section id="dashboard" class="content-section active">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <div class="stat-label">Total Users</div>
                        <div class="stat-value"><?php echo $totalUsers; ?></div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div>
                        <div class="stat-label">Total Orders</div>
                        <div class="stat-value"><?php echo $totalOrders; ?></div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div>
                        <div class="stat-label">Total Revenue</div>
                        <div class="stat-value">PKR <?php echo number_format($totalRevenue, 0); ?></div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div>
                        <div class="stat-label">Total Messages</div>
                        <div class="stat-value"><?php echo $totalMessages; ?></div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-box"></i>
                    </div>
                    <div>
                        <div class="stat-label">Total Products</div>
                        <div class="stat-value"><?php echo $totalProducts; ?></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Orders Section -->
        <section id="orders" class="content-section">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="fas fa-shopping-cart"></i>
                    Orders Management
                </h2>
            </div>
            
            <!-- Search and Filter Controls for Orders -->
            <div class="product-filters" style="margin-bottom: 20px;">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="ordersSearch" placeholder="Search by order ID, customer name, or email..." autocomplete="off">
                </div>
                <div class="filter-group">
                    <select id="orderStatusFilter" class="filter-select">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="shipped">Shipped</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    <select id="paymentStatusFilter" class="filter-select">
                        <option value="">All Payment Status</option>
                        <option value="pending">Pending</option>
                        <option value="completed">Completed</option>
                    </select>
                    <button class="btn btn-secondary" id="resetOrderFilters" onclick="resetOrderFilters()">
                        <i class="fas fa-redo"></i> Reset
                    </button>
                </div>
            </div>

            <!-- Results Count -->
            <div class="results-info">
                <span id="ordersResultsCount">Showing <strong><?php echo count($orders); ?></strong> orders</span>
            </div>
            
            <div>
                <table class="data-table" id="ordersTable">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Email</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="ordersList">
                        <?php foreach ($orders as $order): ?>
                        <tr class="order-row" data-order-id="<?php echo $order['id']; ?>" data-status="<?php echo $order['status']; ?>" data-payment-status="<?php echo $order['payment_status'] ?? ''; ?>">
                            <td>#<?php echo $order['id']; ?></td>
                            <td><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($order['email'] ?? ''); ?></td>
                            <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                            <td>PKR <?php echo number_format($order['total_price'] ?? 0, 2); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $order['status']; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo $order['payment_status'] ?? 'pending'; ?>">
                                    <?php echo ucfirst($order['payment_status'] ?? 'pending'); ?>
                                </span>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <form method="POST">
                                        <input type="hidden" name="action" value="update_order_status">
                                        <input type="hidden" name="id" value="<?php echo $order['id']; ?>">
                                        <select name="status" onchange="this.form.submit()">
                                            <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                                            <option value="shipped" <?php echo $order['status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                            <option value="delivered" <?php echo $order['status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                            <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <!-- No Orders Message -->
                <div id="noOrdersMessage" class="no-results" style="display: none;">
                    <i class="fas fa-shopping-cart"></i>
                    <h3>No orders found</h3>
                    <p>Try adjusting your search or filter criteria.</p>
                </div>
            </div>
        </section>

        <!-- Products Section (Grid View) -->
        <section id="products" class="content-section">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="fas fa-box"></i>
                    Products Management
                </h2>
                <button class="btn btn-primary" onclick="openAddProductModal()">
                    <i class="fas fa-plus"></i> Add Product
                </button>
            </div>

            <!-- Search and Filter Controls -->
            <div class="product-filters" style="margin-bottom: 20px;">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="productSearch" placeholder="Search products by name, category..." autocomplete="off">
                </div>
                <div class="filter-group">
                    <select id="categoryFilter" class="filter-select">
                        <option value="">All Categories</option>
                        <option value="bracelets">Bracelets</option>
                        <option value="digital_watches">Digital Watches</option>
                        <option value="gold_chains">Gold Chains</option>
                        <option value="normal_watches">Normal Watches</option>
                        <option value="silver_chains">Silver Chains</option>
                    </select>
                    <select id="stockFilter" class="filter-select">
                        <option value="">All Stock Status</option>
                        <option value="in-stock">In Stock</option>
                        <option value="low-stock">Low Stock (0-10)</option>
                        <option value="out-of-stock">Out of Stock</option>
                    </select>
                    <select id="priceFilter" class="filter-select">
                        <option value="">All Prices</option>
                        <option value="0-5000">PKR 0 - 5,000</option>
                        <option value="5000-15000">PKR 5,000 - 15,000</option>
                        <option value="15000-50000">PKR 15,000 - 50,000</option>
                        <option value="50000+">PKR 50,000+</option>
                    </select>
                    <button class="btn btn-secondary" id="resetFilters" onclick="resetProductFilters()">
                        <i class="fas fa-redo"></i> Reset
                    </button>
                </div>
            </div>

            <!-- Results Count -->
            <div class="results-info">
                <span id="resultsCount">Showing <strong><?php echo count($products); ?></strong> products</span>
            </div>
            
            <?php if (empty($products)): ?>
                <div>
                    <i class="fas fa-box"></i>
                    <h3>No Products Found</h3>
                    <p>Start by adding your first product to the store.</p>
                    <button class="btn btn-primary" onclick="openAddProductModal()">
                        <i class="fas fa-plus"></i> Add Your First Product
                    </button>
                </div>
            <?php else: ?>
            <div class="cards-grid" id="productsGrid"></div>
            <div id="noResultsMessage" class="no-results" style="display: none;">
                <i class="fas fa-box"></i>
                <h3>No products found</h3>
                <p>Try adjusting your search or filter criteria.</p>
            </div>
            <?php endif; ?>
        </section>

        <!-- Product Data for Filtering (hidden) -->
        <script type="application/json" id="productsData">
        <?php echo json_encode($products); ?>
        </script>

        <!-- Orders Data for Filtering (hidden) -->
        <script type="application/json" id="ordersData">
        <?php echo json_encode($orders); ?>
        </script>

        <!-- Users Section -->
        <section id="users" class="content-section">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="fas fa-users"></i>
                    Users Management
                </h2>
            </div>
            
            <!-- Search and Filter Controls for Users -->
            <div class="product-filters" style="margin-bottom: 20px;">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="usersSearch" placeholder="Search by name or email..." autocomplete="off">
                </div>
                <div class="filter-group">
                    <select id="userStatusFilter" class="filter-select">
                        <option value="">All Users</option>
                        <option value="verified">Verified Email</option>
                        <option value="unverified">Unverified Email</option>
                    </select>
                    <button class="btn btn-secondary" id="resetUserFilters" onclick="resetUserFilters()">
                        <i class="fas fa-redo"></i> Reset
                    </button>
                </div>
            </div>

            <!-- Results Count -->
            <div class="results-info">
                <span id="usersResultsCount">Showing <strong><?php echo count($users); ?></strong> users</span>
            </div>
            
            <div>
                <table class="data-table" id="usersTable">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="usersList">
                        <?php foreach ($users as $user): ?>
                        <tr class="user-row" data-user-id="<?php echo $user['id']; ?>" data-status="<?php echo $user['email_verified'] ? 'verified' : 'unverified'; ?>">
                            <td data-label="Name"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                            <td data-label="Email"><?php echo htmlspecialchars($user['email']); ?></td>
                            <td data-label="Phone"><?php echo $user['phone'] ?? 'N/A'; ?></td>
                            <td data-label="Joined"><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                            <td data-label="Actions">
                                <div class="table-actions">
                                    <button class="btn btn-primary edit-user btn-sm" data-id="<?php echo $user['id']; ?>">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <form method="POST">
                                        <input type="hidden" name="action" value="delete_user">
                                        <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete this user?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <!-- No Users Message -->
                <div id="noUsersMessage" class="no-results" style="display: none;">
                    <i class="fas fa-users"></i>
                    <h3>No users found</h3>
                    <p>Try adjusting your search or filter criteria.</p>
                </div>
            </div>
        </section>

        <!-- Reviews Section -->
        <section id="reviews" class="content-section">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="fas fa-star"></i>
                    Product Reviews
                </h2>
            </div>
            
            <!-- Search and Filter Controls for Reviews -->
            <div class="product-filters" style="margin-bottom: 20px;">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="reviewsSearch" placeholder="Search by user, product, or comment..." autocomplete="off">
                </div>
                <div class="filter-group">
                    <select id="reviewRatingFilter" class="filter-select">
                        <option value="">All Ratings</option>
                        <option value="5">5 Stars</option>
                        <option value="4">4 Stars</option>
                        <option value="3">3 Stars</option>
                        <option value="2">2 Stars</option>
                        <option value="1">1 Star</option>
                    </select>
                    <button class="btn btn-secondary" id="resetReviewFilters" onclick="resetReviewFilters()">
                        <i class="fas fa-redo"></i> Reset
                    </button>
                </div>
            </div>

            <!-- Results Count -->
            <div class="results-info">
                <span id="reviewsResultsCount">Showing <strong><?php echo count($reviews); ?></strong> reviews</span>
            </div>
            
            <div>
                <table class="data-table" id="reviewsTable">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Product</th>
                            <th>Rating</th>
                            <th>Comment</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="reviewsList">
                        <?php foreach ($reviews as $review): ?>
                        <tr class="review-row" data-review-id="<?php echo $review['id']; ?>" data-rating="<?php echo $review['rating']; ?>">
                            <td data-label="User"><?php echo htmlspecialchars($review['first_name'] . ' ' . $review['last_name']); ?></td>
                            <td data-label="Product"><?php echo htmlspecialchars($review['product_name']); ?></td>
                            <td data-label="Rating">
                                <?php 
                                for ($i = 1; $i <= 5; $i++) {
                                    if ($i <= $review['rating']) {
                                        echo '<i class="fas fa-star"></i>';
                                    } else {
                                        echo '<i class="far fa-star"></i>';
                                    }
                                }
                                ?>
                            </td>
                            <td data-label="Comment"><?php echo htmlspecialchars(substr($review['comment'], 0, 40)); ?>...</td>
                            <td data-label="Date"><?php echo date('M j, Y', strtotime($review['created_at'])); ?></td>
                            <td data-label="Actions">
                                <div class="table-actions">
                                    <form method="POST">
                                        <input type="hidden" name="action" value="delete_review">
                                        <input type="hidden" name="id" value="<?php echo $review['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete this review?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <!-- No Reviews Message -->
                <div id="noReviewsMessage" class="no-results" style="display: none;">
                    <i class="fas fa-star"></i>
                    <h3>No reviews found</h3>
                    <p>Try adjusting your search or filter criteria.</p>
                </div>
            </div>
        </section>

        <!-- Messages Section -->
        <section id="messages" class="content-section">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="fas fa-envelope"></i>
                    Contact Messages
                </h2>
            </div>
            
            <!-- Search and Filter Controls for Messages -->
            <div class="product-filters" style="margin-bottom: 20px;">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="messagesSearch" placeholder="Search by name, email, or subject..." autocomplete="off">
                </div>
                <div class="filter-group">
                    <select id="messageStatusFilter" class="filter-select">
                        <option value="">All Messages</option>
                        <option value="unread">Unread</option>
                        <option value="read">Read</option>
                    </select>
                    <button class="btn btn-secondary" id="resetMessageFilters" onclick="resetMessageFilters()">
                        <i class="fas fa-redo"></i> Reset
                    </button>
                </div>
            </div>

            <!-- Results Count -->
            <div class="results-info">
                <span id="messagesResultsCount">Showing <strong><?php echo count($messages); ?></strong> messages</span>
            </div>
            
            <div>
                <table class="data-table" id="messagesTable">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Subject</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="messagesList">
                        <?php foreach ($messages as $message): ?>
                        <tr class="message-row" data-message-id="<?php echo $message['id']; ?>" data-status="<?php echo $message['status']; ?>">
                            <td data-label="Name"><?php echo htmlspecialchars($message['name']); ?></td>
                            <td data-label="Email"><?php echo htmlspecialchars($message['email']); ?></td>
                            <td data-label="Subject"><?php echo htmlspecialchars($message['subject']); ?></td>
                            <td data-label="Date"><?php echo date('M j, Y', strtotime($message['created_at'])); ?></td>
                            <td data-label="Status">
                                <span class="status-badge status-<?php echo $message['status']; ?>">
                                    <?php echo ucfirst($message['status']); ?>
                                </span>
                            </td>
                            <td data-label="Actions">
                                <div class="table-actions">
                                    <button type="button" class="btn btn-info btn-sm" onclick="openMessageModal(<?php echo $message['id']; ?>, '<?php echo addslashes(htmlspecialchars($message['name'])); ?>', '<?php echo addslashes(htmlspecialchars($message['email'])); ?>', '<?php echo addslashes(htmlspecialchars($message['subject'])); ?>', '<?php echo addslashes(htmlspecialchars($message['message'])); ?>')">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    <form method="POST">
                                        <input type="hidden" name="action" value="update_message_status">
                                        <input type="hidden" name="id" value="<?php echo $message['id']; ?>">
                                        <select name="status" onchange="this.form.submit()">
                                            <option value="read" <?php echo $message['status'] == 'read' ? 'selected' : ''; ?>>Read</option>
                                            <option value="unread" <?php echo $message['status'] == 'unread' ? 'selected' : ''; ?>>Unread</option>
                                        </select>
                                    </form>
                                    <form method="POST">
                                        <input type="hidden" name="action" value="delete_message">
                                        <input type="hidden" name="id" value="<?php echo $message['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete this message?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <!-- No Messages Message -->
                <div id="noMessagesMessage" class="no-results" style="display: none;">
                    <i class="fas fa-envelope"></i>
                    <h3>No messages found</h3>
                    <p>Try adjusting your search or filter criteria.</p>
                </div>
            </div>
        </section>

    <!-- Message Detail Modal -->
    <div id="messageModal" class="modal">
        <div class="modal-content" style="max-width: 700px; max-height: 90vh; overflow-y: auto;">
            <div class="modal-header">
                <h3><i class="fas fa-envelope" style="color: #d4af37; margin-right: 8px;"></i>Customer Message</h3>
                <button class="modal-close" onclick="closeMessageModal()">&times;</button>
            </div>
            <div class="modal-body">
                <!-- Message Details -->
                <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                    <div style="margin-bottom: 15px;">
                        <span style="color: #666; font-size: 0.9rem; font-weight: 600;">FROM</span>
                        <p id="messageFrom" style="margin: 5px 0; font-size: 1rem;"></p>
                    </div>
                    
                    <div style="margin-bottom: 15px;">
                        <span style="color: #666; font-size: 0.9rem; font-weight: 600;">EMAIL</span>
                        <p id="messageEmail" style="margin: 5px 0; font-size: 1rem;"></p>
                    </div>
                    
                    <div style="margin-bottom: 15px;">
                        <span style="color: #666; font-size: 0.9rem; font-weight: 600;">SUBJECT</span>
                        <p id="messageSubject" style="margin: 5px 0; font-size: 1rem;"></p>
                    </div>
                    
                    <div>
                        <span style="color: #666; font-size: 0.9rem; font-weight: 600;">MESSAGE</span>
                        <p id="messageContent" style="margin: 5px 0; font-size: 1rem; white-space: pre-wrap; line-height: 1.6;"></p>
                    </div>
                </div>

                <!-- Reply Form -->
                <div style="border-top: 2px solid #e9ecef; padding-top: 20px;">
                    <h4 style="color: #1a1a1a; margin-bottom: 15px;"><i class="fas fa-reply"></i> Send Reply</h4>
                    
                    <form id="replyForm" style="display: none;">
                        <input type="hidden" id="messageId" name="message_id" value="">
                        <input type="hidden" id="replyEmail" name="customer_email" value="">
                        <input type="hidden" id="replyName" name="customer_name" value="">
                        
                        <div class="form-group">
                            <label for="replySubject">Reply Subject</label>
                            <input type="text" id="replySubject" name="subject" placeholder="Re: Customer Subject" autocomplete="off" required style="width: 100%; padding: 10px; border: 1px solid #e9ecef; border-radius: 6px; font-family: inherit;">
                        </div>
                        
                        <div class="form-group">
                            <label for="replyText">Your Reply</label>
                            <textarea id="replyText" name="reply_message" placeholder="Type your reply here..." required style="width: 100%; padding: 10px; border: 1px solid #e9ecef; border-radius: 6px; font-family: inherit; min-height: 150px;"></textarea>
                        </div>
                        
                        <div style="display: flex; gap: 10px; justify-content: flex-end;">
                            <button type="button" onclick="closeMessageModal()" class="btn btn-secondary">Cancel</button>
                            <button type="submit" class="btn btn-primary" id="sendReplyBtn">
                                <i class="fas fa-paper-plane"></i> Send Reply
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    </main>
</div>

    <!-- User Edit Modal -->
    <div id="userModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-user-edit" style="color: #d4af37; margin-right: 8px;"></i>Edit User</h3>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <form id="userForm" method="POST">
                    <input type="hidden" name="action" value="update_user">
                    <input type="hidden" id="userId" name="id" value="">
                    
                    <div class="form-group">
                        <label for="userFirstName">First Name</label>
                        <input type="text" id="userFirstName" name="first_name" autocomplete="given-name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="userLastName">Last Name</label>
                        <input type="text" id="userLastName" name="last_name" autocomplete="family-name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="userEmail">Email</label>
                        <input type="email" id="userEmail" name="email" autocomplete="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="userPhone">Phone</label>
                        <input type="text" id="userPhone" name="phone" autocomplete="tel">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" onclick="closeModal('userModal')">Cancel</button>
                <button type="submit" form="userForm" class="btn btn-primary">Save Changes</button>
            </div>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div id="addProductModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-plus-circle" style="color: #d4af37; margin-right: 8px;"></i>Add New Product</h3>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <form id="addProductForm" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add_product">
                    
                    <div class="form-group">
                        <label for="addProductName">Product Name</label>
                        <input type="text" id="addProductName" name="name" autocomplete="off" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="addProductDescription">Description</label>
                        <textarea id="addProductDescription" name="description" required></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="addProductPrice">Price ($)</label>
                            <input type="number" id="addProductPrice" step="0.01" name="price" autocomplete="off" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="addProductStock">Stock Quantity</label>
                            <input type="number" id="addProductStock" name="stock_quantity" autocomplete="off" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="addProductCategory">Category</label>
                        <select id="addProductCategory" name="category" required>
                            <option value="">Select Category</option>
                            <option value="bracelets">Bracelets</option>
                            <option value="digital_watches">Digital Watches</option>
                            <option value="gold_chains">Gold Chains</option>
                            <option value="normal_watches">Normal Watches</option>
                            <option value="silver_chains">Silver Chains</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="addProductImage">Product Image</label>
                        <input type="file" id="addProductImage" name="image" accept="image/*" required onchange="previewImage(event, 'addImagePreview')">
                        <img id="addImagePreview" class="image-preview" src="#" alt="Image Preview">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" onclick="closeModal('addProductModal')">Cancel</button>
                <button type="submit" form="addProductForm" class="btn btn-primary">Add Product</button>
            </div>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div id="editProductModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-edit" style="color: #d4af37; margin-right: 8px;"></i>Edit Product</h3>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <form id="editProductForm" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="update_product">
                    <input type="hidden" id="editProductId" name="id" value="">
                    <input type="hidden" id="editProductCurrentImage" name="current_image_url" value="">
                    
                    <div class="form-group">
                        <label for="editProductName">Product Name</label>
                        <input type="text" id="editProductName" name="name" autocomplete="off" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="editProductDescription">Description</label>
                        <textarea id="editProductDescription" name="description" required></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="editProductPrice">Price ($)</label>
                            <input type="number" step="0.01" id="editProductPrice" name="price" autocomplete="off" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="editProductStock">Stock Quantity</label>
                            <input type="number" id="editProductStock" name="stock_quantity" autocomplete="off" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="editProductCategory">Category</label>
                        <select id="editProductCategory" name="category" required>
                            <option value="">Select Category</option>
                            <option value="bracelets">Bracelets</option>
                            <option value="digital_watches">Digital Watches</option>
                            <option value="gold_chains">Gold Chains</option>
                            <option value="normal_watches">Normal Watches</option>
                            <option value="silver_chains">Silver Chains</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <span style="display: block; margin-bottom: 8px; font-weight: 600; color: #1a1a1a; font-size: 0.9rem;">Current Image</span>
                        <div id="currentImageContainer" style="text-align: center;">
                            <img id="currentImagePreview" class="product-image" src="" alt="Current Image" style="max-width: 150px; margin-bottom: 10px;">
                            <p id="noImageText" style="display: none; color: #666;">No image available</p>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="editProductImage">New Product Image (Leave empty to keep current)</label>
                        <input type="file" id="editProductImage" name="image" accept="image/*" onchange="previewImage(event, 'editImagePreview')">
                        <img id="editImagePreview" class="image-preview" src="#" alt="Image Preview">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" onclick="closeModal('editProductModal')">Cancel</button>
                <button type="submit" form="editProductForm" class="btn btn-primary">Update Product</button>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="js/admin.js"></script>
    <script src="js/product-filters.js"></script>
</body>
</html>