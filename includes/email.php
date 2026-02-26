<?php

/**
 * Email Verification System for Fashion Bloom
 * Sends OTP (One-Time Password) verification emails to new users
 * Uses SMTP for reliable email delivery
 */

/**
 * Helper function to get environment variable from .env file
 */
function getEnvVar($key, $default = '') {
    $envFile = __DIR__ . '/../.env';
    if (!file_exists($envFile)) {
        return $default;
    }
    
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') === false || strpos($line, '#') === 0) {
            continue;
        }
        list($name, $value) = explode('=', $line, 2);
        if (trim($name) === $key) {
            return trim($value);
        }
    }
    return $default;
}

/**
 * Send email via SMTP (Gmail)
 */
function sendEmailViaSMTP($to, $subject, $message, $headers = '') {
    $smtp_host = getEnvVar('SMTP_HOST', 'smtp.gmail.com');
    $smtp_port = getEnvVar('SMTP_PORT', '587');
    $smtp_username = getEnvVar('SMTP_USERNAME', '');
    $smtp_password = getEnvVar('SMTP_PASSWORD', '');
    $smtp_from = getEnvVar('SMTP_FROM_EMAIL', 'noreply@fashionbloom.com');
    
    if (!$smtp_username || !$smtp_password) {
        return false;
    }
    
    try {
        // Connect to SMTP server
        $connection = @fsockopen($smtp_host, $smtp_port, $errno, $errstr, 10);
        if (!$connection) {
            return false;
        }
        
        // Read server response
        $response = fgets($connection, 515);
        if (strpos($response, '220') === false) {
            fclose($connection);
            return false;
        }
        
        // Send EHLO
        fputs($connection, "EHLO localhost\r\n");
        $response = fgets($connection, 515);
        while (substr($response, 3, 1) !== ' ' && !feof($connection)) {
            $response = fgets($connection, 515);
        }
        
        // Start TLS
        fputs($connection, "STARTTLS\r\n");
        $response = fgets($connection, 515);
        
        if (strpos($response, '220') === false) {
            fclose($connection);
            return false;
        }
        
        // Enable encryption
        if (!stream_socket_enable_crypto($connection, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            fclose($connection);
            return false;
        }
        
        // Send EHLO again after TLS
        fputs($connection, "EHLO localhost\r\n");
        $response = fgets($connection, 515);
        while (substr($response, 3, 1) !== ' ' && !feof($connection)) {
            $response = fgets($connection, 515);
        }
        
        // Authenticate
        fputs($connection, "AUTH LOGIN\r\n");
        fgets($connection, 515);
        
        fputs($connection, base64_encode($smtp_username) . "\r\n");
        fgets($connection, 515);
        
        fputs($connection, base64_encode($smtp_password) . "\r\n");
        $auth_response = fgets($connection, 515);
        
        if (strpos($auth_response, '235') === false) {
            fclose($connection);
            return false;
        }
        
        // Send mail
        fputs($connection, "MAIL FROM: <$smtp_from>\r\n");
        $response = fgets($connection, 515);
        if (strpos($response, '250') === false) {
            fclose($connection);
            return false;
        }
        
        fputs($connection, "RCPT TO: <$to>\r\n");
        $response = fgets($connection, 515);
        if (strpos($response, '250') === false) {
            fclose($connection);
            return false;
        }
        
        fputs($connection, "DATA\r\n");
        $response = fgets($connection, 515);
        if (strpos($response, '354') === false) {
            fclose($connection);
            return false;
        }
        
        // Build email
        $email_lines = [];
        $email_lines[] = "From: " . getEnvVar('SMTP_FROM_NAME', 'Fashion Bloom') . " <$smtp_from>";
        $email_lines[] = "To: $to";
        $email_lines[] = "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=";
        $email_lines[] = "MIME-Version: 1.0";
        $email_lines[] = "Content-type: text/html; charset=UTF-8";
        $email_lines[] = "Reply-To: support@fashionbloom.com";
        $email_lines[] = "";
        $email_lines[] = $message;
        
        $email = implode("\r\n", $email_lines);
        fputs($connection, $email . "\r\n.\r\n");
        $response = fgets($connection, 515);
        
        if (strpos($response, '250') === false) {
            fclose($connection);
            return false;
        }
        
        // Close connection
        fputs($connection, "QUIT\r\n");
        fclose($connection);
        
        return true;
        
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Generate a random 6-digit OTP
 */
function generateOTP() {
    return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

/**
 * Send verification OTP email to user
 */
function sendVerificationEmail($email, $first_name, $otp) {
    $subject = "Your Fashion Bloom Verification Code: " . $otp;
    
    $message = "
    <html>
    <head>
        <style>
            body { font-family: 'Segoe UI', Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 0; }
            .container { max-width: 600px; margin: 20px auto; background: #fff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); overflow: hidden; }
            .header { background: linear-gradient(135deg, #e91e63 0%, #c2185b 100%); padding: 40px 30px; text-align: center; color: #fff; }
            .header h1 { margin: 0 0 5px 0; font-size: 28px; font-weight: 700; }
            .header p { margin: 0; font-size: 14px; opacity: 0.9; }
            .content { padding: 40px 30px; }
            .greeting { font-size: 16px; color: #333; margin-bottom: 20px; line-height: 1.6; }
            .otp-box { 
                background: linear-gradient(135deg, #f8f8f8 0%, rgba(233, 30, 99, 0.05) 100%);
                border: 2px solid #e91e63;
                border-radius: 8px;
                padding: 25px;
                text-align: center;
                margin: 30px 0;
            }
            .otp-label { font-size: 12px; color: #666; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; }
            .otp-code {
                font-size: 48px;
                font-weight: 700;
                color: #e91e63;
                letter-spacing: 8px;
                font-family: 'Courier New', monospace;
                margin: 0;
            }
            .instructions { font-size: 14px; color: #666; line-height: 1.6; margin: 25px 0; }
            .highlight { background: #fff3e0; border-left: 4px solid #ff9800; padding: 12px 15px; margin: 15px 0; border-radius: 4px; font-size: 13px; color: #e65100; }
            .footer { background: #f9f9f9; padding: 20px 30px; text-align: center; color: #999; font-size: 12px; border-top: 1px solid #eee; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🔐 Verify Your Account</h1>
                <p>Welcome to Fashion Bloom!</p>
            </div>
            <div class='content'>
                <div class='greeting'>Hi <strong>" . htmlspecialchars($first_name) . "</strong>,</div>
                <p>Thank you for signing up! To complete your registration, please verify your email address by entering the code below:</p>
                
                <div class='otp-box'>
                    <div class='otp-label'>Your Verification Code</div>
                    <div class='otp-code'>" . htmlspecialchars($otp) . "</div>
                </div>
                
                <div class='instructions'>
                    <strong>📋 Next Steps:</strong>
                    <ol>
                        <li>Visit our verification page</li>
                        <li>Enter your email address and the code above</li>
                        <li>Click 'Verify' to activate your account</li>
                    </ol>
                </div>
                
                <div class='highlight'>
                    ⏱️ <strong>Code expires in 15 minutes</strong> from the time this email was sent. If you don't complete verification in time, you can request a new code.
                </div>
                
                <div class='instructions'>
                    <strong>💡 Didn't sign up?</strong><br>
                    If you didn't create this account, you can safely ignore this email.
                </div>
            </div>
            <div class='footer'>
                <p style='margin: 0;'>© 2024 Fashion Bloom. All rights reserved.</p>
                <p style='margin: 5px 0 0 0; font-size: 11px;'>This is an automated email, please do not reply.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return sendEmailViaSMTP($email, $subject, $message);
}

/**
 * Send welcome email after successful verification
 */
function sendWelcomeEmail($email, $first_name) {
    $subject = "Welcome to Fashion Bloom! 🎉";
    
    $message = "
    <html>
    <head>
        <style>
            body { font-family: 'Segoe UI', Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 0; }
            .container { max-width: 600px; margin: 20px auto; background: #fff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); overflow: hidden; }
            .header { background: linear-gradient(135deg, #e91e63 0%, #c2185b 100%); padding: 40px 30px; text-align: center; color: #fff; }
            .header h1 { margin: 0 0 5px 0; font-size: 28px; font-weight: 700; }
            .content { padding: 40px 30px; }
            .message { font-size: 15px; color: #333; line-height: 1.8; margin-bottom: 25px; }
            .feature-box {
                background: linear-gradient(135deg, rgba(233, 30, 99, 0.08) 0%, rgba(194, 24, 91, 0.04) 100%);
                border-left: 4px solid #e91e63;
                padding: 15px;
                margin: 10px 0;
                border-radius: 4px;
            }
            .feature-box strong { color: #e91e63; }
            .cta-button {
                display: inline-block;
                background: linear-gradient(135deg, #e91e63 0%, #c2185b 100%);
                color: #fff;
                padding: 14px 32px;
                border-radius: 8px;
                text-decoration: none;
                font-weight: 600;
                margin-top: 20px;
            }
            .footer { background: #f9f9f9; padding: 20px 30px; text-align: center; font-size: 12px; color: #999; border-top: 1px solid #eee; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>✅ Email Verified!</h1>
                <p>Your account is ready to use</p>
            </div>
            <div class='content'>
                <div class='message'>
                    <p>Congratulations, <strong>" . htmlspecialchars($first_name) . "</strong>!</p>
                    <p>Your email has been verified successfully. Your Fashion Bloom account is now fully activated and ready to use.</p>
                </div>
                
                <div style='margin: 25px 0;'>
                    <p><strong>Here's what you can do now:</strong></p>
                    <div class='feature-box'>
                        🛍️ <strong>Browse our collection</strong> - Explore our latest fashion items and accessories
                    </div>
                    <div class='feature-box'>
                        ❤️ <strong>Create wishlists</strong> - Save your favorite items for later
                    </div>
                    <div class='feature-box'>
                        💳 <strong>Make purchases</strong> - Checkout securely with multiple payment options
                    </div>
                    <div class='feature-box'>
                        👤 <strong>Manage your profile</strong> - Update your information and preferences
                    </div>
                </div>
                
                <center>
                    <a href='http://" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "/dashboard.php' class='cta-button'>Go to Your Dashboard</a>
                </center>
            </div>
            <div class='footer'>
                <p style='margin: 0;'>© 2024 Fashion Bloom. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return sendEmailViaSMTP($email, $subject, $message);
}

/**
 * Send Order Confirmation Email
 */
function sendOrderConfirmationEmail($email, $first_name, $order_number, $order_id, $order_items, $subtotal, $tax, $shipping, $total) {
    $subject = "Order Confirmation - " . $order_number;
    
    $items_html = "";
    foreach ($order_items as $item) {
        $item_total = $item['product_price'] * $item['quantity'];
        $items_html .= "
            <tr>
                <td style='padding: 10px; border-bottom: 1px solid #eee;'>{$item['product_name']}</td>
                <td style='padding: 10px; border-bottom: 1px solid #eee; text-align: center;'>{$item['quantity']}</td>
                <td style='padding: 10px; border-bottom: 1px solid #eee; text-align: right;'>PKR " . number_format($item['product_price']) . "</td>
                <td style='padding: 10px; border-bottom: 1px solid #eee; text-align: right;'>PKR " . number_format($item_total) . "</td>
            </tr>
        ";
    }
    
    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Order Confirmation</title>
    </head>
    <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto;'>
        <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center;'>
            <h1 style='color: white; margin: 0; font-size: 28px;'>Fashion Bloom</h1>
            <p style='color: white; margin: 10px 0 0 0; font-size: 16px;'>Your Order Has Been Confirmed!</p>
        </div>
        
        <div style='padding: 30px; background: white;'>
            <h2 style='color: #333; margin-bottom: 20px;'>Hi {$first_name},</h2>
            <p>Thank you for your order! We're excited to let you know that your order has been successfully placed and confirmed.</p>
            
            <div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                <h3 style='margin: 0 0 15px 0; color: #333;'>Order Details</h3>
                <p style='margin: 5px 0;'><strong>Order Number:</strong> {$order_number}</p>
                <p style='margin: 5px 0;'><strong>Order Date:</strong> " . date('F d, Y') . "</p>
            </div>
            
            <h3 style='color: #333; margin: 30px 0 15px 0;'>Order Items</h3>
            <table style='width: 100%; border-collapse: collapse; border: 1px solid #ddd;'>
                <thead>
                    <tr style='background: #f8f9fa;'>
                        <th style='padding: 12px; text-align: left; border-bottom: 2px solid #ddd;'>Product</th>
                        <th style='padding: 12px; text-align: center; border-bottom: 2px solid #ddd;'>Qty</th>
                        <th style='padding: 12px; text-align: right; border-bottom: 2px solid #ddd;'>Price</th>
                        <th style='padding: 12px; text-align: right; border-bottom: 2px solid #ddd;'>Total</th>
                    </tr>
                </thead>
                <tbody>
                    {$items_html}
                </tbody>
            </table>
            
            <div style='margin-top: 20px; padding: 20px; background: #f8f9fa; border-radius: 8px;'>
                <div style='display: flex; justify-content: space-between; margin-bottom: 10px;'>
                    <span>Subtotal:</span>
                    <span>PKR " . number_format($subtotal) . "</span>
                </div>
                <div style='display: flex; justify-content: space-between; margin-bottom: 10px;'>
                    <span>Shipping:</span>
                    <span>PKR " . number_format($shipping) . "</span>
                </div>
                <div style='display: flex; justify-content: space-between; margin-bottom: 10px;'>
                    <span>Tax (17%):</span>
                    <span>PKR " . number_format($tax) . "</span>
                </div>
                <div style='display: flex; justify-content: space-between; font-weight: bold; font-size: 18px; border-top: 2px solid #ddd; padding-top: 10px;'>
                    <span>Total:</span>
                    <span>PKR " . number_format($total) . "</span>
                </div>
            </div>
            
            <div style='margin-top: 30px; padding: 20px; background: #e8f5e8; border-radius: 8px; border-left: 4px solid #4caf50;'>
                <h4 style='margin: 0 0 10px 0; color: #2e7d32;'>What's Next?</h4>
                <ul style='margin: 0; padding-left: 20px;'>
                    <li>You will receive a shipping confirmation email once your order ships</li>
                    <li>Track your order status in your <a href='http://" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "/dashboard.php' style='color: #1976d2;'>dashboard</a></li>
                    <li>For any questions, contact our support team</li>
                </ul>
            </div>
            
            <div style='text-align: center; margin-top: 30px;'>
                <a href='http://" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "/dashboard.php' style='background: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;'>View Order Details</a>
            </div>
        </div>
        
        <div style='background: #f8f9fa; padding: 20px; text-align: center; border-top: 1px solid #ddd;'>
            <p style='margin: 0; color: #666;'>© 2024 Fashion Bloom. All rights reserved.</p>
            <p style='margin: 5px 0 0 0; color: #666;'>Questions? Contact us at support@fashionbloom.com</p>
        </div>
    </body>
    </html>
    ";
    
    return sendEmailViaSMTP($email, $subject, $message);
}

function sendOrderStatusUpdateEmail($email, $first_name, $order_number, $order_id, $new_status, $order_total) {
    $subject = "Order Status Update - " . $order_number;

    // Get status message
    $status_messages = [
        'pending' => ['title' => 'Order Received', 'message' => 'We have received your order and are processing it.', 'color' => '#ff9800'],
        'confirmed' => ['title' => 'Order Confirmed', 'message' => 'Your order has been confirmed and will be prepared for shipping soon.', 'color' => '#2196f3'],
        'processing' => ['title' => 'Order Processing', 'message' => 'Your order is currently being processed and prepared for shipment.', 'color' => '#ff9800'],
        'shipped' => ['title' => 'Order Shipped', 'message' => 'Great news! Your order has been shipped and is on its way to you.', 'color' => '#4caf50'],
        'delivered' => ['title' => 'Order Delivered', 'message' => 'Your order has been successfully delivered. Thank you for shopping with us!', 'color' => '#4caf50'],
        'cancelled' => ['title' => 'Order Cancelled', 'message' => 'Your order has been cancelled. Please contact us if you have any questions.', 'color' => '#f44336']
    ];

    $status_info = $status_messages[$new_status] ?? ['title' => 'Status Update', 'message' => 'Your order status has been updated.', 'color' => '#666'];

    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Order Status Update</title>
    </head>
    <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto;'>
        <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center;'>
            <h1 style='color: white; margin: 0; font-size: 28px;'>Fashion Bloom</h1>
            <p style='color: white; margin: 10px 0 0 0; font-size: 16px;'>Order Status Update</p>
        </div>

        <div style='padding: 30px; background: white;'>
            <h2 style='color: #333; margin-bottom: 20px;'>Hi {$first_name},</h2>

            <div style='background: {$status_info['color']}15; border: 2px solid {$status_info['color']}; border-radius: 8px; padding: 20px; margin: 20px 0; text-align: center;'>
                <h3 style='margin: 0 0 10px 0; color: {$status_info['color']}; font-size: 24px;'>{$status_info['title']}</h3>
                <p style='margin: 0; font-size: 16px; color: #333;'>{$status_info['message']}</p>
            </div>

            <div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                <h3 style='margin: 0 0 15px 0; color: #333;'>Order Information</h3>
                <p style='margin: 5px 0;'><strong>Order Number:</strong> {$order_number}</p>
                <p style='margin: 5px 0;'><strong>Order Total:</strong> PKR " . number_format($order_total, 2) . "</p>
                <p style='margin: 5px 0;'><strong>Status:</strong> <span style='color: {$status_info['color']}; font-weight: bold;'>" . ucfirst($new_status) . "</span></p>
                <p style='margin: 5px 0;'><strong>Updated Date:</strong> " . date('F d, Y \a\t g:i A') . "</p>
            </div>";

    // Add specific messages based on status
    if ($new_status === 'shipped') {
        $message .= "
            <div style='margin-top: 30px; padding: 20px; background: #e8f5e8; border-radius: 8px; border-left: 4px solid #4caf50;'>
                <h4 style='margin: 0 0 10px 0; color: #2e7d32;'>Shipping Information</h4>
                <p style='margin: 0;'>Your order is now in transit. You will receive tracking information via SMS or email shortly.</p>
            </div>";
    } elseif ($new_status === 'delivered') {
        $message .= "
            <div style='margin-top: 30px; padding: 20px; background: #e8f5e8; border-radius: 8px; border-left: 4px solid #4caf50;'>
                <h4 style='margin: 0 0 10px 0; color: #2e7d32;'>Delivery Confirmation</h4>
                <p style='margin: 0;'>We hope you love your new items! If you have any questions about your order, please don't hesitate to contact us.</p>
            </div>";
    }

    $message .= "
            <div style='text-align: center; margin-top: 30px;'>
                <a href='http://" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "/dashboard.php' style='background: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;'>View Order Details</a>
            </div>

            <div style='margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 8px;'>
                <h4 style='margin: 0 0 10px 0; color: #333;'>Need Help?</h4>
                <p style='margin: 0;'>If you have any questions about your order or need assistance, please contact our support team:</p>
                <ul style='margin: 10px 0 0 0; padding-left: 20px;'>
                    <li>Email: support@fashionbloom.com</li>
                    <li>Phone: +92 300 123 4567</li>
                </ul>
            </div>
        </div>

        <div style='background: #f8f9fa; padding: 20px; text-align: center; border-top: 1px solid #ddd;'>
            <p style='margin: 0; color: #666;'>© 2024 Fashion Bloom. All rights reserved.</p>
            <p style='margin: 5px 0 0 0; color: #666;'>Thank you for choosing Fashion Bloom!</p>
        </div>
    </body>
    </html>
    ";

    return sendEmailViaSMTP($email, $subject, $message);
}

?>
