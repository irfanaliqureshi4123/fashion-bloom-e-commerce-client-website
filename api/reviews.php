<?php
/**
 * Product Reviews API
 * Handles product review operations
 */

session_start();
require_once(dirname(dirname(__FILE__)) . '/includes/db.php');

header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? null;
$product_id = $_GET['product_id'] ?? $_POST['product_id'] ?? null;

try {
    switch ($action) {
        case 'get_reviews':
            getProductReviews($product_id);
            break;

        case 'get_rating_summary':
            getRatingSummary($product_id);
            break;

        case 'submit_review':
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Please log in to submit a review']);
                exit;
            }
            submitReview();
            break;

        case 'vote_helpful':
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Please log in to vote']);
                exit;
            }
            voteOnReview();
            break;

        case 'get_user_review':
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'review' => null]);
                exit;
            }
            getUserReview($product_id);
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

/**
 * Get product reviews with pagination
 */
function getProductReviews($product_id) {
    global $pdo;
    
    if (!$product_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Product ID required']);
        exit;
    }

    try {
        $sort = $_GET['sort'] ?? 'recent'; // recent, helpful, highest, lowest
        $page = max(1, (int)($_GET['page'] ?? 1));
        $per_page = 5;
        $offset = ($page - 1) * $per_page;

        // Build sort query
        $order_by = 'pr.created_at DESC';
        if ($sort === 'helpful') {
            $order_by = 'pr.helpful_count DESC';
        } elseif ($sort === 'highest') {
            $order_by = 'pr.rating DESC';
        } elseif ($sort === 'lowest') {
            $order_by = 'pr.rating ASC';
        }

        // Use integer directly in LIMIT/OFFSET instead of placeholders
        $sql = "SELECT pr.*, u.first_name, u.last_name
                FROM product_reviews pr
                LEFT JOIN users u ON pr.user_id = u.id
                WHERE pr.product_id = ?
                ORDER BY $order_by
                LIMIT " . intval($per_page) . " OFFSET " . intval($offset);

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$product_id]);
        $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Handle missing user names
        foreach ($reviews as &$review) {
            if (empty($review['first_name'])) {
                $review['first_name'] = 'User';
                $review['last_name'] = '#' . $review['user_id'];
            }
        }

        // Get total count
        $sql_count = "SELECT COUNT(*) as total FROM product_reviews WHERE product_id = ?";
        $stmt_count = $pdo->prepare($sql_count);
        $stmt_count->execute([$product_id]);
        $count_result = $stmt_count->fetch();
        $total = $count_result['total'] ?? 0;

        echo json_encode([
            'success' => true,
            'reviews' => $reviews,
            'pagination' => [
                'current_page' => $page,
                'total_reviews' => intval($total),
                'per_page' => $per_page,
                'total_pages' => ceil(intval($total) / $per_page)
            ]
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error loading reviews',
            'error' => $e->getMessage()
        ]);
    }
}
/**
 * Get rating summary for product
 */
function getRatingSummary($product_id) {
    global $pdo;
    
    if (!$product_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Product ID required']);
        exit;
    }

    try {
        $sql = "SELECT * FROM product_rating_summary WHERE product_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$product_id]);
        $summary = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$summary) {
            // Create empty summary if doesn't exist
            try {
                $sql_insert = "INSERT INTO product_rating_summary (product_id) VALUES (?)";
                $stmt_insert = $pdo->prepare($sql_insert);
                $stmt_insert->execute([$product_id]);
            } catch (Exception $e) {
                // If insert fails, it's okay - just use empty values
            }
            
            $summary = [
                'product_id' => $product_id,
                'total_reviews' => 0,
                'average_rating' => 0,
                'rating_5' => 0,
                'rating_4' => 0,
                'rating_3' => 0,
                'rating_2' => 0,
                'rating_1' => 0
            ];
        } else {
            // Ensure all fields exist with proper null-safety
            if (!isset($summary['rating_5'])) $summary['rating_5'] = 0;
            if (!isset($summary['rating_4'])) $summary['rating_4'] = 0;
            if (!isset($summary['rating_3'])) $summary['rating_3'] = 0;
            if (!isset($summary['rating_2'])) $summary['rating_2'] = 0;
            if (!isset($summary['rating_1'])) $summary['rating_1'] = 0;
            $summary['product_id'] = $summary['product_id'] ?? $product_id;
            $summary['total_reviews'] = $summary['total_reviews'] ?? 0;
            $summary['average_rating'] = $summary['average_rating'] ?? 0;
        }
        
        // Map database field names to expected names for frontend
        $response_summary = [
            'product_id' => intval($summary['product_id']),
            'total_reviews' => intval($summary['total_reviews']),
            'average_rating' => floatval($summary['average_rating']),
            'rating_5' => intval($summary['rating_5']),
            'rating_4' => intval($summary['rating_4']),
            'rating_3' => intval($summary['rating_3']),
            'rating_2' => intval($summary['rating_2']),
            'rating_1' => intval($summary['rating_1'])
        ];

        echo json_encode([
            'success' => true,
            'summary' => $response_summary
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error loading rating summary',
            'error' => $e->getMessage()
        ]);
    }
}

/**
 * Submit a review
 */
function submitReview() {
    global $pdo;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $product_id = $data['product_id'] ?? null;
    $rating = (int)($data['rating'] ?? 0);
    $title = trim($data['title'] ?? '');
    $review_text = trim($data['review_text'] ?? '');
    $user_id = $_SESSION['user_id'];

    // Validation
    if (!$product_id || $rating < 1 || $rating > 5 || empty($title)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid review data']);
        exit;
    }

    if (strlen($title) < 5 || strlen($title) > 255) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Title must be between 5 and 255 characters']);
        exit;
    }

    if (strlen($review_text) > 2000) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Review must not exceed 2000 characters']);
        exit;
    }

    // Check if user already reviewed this product
    $sql_existing = "SELECT id FROM product_reviews WHERE product_id = ? AND user_id = ?";
    $stmt_existing = $pdo->prepare($sql_existing);
    $stmt_existing->execute([$product_id, $user_id]);
    if ($stmt_existing->fetch()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'You have already reviewed this product']);
        exit;
    }

    // Check if user has purchased this product
    $sql_purchase = "SELECT id FROM order_items 
                     WHERE product_id = ? 
                     AND order_id IN (SELECT id FROM orders WHERE user_id = ?)";
    $stmt_purchase = $pdo->prepare($sql_purchase);
    $stmt_purchase->execute([$product_id, $user_id]);
    $verified_purchase = (bool)$stmt_purchase->fetch();

    try {
        // Temporarily disable foreign key checks to allow reviews for products not in products table
        $pdo->exec("SET FOREIGN_KEY_CHECKS=0");

        // Insert review (without verified_purchase column since it doesn't exist in our schema)
        $sql_insert = "INSERT INTO product_reviews 
                       (product_id, user_id, rating, title, review)
                       VALUES (?, ?, ?, ?, ?)";
        $stmt_insert = $pdo->prepare($sql_insert);
        $stmt_insert->execute([
            $product_id,
            $user_id,
            $rating,
            $title,
            $review_text
        ]);

        $review_id = $pdo->lastInsertId();
        
        // Re-enable foreign key checks
        $pdo->exec("SET FOREIGN_KEY_CHECKS=1");

        // Update rating summary
        updateRatingSummary($product_id);

        echo json_encode([
            'success' => true,
            'message' => 'Review submitted successfully',
            'review_id' => $review_id,
            'verified_purchase' => $verified_purchase
        ]);
    } catch (Exception $e) {
        $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error submitting review',
            'error' => $e->getMessage()
        ]);
    }
}

/**
 * Vote on a review (helpful/unhelpful)
 */
function voteOnReview() {
    global $pdo;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $review_id = (int)($data['review_id'] ?? 0);
    $vote_type = $data['vote_type'] ?? null; // 'helpful' or 'unhelpful'
    $user_id = $_SESSION['user_id'];

    if (!in_array($vote_type, ['helpful', 'unhelpful'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid vote type']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Check if user already voted (using correct table name)
        $sql_check = "SELECT vote_type FROM review_helpful_votes WHERE review_id = ? AND user_id = ?";
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_check->execute([$review_id, $user_id]);
        $existing_vote = $stmt_check->fetch(PDO::FETCH_ASSOC);

        if ($existing_vote) {
            // Remove old vote
            $old_type = $existing_vote['vote_type'];
            $sql_delete = "DELETE FROM review_helpful_votes WHERE review_id = ? AND user_id = ?";
            $stmt_delete = $pdo->prepare($sql_delete);
            $stmt_delete->execute([$review_id, $user_id]);

            // Update review counts
            $col = $old_type === 'helpful' ? 'helpful_count' : 'unhelpful_count';
            $sql_update = "UPDATE product_reviews SET $col = $col - 1 WHERE id = ?";
            $stmt_update = $pdo->prepare($sql_update);
            $stmt_update->execute([$review_id]);

            // If same vote, just remove it
            if ($existing_vote['vote_type'] === $vote_type) {
                $pdo->commit();
                echo json_encode(['success' => true, 'message' => 'Vote removed']);
                exit;
            }
        }

        // Insert new vote (using correct table name)
        $sql_insert = "INSERT INTO review_helpful_votes (review_id, user_id, vote_type) VALUES (?, ?, ?)";
        $stmt_insert = $pdo->prepare($sql_insert);
        $stmt_insert->execute([$review_id, $user_id, $vote_type]);

        // Update review counts
        $col = $vote_type === 'helpful' ? 'helpful_count' : 'unhelpful_count';
        $sql_update = "UPDATE product_reviews SET $col = $col + 1 WHERE id = ?";
        $stmt_update = $pdo->prepare($sql_update);
        $stmt_update->execute([$review_id]);

        $pdo->commit();

        echo json_encode(['success' => true, 'message' => 'Vote recorded']);

    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error recording vote']);
    }
}

/**
 * Get user's review for a product
 */
function getUserReview($product_id) {
    global $pdo;
    
    $user_id = $_SESSION['user_id'] ?? null;
    
    if (!$user_id || !$product_id) {
        echo json_encode(['success' => true, 'review' => null]);
        exit;
    }

    $sql = "SELECT * FROM product_reviews WHERE product_id = ? AND user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$product_id, $user_id]);
    $review = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'review' => $review
    ]);
}

/**
 * Update rating summary for a product
 */
function updateRatingSummary($product_id) {
    global $pdo;

    try {
        $sql = "SELECT 
                    COUNT(*) as total_reviews,
                    AVG(rating) as average_rating,
                    SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as rating_5,
                    SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as rating_4,
                    SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as rating_3,
                    SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as rating_2,
                    SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as rating_1
                FROM product_reviews
                WHERE product_id = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$product_id]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        // Use correct column names from the database
        $sql_update = "UPDATE product_rating_summary 
                       SET total_reviews = ?,
                           average_rating = ?,
                           rating_5 = ?,
                           rating_4 = ?,
                           rating_3 = ?,
                           rating_2 = ?,
                           rating_1 = ?
                       WHERE product_id = ?";

        $stmt_update = $pdo->prepare($sql_update);
        $stmt_update->execute([
            intval($stats['total_reviews'] ?? 0),
            round(floatval($stats['average_rating'] ?? 0), 2),
            intval($stats['rating_5'] ?? 0),
            intval($stats['rating_4'] ?? 0),
            intval($stats['rating_3'] ?? 0),
            intval($stats['rating_2'] ?? 0),
            intval($stats['rating_1'] ?? 0),
            $product_id
        ]);
    } catch (Exception $e) {
        // Silently fail if update doesn't work
        error_log("Error updating rating summary: " . $e->getMessage());
    }
}

?>
