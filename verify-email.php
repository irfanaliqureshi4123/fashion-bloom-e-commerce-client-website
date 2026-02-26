<?php
session_start();
include "includes/db.php";
include "includes/email.php";

// Handle OTP verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'verify_otp') {
    $otp = trim($_POST['otp'] ?? '');
    $email = trim($_POST['email'] ?? '');
    
    if (empty($otp) || empty($email)) {
        $_SESSION['verify_error'] = 'Please enter both email and verification code.';
    } elseif (strlen($otp) !== 6 || !ctype_digit($otp)) {
        $_SESSION['verify_error'] = 'Verification code must be 6 digits.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, first_name, email_verified, verification_token, token_expires FROM users WHERE email = ? AND verification_token = ?");
            $stmt->execute([$email, $otp]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                $_SESSION['verify_error'] = '❌ Invalid verification code or email address.';
            } elseif ($user['email_verified'] == 1) {
                $_SESSION['verify_error'] = '✅ Email already verified! You can now login.';
                $_SESSION['verify_success_redirect'] = true;
            } elseif (strtotime($user['token_expires']) < time()) {
                $_SESSION['verify_error'] = '⏰ Verification code has expired. Please request a new one.';
            } else {
                // Mark as verified
                $update_stmt = $pdo->prepare("UPDATE users SET email_verified = 1, verification_token = NULL, token_expires = NULL WHERE id = ?");
                if ($update_stmt->execute([$user['id']])) {
                    // Send welcome email
                    sendWelcomeEmail($email, $user['first_name']);
                    $_SESSION['verify_success'] = '✅ Email verified successfully! You can now login.';
                    header('Location: login.php');
                    exit;
                } else {
                    $_SESSION['verify_error'] = 'Error updating account. Please try again.';
                }
            }
        } catch (PDOException $e) {
            $_SESSION['verify_error'] = 'Database error: ' . $e->getMessage();
        }
    }
    
    header('Location: verify-email.php');
    exit;
}

// Check if successful
$success = isset($_GET['success']) && $_GET['success'] == 1;
$pending_email = $_SESSION['pending_verification_email'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email - Fashion Bloom</title>
    <link rel="stylesheet" href="css/pages/verify-email.css">
</head>
<body>
    <div class="container">
        <?php if ($success): ?>
            <!-- Success State -->
            <div class="success-box">
                <div class="success-icon">✅</div>
                <div class="success-title">Email Verified!</div>
                <div class="success-message">
                    <p>Congratulations! Your email has been verified successfully.</p>
                    <p>Your Fashion Bloom account is now ready to use.</p>
                </div>
                
                <a href="login.php" class="btn btn-primary">Login to Your Account</a>
                <a href="index.php" class="btn btn-secondary">Back to Home</a>
            </div>
        <?php else: ?>
            <!-- Verification Form -->
            <div class="logo-section">
                <h1>📧 Verify Your Email</h1>
                <p>Enter the verification code we sent to your email</p>
            </div>

            <?php if (isset($_SESSION['verify_error'])): ?>
                <div class="message error">
                    <?php echo htmlspecialchars($_SESSION['verify_error']); unset($_SESSION['verify_error']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['register_success'])): ?>
                <div class="message success">
                    <?php echo htmlspecialchars($_SESSION['register_success']); unset($_SESSION['register_success']); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="hidden" name="action" value="verify_otp">
                
                <div class="form-group">
                    <label for="email">📧 Email Address</label>
                    <input type="email" name="email" id="email" placeholder="your@email.com" value="<?php echo htmlspecialchars($pending_email); ?>" autocomplete="email" required>
                </div>

                <div class="form-group">
                    <label for="otp">🔐 Verification Code (6 digits)</label>
                    <input type="text" name="otp" id="otp" class="otp-input" placeholder="000000" maxlength="6" inputmode="numeric" pattern="[0-9]{6}" autocomplete="one-time-code" required>
                </div>

                <button type="submit" class="btn btn-primary">Verify Email</button>
            </form>

            <div class="info-box">
                <strong>💡 How it works:</strong>
                <ul style="margin-top: 8px; margin-left: 20px;">
                    <li>Check your email for the 6-digit code</li>
                    <li>Enter the code above</li>
                    <li>Code expires in 15 minutes</li>
                </ul>
            </div>

            <div class="divider">━━ Need help? ━━</div>

            <div class="back-link">
                <p style="font-size: 13px; color: #666; margin-bottom: 10px;">Didn't receive the code?</p>
                <a href="resend-verification.php">← Request a new code</a>
            </div>

            <div class="back-link" style="margin-top: 15px;">
                <a href="login.php">← Back to Login</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
