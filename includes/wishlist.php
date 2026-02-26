<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? null;
$isGuest = $user_id === null;
$session_id = session_id();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Helper function to check if session_id column exists
function hasSessionIdColumn($pdo) {
    try {
        $result = $pdo->query("DESCRIBE wishlist");
        $columns = $result->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $col) {
            if ($col['Field'] === 'session_id') {
                return true;
            }
        }
        return false;
    } catch (Exception $e) {
        return false;
    }
}

$useSessionId = hasSessionIdColumn($pdo);

try {
    switch ($action) {
        case 'add':
            // Add product to wishlist
            $product_id = (int)$_POST['product_id'];
            $category = $_POST['category'] ?? '';
            $product_name = $_POST['product_name'] ?? '';
            $product_price = (float)$_POST['product_price'] ?? 0;
            $product_image = $_POST['product_image'] ?? '';

            if ($isGuest) {
                // Guest user: store in database with session_id (if column exists)
                if ($useSessionId) {
                    $sql = "SELECT id FROM wishlist WHERE session_id = ? AND product_id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$session_id, $product_id]);
                    
                    if ($stmt->rowCount() > 0) {
                        echo json_encode(['success' => false, 'message' => 'Item already in wishlist']);
                        exit();
                    }

                    $sql = "INSERT INTO wishlist (session_id, product_id, category, product_name, product_price, product_image, is_guest, user_id) 
                            VALUES (?, ?, ?, ?, ?, ?, TRUE, NULL)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$session_id, $product_id, $category, $product_name, $product_price, $product_image]);
                } else {
                    // Fallback: store in PHP session array
                    if (!isset($_SESSION['guest_wishlist'])) {
                        $_SESSION['guest_wishlist'] = [];
                    }
                    
                    foreach ($_SESSION['guest_wishlist'] as $item) {
                        if ($item['product_id'] == $product_id) {
                            echo json_encode(['success' => false, 'message' => 'Item already in wishlist']);
                            exit();
                        }
                    }
                    
                    $_SESSION['guest_wishlist'][] = [
                        'product_id' => $product_id,
                        'category' => $category,
                        'product_name' => $product_name,
                        'product_price' => $product_price,
                        'product_image' => $product_image,
                        'created_at' => date('Y-m-d H:i:s')
                    ];
                }

                echo json_encode(['success' => true, 'message' => 'Item added to wishlist', 'is_guest' => true]);
            } else {
                // Logged-in user: store in database
                $sql = "SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$user_id, $product_id]);
                
                if ($stmt->rowCount() > 0) {
                    echo json_encode(['success' => false, 'message' => 'Item already in wishlist']);
                    exit();
                }

                $sql = "INSERT INTO wishlist (user_id, product_id, category, product_name, product_price, product_image) 
                        VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$user_id, $product_id, $category, $product_name, $product_price, $product_image]);

                echo json_encode(['success' => true, 'message' => 'Item added to wishlist', 'is_guest' => false]);
            }
            break;

        case 'remove':
            // Remove product from wishlist
            $product_id = (int)$_POST['product_id'];

            if ($isGuest) {
                if ($useSessionId) {
                    $sql = "DELETE FROM wishlist WHERE session_id = ? AND product_id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$session_id, $product_id]);
                } else {
                    if (isset($_SESSION['guest_wishlist'])) {
                        $_SESSION['guest_wishlist'] = array_filter($_SESSION['guest_wishlist'], function($item) use ($product_id) {
                            return $item['product_id'] != $product_id;
                        });
                        $_SESSION['guest_wishlist'] = array_values($_SESSION['guest_wishlist']);
                    }
                }
                echo json_encode(['success' => true, 'message' => 'Item removed from wishlist', 'is_guest' => true]);
            } else {
                $sql = "DELETE FROM wishlist WHERE user_id = ? AND product_id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$user_id, $product_id]);
                echo json_encode(['success' => true, 'message' => 'Item removed from wishlist', 'is_guest' => false]);
            }
            break;

        case 'get':
            // Get all wishlist items for user
            if ($isGuest) {
                if ($useSessionId) {
                    $sql = "SELECT * FROM wishlist WHERE session_id = ? ORDER BY created_at DESC";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$session_id]);
                    $items = $stmt->fetchAll();
                } else {
                    $items = $_SESSION['guest_wishlist'] ?? [];
                }
                echo json_encode(['success' => true, 'items' => $items, 'is_guest' => true]);
            } else {
                $sql = "SELECT * FROM wishlist WHERE user_id = ? ORDER BY created_at DESC";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$user_id]);
                $items = $stmt->fetchAll();
                echo json_encode(['success' => true, 'items' => $items, 'is_guest' => false]);
            }
            break;

        case 'check':
            // Check if product is in wishlist
            $product_id = (int)($_POST['product_id'] ?? $_GET['product_id']);

            if ($isGuest) {
                if ($useSessionId) {
                    $sql = "SELECT id FROM wishlist WHERE session_id = ? AND product_id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$session_id, $product_id]);
                    $is_in_wishlist = $stmt->rowCount() > 0;
                } else {
                    $is_in_wishlist = false;
                    if (isset($_SESSION['guest_wishlist'])) {
                        foreach ($_SESSION['guest_wishlist'] as $item) {
                            if ($item['product_id'] == $product_id) {
                                $is_in_wishlist = true;
                                break;
                            }
                        }
                    }
                }
                echo json_encode(['success' => true, 'in_wishlist' => $is_in_wishlist, 'is_guest' => true]);
            } else {
                $sql = "SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$user_id, $product_id]);
                $is_in_wishlist = $stmt->rowCount() > 0;
                echo json_encode(['success' => true, 'in_wishlist' => $is_in_wishlist, 'is_guest' => false]);
            }
            break;

        case 'count':
            // Get wishlist item count
            if ($isGuest) {
                if ($useSessionId) {
                    $sql = "SELECT COUNT(*) as count FROM wishlist WHERE session_id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$session_id]);
                    $result = $stmt->fetch();
                    $count = $result['count'];
                } else {
                    $count = isset($_SESSION['guest_wishlist']) ? count($_SESSION['guest_wishlist']) : 0;
                }
                echo json_encode(['success' => true, 'count' => $count, 'is_guest' => true]);
            } else {
                $sql = "SELECT COUNT(*) as count FROM wishlist WHERE user_id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$user_id]);
                $result = $stmt->fetch();
                echo json_encode(['success' => true, 'count' => $result['count'], 'is_guest' => false]);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
