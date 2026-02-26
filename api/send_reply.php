<?php
/**
 * Send Reply to Customer Message
 * Allows admin to send email reply to customer messages
 */

// Start output buffering
ob_start();

// Set JSON header
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

try {
    // Verify admin session
    session_start();
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        ob_end_clean();
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    // Check if it's a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit;
    }

    // Get form data
    $message_id = intval($_POST['message_id'] ?? 0);
    $customer_email = trim($_POST['customer_email'] ?? '');
    $customer_name = trim($_POST['customer_name'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $reply_message = trim($_POST['reply_message'] ?? '');

    // Validation
    $errors = [];

    if (empty($message_id)) {
        $errors[] = 'Invalid message ID';
    }
    if (empty($customer_email) || !filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid customer email is required';
    }
    if (empty($subject)) {
        $errors[] = 'Subject is required';
    }
    if (empty($reply_message)) {
        $errors[] = 'Reply message is required';
    }

    // Return validation errors
    if (!empty($errors)) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
        exit;
    }

    // Get root directory
    $root_dir = dirname(__DIR__);

    // Include database connection
    require_once $root_dir . '/includes/db.php';

    // Verify message exists in database
    $stmt = $pdo->prepare("SELECT * FROM contact_messages WHERE id = ?");
    $stmt->execute([$message_id]);
    $message = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$message) {
        ob_end_clean();
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Message not found']);
        exit;
    }

    // Try to send reply email
    try {
        require_once $root_dir . '/includes/email.php';

        // Create HTML email body
        $email_body = "
        <h2>Re: " . htmlspecialchars($subject) . "</h2>
        <p>Hi " . htmlspecialchars($customer_name) . ",</p>
        <p>" . nl2br(htmlspecialchars($reply_message)) . "</p>
        <hr style='border: none; border-top: 1px solid #ddd; margin: 20px 0;'>
        <p style='color: #666; font-size: 0.9rem;'>
            <strong>Fashion Bloom Support Team</strong><br>
            Email: support@fashionbloom.com<br>
            Phone: +92 300 1234567
        </p>
        ";

        // Send reply email to customer
        @sendEmailViaSMTP($customer_email, "Re: " . $subject, $email_body);

        // Update message status to read
        $update_stmt = $pdo->prepare("UPDATE contact_messages SET status = ? WHERE id = ?");
        $update_stmt->execute(['read', $message_id]);

        // Clear output buffer and return success
        ob_end_clean();
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Reply sent successfully to customer!']);
        exit;

    } catch (Exception $e) {
        ob_end_clean();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to send reply email. Please try again.']);
        exit;
    }

} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again later.']);
    exit;
}
?>
