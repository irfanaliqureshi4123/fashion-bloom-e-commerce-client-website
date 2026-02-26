<?php
session_start();
include "includes/db.php"; // PDO $pdo already exists
include "includes/email.php"; // Email functions

/* ---------- 1.  POST-handling (server-side) ---------- */
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['action']) &&
    $_POST['action'] === 'register'
) {
    // sanitise
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name']  ?? '');
    $email      = trim($_POST['email']      ?? '');
    $phone      = trim($_POST['phone']      ?? '');
    $password   = $_POST['password']        ?? '';

    /* basic server-side checks */
    if ($first_name === '' || $last_name === '' || $email === '' || $password === '' || $phone === '') {
        $_SESSION['register_error'] = 'All fields are required!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['register_error'] = 'Invalid email address!';
    } elseif (strlen($password) < 6) {
        $_SESSION['register_error'] = 'Password must be at least 6 characters!';
    } elseif (!preg_match('/^[0-9]{10,15}$/', $phone)) {
        $_SESSION['register_error'] = 'Phone number must be 10-15 digits!';
    } else {
        /* duplicate e-mail ? */
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount()) {
            $_SESSION['register_error'] = 'Email already registered!';
        } else {
            /* insert */
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $otp = generateOTP();
            $token_expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));
            
            $stmt = $pdo->prepare(
                "INSERT INTO users (first_name, last_name, email, password, phone, role, verification_token, token_expires, email_verified)
                 VALUES (?, ?, ?, ?, ?, 'user', ?, ?, 0)"
            );
            if ($stmt->execute([$first_name, $last_name, $email, $hash, $phone, $otp, $token_expires])) {
                // Send verification email with OTP
                $email_sent = sendVerificationEmail($email, $first_name, $otp);
                
                if ($email_sent) {
                    $_SESSION['register_success'] = '✅ Registration successful! We\'ve sent a verification code to your email.';
                    $_SESSION['pending_verification_email'] = $email;
                    header('Location: verify-email.php');
                    exit;
                } else {
                    $_SESSION['register_error'] = 'Registration complete, but email could not be sent. Please contact support.';
                }
            } else {
                $_SESSION['register_error'] = 'Something went wrong. Please try again.';
                error_log("Failed to insert user: $email");
            }
        }
    }
    header('Location: register.php');
    exit;
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - Fashion Bloom</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/pages/register.css">
</head>
<body>
    <div class="register-wrapper">
        <h1>Create Account</h1>

        <!-- Messages -->
        <?php
        if (isset($_SESSION['register_error'])) {
            echo '<div class="auth-message error">
                ' . htmlspecialchars($_SESSION['register_error']) . '
            </div>';
            unset($_SESSION['register_error']);
        }
        if (isset($_SESSION['register_success'])) {
            echo '<div class="auth-message success">
                ' . htmlspecialchars($_SESSION['register_success']) . '
            </div>';
            unset($_SESSION['register_success']);
        }
        ?>

        <!-- Register Form -->
        <form method="POST" id="registerForm" novalidate>
            <input type="hidden" name="action" value="register">
            
            <!-- Name Row -->
            <div class="form-row">
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input 
                        type="text" 
                        id="first_name" 
                        name="first_name" 
                        placeholder="Sartaj" 
                        autocomplete="given-name" 
                        required
                    >
                    <div class="field-error" id="first_name_error"></div>
                </div>

                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input 
                        type="text" 
                        id="last_name" 
                        name="last_name" 
                        placeholder="Aziz" 
                        autocomplete="family-name" 
                        required
                    >
                    <div class="field-error" id="last_name_error"></div>
                </div>
            </div>

            <!-- Email -->
            <div class="form-group">
                <label for="email">Email Address</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    placeholder="your@email.com" 
                    autocomplete="email" 
                    required
                >
                <div class="field-error" id="email_error"></div>
            </div>

            <!-- Phone -->
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input 
                    type="tel" 
                    id="phone" 
                    name="phone" 
                    placeholder="03001234567" 
                    autocomplete="tel" 
                    required
                >
                <div class="field-error" id="phone_error"></div>
            </div>

            <!-- Password -->
            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-wrapper">
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="At least 8 characters" 
                        autocomplete="new-password" 
                        required
                    >
                    <span class="toggle-password" id="togglePassword">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
                <div class="field-error" id="password_error"></div>
            </div>

            <!-- Terms -->
            <div class="terms-agreement">
                <input type="checkbox" id="terms" name="terms" required>
                <label for="terms">
                    I agree to the <a href="terms-conditions.php" target="_blank">Terms & Conditions</a> and <a href="privacy-policy.php" target="_blank">Privacy Policy</a>
                </label>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="register-btn">Create Account</button>
        </form>

        <!-- Login Link -->
        <div class="login-text">
            Already have an account? 
            <a href="login.php">Sign In</a>
        </div>
    </div>

    <script src="js/main.js"></script>
    <script src="js/register.js"></script>
</body>
</html>
