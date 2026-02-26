<?php
/**
 * Stripe Payment Processor
 * Handles Stripe payment processing using Stripe API
 */

session_start();
require_once(dirname(dirname(__FILE__)) . '/config/config.php');
require_once('db.php');
require_once(dirname(dirname(__FILE__)) . '/vendor/autoload.php');

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['user_id'];
$order_id = $data['order_id'] ?? null;
$payment_method_id = $data['payment_method_id'] ?? null;
$email = $data['email'] ?? null;
$amount = $data['amount'] ?? null;
$currency = $data['currency'] ?? 'pkr';

// Validate inputs
if (!$order_id || !$payment_method_id || !$email || !$amount) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Get Stripe keys
$stripe_secret_key = STRIPE_SECRET_KEY;
if (!$stripe_secret_key || $stripe_secret_key === 'sk_test_YOUR_STRIPE_SECRET_KEY_HERE') {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Stripe not configured properly']);
    exit;
}

try {
    // Initialize Stripe API
    \Stripe\Stripe::setApiKey($stripe_secret_key);

    // Verify order exists and belongs to user
    $sql = "SELECT * FROM orders WHERE id = ? AND user_id = ? AND payment_method = 'card'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$order_id, $user_id]);
    $order = $stmt->fetch();

    if (!$order) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid order']);
        exit;
    }

    // Create a Stripe Payment Intent with the payment method
    $charge_response = \Stripe\PaymentIntent::create([
        'amount' => (int)($amount * 100), // Stripe uses cents
        'currency' => strtolower($currency),
        'payment_method' => $payment_method_id,
        'confirm' => true,
        'description' => 'Order #' . $order['order_number'],
        'receipt_email' => $email,
        'return_url' => STRIPE_RETURN_URL ?? (APP_URL . '/order-confirmation.php'),
        'automatic_payment_methods' => [
            'enabled' => true,
            'allow_redirects' => 'never'
        ],
        'metadata' => [
            'order_id' => $order_id,
            'user_id' => $user_id
        ]
    ]);

    // Check if payment was successful
    if ($charge_response->status === 'succeeded') {
        // Update order status
        $sql = "UPDATE orders SET payment_status = 'completed', status = 'confirmed', 
                stripe_payment_id = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$charge_response->id, $order_id]);

        // Send order confirmation email after successful payment
        try {
            require_once('email.php');
            
            // Get order details
            $sql_order = "SELECT * FROM orders WHERE id = ?";
            $stmt_order = $pdo->prepare($sql_order);
            $stmt_order->execute([$order_id]);
            $order_details = $stmt_order->fetch(PDO::FETCH_ASSOC);
            
            // Get order items
            $sql_items = "SELECT product_name, product_price, quantity FROM order_items WHERE order_id = ?";
            $stmt_items = $pdo->prepare($sql_items);
            $stmt_items->execute([$order_id]);
            $email_order_items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
            
            sendOrderConfirmationEmail(
                $order_details['email'],
                $order_details['first_name'],
                $order_details['order_number'],
                $order_id,
                $email_order_items,
                $order_details['subtotal'],
                $order_details['tax'],
                $order_details['shipping'],
                $order_details['total_price']
            );
        } catch (Exception $e) {
            // Log email error but don't fail the payment
            error_log("Failed to send order confirmation email after payment: " . $e->getMessage());
        }

        // Log transaction
        $log_entry = [
            'user_id' => $user_id,
            'order_id' => $order_id,
            'payment_method' => 'stripe',
            'amount' => $amount,
            'status' => 'completed',
            'stripe_charge_id' => $charge_response->id,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        error_log(json_encode($log_entry));

        echo json_encode([
            'success' => true,
            'message' => 'Payment processed successfully',
            'charge_id' => $charge_response->id,
            'order_id' => $order_id
        ]);
    } else if ($charge_response->status === 'requires_action') {
        // Payment requires additional action (3D Secure, etc.)
        echo json_encode([
            'success' => false,
            'requires_action' => true,
            'client_secret' => $charge_response->client_secret,
            'message' => 'Additional action required'
        ]);
    } else {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Payment failed. Please try again.',
            'status' => $charge_response->status
        ]);
    }

} catch (\Stripe\Exception\CardException $e) {
    // Card was declined
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Card declined: ' . $e->getError()->message
    ]);
} catch (\Stripe\Exception\RateLimitException $e) {
    // Too many requests made to the API too quickly
    http_response_code(429);
    echo json_encode([
        'success' => false,
        'message' => 'Too many requests. Please try again later.'
    ]);
} catch (\Stripe\Exception\InvalidRequestException $e) {
    // Invalid parameters
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid payment details: ' . $e->getError()->message
    ]);
} catch (\Stripe\Exception\AuthenticationException $e) {
    // Authentication with Stripe's API failed
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Authentication error. Please contact support.'
    ]);
} catch (\Stripe\Exception\ApiConnectionException $e) {
    // Network communication with Stripe failed
    http_response_code(503);
    echo json_encode([
        'success' => false,
        'message' => 'Network error. Please try again.'
    ]);
} catch (\Stripe\Exception\ApiErrorException $e) {
    // Generic API error
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error processing payment: ' . $e->getError()->message
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error processing payment: ' . $e->getMessage()
    ]);
}
?>
