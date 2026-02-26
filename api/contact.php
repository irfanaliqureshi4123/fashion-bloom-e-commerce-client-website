<?php
/**
 * Contact Form Handler
 * Processes contact form submissions from the contact page
 */

// Enable error logging
ini_set('log_errors', 1);
ini_set('error_log', dirname(__DIR__) . '/error.log');

// Start output buffering
ob_start();

// Set JSON header FIRST
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

try {
    // Check if it's a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit;
    }

    // Get form data
    $first_name = trim($_POST['firstName'] ?? '');
    $last_name = trim($_POST['lastName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $order_number = trim($_POST['orderNumber'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Validation
    $errors = [];

    if (empty($first_name)) {
        $errors[] = 'First name is required';
    }
    if (empty($last_name)) {
        $errors[] = 'Last name is required';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email address is required';
    }
    if (empty($subject)) {
        $errors[] = 'Subject is required';
    }
    if (empty($message)) {
        $errors[] = 'Message is required';
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
    if (!file_exists($root_dir . '/includes/db.php')) {
        throw new Exception('Database file not found at: ' . $root_dir . '/includes/db.php');
    }
    
    require_once $root_dir . '/includes/db.php';
    
    // Check if PDO exists
    if (!isset($pdo)) {
        throw new Exception('Database connection failed - PDO not initialized');
    }

    // Combine first and last name
    $full_name = $first_name . ' ' . $last_name;

    // Prepare and execute INSERT statement (matching actual table schema)
    $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, subject, message, status, created_at) VALUES (?, ?, ?, ?, 'unread', NOW())");
    
    if ($stmt->execute([$full_name, $email, $subject, $message])) {
        // Try to send emails
        try {
            require_once $root_dir . '/includes/email.php';
            
            // Send confirmation email to user
            $email_subject = "We Received Your Message - Fashion Bloom";
            $email_body = "<h2>Hi " . htmlspecialchars($first_name) . ",</h2>
            <p>Thank you for contacting Fashion Bloom! We have received your message and will get back to you within 24 hours.</p>
            <p><strong>Subject:</strong> " . htmlspecialchars($subject) . "</p>";
            
            @sendEmailViaSMTP($email, $email_subject, $email_body);
            
            // Send notification email to admin
            $admin_subject = "New Contact Form Message - " . $subject;
            $admin_body = "<h3>New Contact Form Message</h3>
            <p><strong>From:</strong> " . htmlspecialchars($first_name . ' ' . $last_name) . " (" . htmlspecialchars($email) . ")</p>
            <p><strong>Message:</strong></p>
            <p>" . nl2br(htmlspecialchars($message)) . "</p>";
            
            @sendEmailViaSMTP('support@fashionbloom.com', $admin_subject, $admin_body);
        } catch (Exception $e) {
            // Silently fail - message was saved
        }

        // Clear output buffer and return success
        ob_end_clean();
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Message sent successfully! We will get back to you within 24 hours.']);
        exit;
    } else {
        ob_end_clean();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to save message. Please try again.']);
        exit;
    }

} catch (PDOException $e) {
    ob_end_clean();
    error_log('PDOException in contact.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error. Please try again later.']);
    exit;
} catch (Exception $e) {
    ob_end_clean();
    error_log('Exception in contact.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again later.']);
    exit;
}
?>
?>