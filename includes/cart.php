<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page
    header('Location: /login.php');
    exit();
}

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'add':
            // Add product to cart
            $product_id = (int)$_POST['product_id'];
            $category = $_POST['category'] ?? '';
            $product_name = $_POST['product_name'] ?? '';
            $product_price = (float)($_POST['product_price'] ?? 0);
            $product_image = $_POST['product_image'] ?? '';
            $quantity = (int)($_POST['quantity'] ?? 1);

            // Check if already in cart
            $sql = "SELECT id, quantity FROM shopping_cart WHERE user_id = ? AND product_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user_id, $product_id]);
            $existing = $stmt->fetch();
            
            if ($existing) {
                // Update quantity
                $new_quantity = $existing['quantity'] + $quantity;
                $sql = "UPDATE shopping_cart SET quantity = ? WHERE user_id = ? AND product_id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$new_quantity, $user_id, $product_id]);
            } else {
                // Insert new item
                $sql = "INSERT INTO shopping_cart (user_id, product_id, category, product_name, product_price, product_image, quantity) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$user_id, $product_id, $category, $product_name, $product_price, $product_image, $quantity]);
            }

            echo json_encode(['success' => true, 'message' => 'Item added to cart']);
            break;

        case 'remove':
            // Remove product from cart
            $product_id = (int)$_POST['product_id'];

            $sql = "DELETE FROM shopping_cart WHERE user_id = ? AND product_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user_id, $product_id]);

            echo json_encode(['success' => true, 'message' => 'Item removed from cart']);
            break;

        case 'update':
            // Update product quantity in cart
            $product_id = (int)$_POST['product_id'];
            $quantity = (int)$_POST['quantity'] ?? 1;

            if ($quantity <= 0) {
                // Delete if quantity is 0 or less
                $sql = "DELETE FROM shopping_cart WHERE user_id = ? AND product_id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$user_id, $product_id]);
            } else {
                $sql = "UPDATE shopping_cart SET quantity = ? WHERE user_id = ? AND product_id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$quantity, $user_id, $product_id]);
            }

            echo json_encode(['success' => true, 'message' => 'Cart updated']);
            break;

        case 'get':
            // Get all cart items for user
            $sql = "SELECT * FROM shopping_cart WHERE user_id = ? ORDER BY updated_at DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user_id]);
            $items = $stmt->fetchAll();

            $total = 0;
            foreach ($items as $item) {
                $total += $item['product_price'] * $item['quantity'];
            }

            echo json_encode(['success' => true, 'items' => $items, 'total' => $total]);
            break;

        case 'count':
            // Get cart item count
            $sql = "SELECT COUNT(*) as count FROM shopping_cart WHERE user_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user_id]);
            $result = $stmt->fetch();

            echo json_encode(['success' => true, 'count' => $result['count']]);
            break;

        case 'clear':
            // Clear entire cart
            $sql = "DELETE FROM shopping_cart WHERE user_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user_id]);

            echo json_encode(['success' => true, 'message' => 'Cart cleared']);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
