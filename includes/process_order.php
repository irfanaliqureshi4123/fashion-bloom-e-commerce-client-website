<?php
/**
 * Process Order - Backend Payment Processor
 * Handles different payment methods and creates orders
 */

session_start();
require_once(dirname(dirname(__FILE__)) . '/config/config.php');
require_once('db.php');

header('Content-Type: application/json');

// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['user_id'];
$payment_method = $data['payment_method'] ?? null;

if (!$payment_method) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Payment method not specified']);
    exit;
}

// Get shipping info from session
$shipping_info = $_SESSION['shipping_info'] ?? null;
if (!$shipping_info) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Shipping information missing']);
    exit;
}

try {
    // Get cart items
    $sql = "SELECT * FROM shopping_cart WHERE user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll();

    if (empty($cart_items)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Cart is empty']);
        exit;
    }

    // Calculate totals
    $subtotal = 0;
    foreach ($cart_items as $item) {
        $subtotal += $item['product_price'] * $item['quantity'];
    }

    // Validate that we have items with valid prices
    if ($subtotal <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid cart items or prices. Please check your cart and try again.']);
        exit;
    }

    $tax = $subtotal * TAX_RATE;
    $shipping = SHIPPING_COST;
    $total = $subtotal + $tax + $shipping;

    // Determine order status based on payment method
    $order_status = 'pending';
    $payment_status = 'pending';

    // Process based on payment method
    switch ($payment_method) {
        case 'cod':
            // Cash on Delivery
            $order_status = 'confirmed';
            $payment_status = 'pending';
            break;

        case 'card':
            // Credit/Debit Card Processing via Stripe
            // Card details will be processed by Stripe Elements
            $payment_status = 'pending';
            $order_status = 'pending';
            break;

        case 'bank_transfer':
            // Bank Transfer
            $payment_status = 'pending';
            $order_status = 'waiting_payment';

            // Send email with bank details to customer
            $to = $shipping_info['email'] ?? $_SESSION['email'];
            $subject = "Payment Instructions - Fashion Bloom Order";
            $message = "
                <h2>Payment Instructions</h2>
                <p>Please transfer PKR " . number_format($total) . " to the following bank account:</p>
                <p>
                    <strong>Account Holder:</strong> Fashion Bloom<br>
                    <strong>Bank Name:</strong> NBP (National Bank of Pakistan)<br>
                    <strong>Account Number:</strong> 1234567890<br>
                    <strong>IBAN:</strong> PK36ABNA0000001234567890<br>
                    <strong>Swift Code:</strong> ABNAPKKA<br>
                </p>
                <p>Please mention your order ID as reference: {ORDER_ID}</p>
            ";

            break;

        case 'stripe':
            // Stripe Payment - will be handled on separate page
            $payment_status = 'pending';
            $order_status = 'pending_stripe_payment';
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid payment method']);
            exit;
    }

    // Begin transaction
    $pdo->beginTransaction();

    try {
        // Validate total price before inserting
        if ($total <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Order total must be greater than 0']);
            exit;
        }

        // Create order
        $sql = "INSERT INTO orders (
                    user_id, 
                    order_number, 
                    subtotal, 
                    tax, 
                    shipping, 
                    total_price, 
                    status, 
                    payment_method, 
                    payment_status,
                    first_name,
                    last_name,
                    email,
                    phone,
                    address,
                    city,
                    postal_code,
                    notes,
                    created_at
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                )";

        $order_number = 'ORD-' . date('Ymd') . '-' . rand(10000, 99999);

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $user_id,
            $order_number,
            $subtotal,
            $tax,
            $shipping,
            $total,
            $order_status,
            $payment_method,
            $payment_status,
            $shipping_info['first_name'] ?? null,
            $shipping_info['last_name'] ?? null,
            $shipping_info['email'] ?? null,
            $shipping_info['phone'] ?? null,
            $shipping_info['address'] ?? null,
            $shipping_info['city'] ?? null,
            $shipping_info['postal'] ?? null,
            $shipping_info['notes'] ?? null,
            date('Y-m-d H:i:s')
        ]);

        $order_id = $pdo->lastInsertId();

        // Add order items
        $sql = "INSERT INTO order_items (order_id, product_id, product_name, product_price, quantity, category) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);

        foreach ($cart_items as $item) {
            $stmt->execute([
                $order_id,
                $item['product_id'],
                $item['product_name'],
                $item['product_price'],
                $item['quantity'],
                $item['category']
            ]);
        }

        // Clear shopping cart
        $sql = "DELETE FROM shopping_cart WHERE user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id]);

        // Clear shipping info from session
        unset($_SESSION['shipping_info']);

        // Commit transaction
        $pdo->commit();

        // Send order confirmation email
        try {
            require_once('email.php');
            $customer_email = $shipping_info['email'] ?? $_SESSION['email'];
            $customer_name = $shipping_info['first_name'] ?? 'Valued Customer';
            
            // Get order items for email
            $sql_items = "SELECT product_name, product_price, quantity FROM order_items WHERE order_id = ?";
            $stmt_items = $pdo->prepare($sql_items);
            $stmt_items->execute([$order_id]);
            $email_order_items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
            
            sendOrderConfirmationEmail(
                $customer_email,
                $customer_name,
                $order_number,
                $order_id,
                $email_order_items,
                $subtotal,
                $tax,
                $shipping,
                $total
            );
        } catch (Exception $e) {
            // Log email error but don't fail the order
            error_log("Failed to send order confirmation email: " . $e->getMessage());
        }

        // Return success response
        echo json_encode([
            'success' => true,
            'message' => 'Order placed successfully',
            'order_id' => $order_id,
            'order_number' => $order_number,
            'total' => $total,
            'payment_method' => $payment_method,
            'status' => $order_status
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Order creation error: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error creating order: ' . $e->getMessage(),
            'debug' => defined('APP_DEBUG') && APP_DEBUG ? $e->getTraceAsString() : null
        ]);
    }

} catch (Exception $e) {
    error_log("Process order error: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'debug' => defined('APP_DEBUG') && APP_DEBUG ? $e->getTraceAsString() : null
    ]);
}
?>
