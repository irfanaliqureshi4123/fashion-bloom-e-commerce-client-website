<?php
session_start();
require "includes/db.php"; // PDO connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $_SESSION['login_error'] = "Both fields are required!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['login_error'] = "Enter a valid email address!";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, first_name, password, role, email_verified FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                // Check if email is verified
                if ($user['email_verified'] == 0) {
                    $_SESSION['login_error'] = "⚠️ Your email is not verified yet. Please check your inbox for the verification link.";
                    $_SESSION['unverified_email'] = $email; // For resend option
                } else {
                    $_SESSION['user_id']    = $user['id'];
                    $_SESSION['first_name'] = $user['first_name'];
                    $_SESSION['role']       = $user['role'];

                    $_SESSION['dashboard_success'] = "Welcome back, " . $user['first_name'] . "!";

                    if ($user['role'] === 'admin') {
                        header("Location: admin.php");
                    } else {
                        header("Location: dashboard.php");
                    }
                    exit;
                }
            } else {
                $_SESSION['login_error'] = "Invalid email or password!";
            }
        } catch (PDOException $e) {
            $_SESSION['login_error'] = "Database error: " . $e->getMessage();
        }
    }
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Fashion Bloom</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      background: linear-gradient(120deg, #ffffff 55%, #f8a1b6 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 16px;
      font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    }

    .auth-container {
      width: 100%;
      max-width: 420px;
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.98) 0%, rgba(255, 255, 255, 0.95) 100%);
      border-radius: 16px;
      box-shadow: 0 20px 60px rgba(233, 30, 99, 0.15), 0 0 1px rgba(0, 0, 0, 0.05);
      padding: 40px 32px;
      animation: slideUp 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
      border: 1px solid rgba(233, 30, 99, 0.08);
      backdrop-filter: blur(10px);
      margin-top: 80px;
    }

    @keyframes slideUp {
      from {
        opacity: 0;
        transform: translateY(50px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .auth-header {
      text-align: center;
      margin-bottom: 24px;
    }

    .auth-title {
      font-size: 2rem;
      margin-bottom: 8px;
      color: #e91e63;
      font-weight: 700;
      letter-spacing: -0.5px;
      line-height: 1.2;
    }

    .auth-subtitle {
      font-size: 0.9rem;
      color: #999;
      font-weight: 400;
      line-height: 1.4;
    }

    .auth-register-link {
      text-align: center;
      margin-bottom: 24px;
      padding: 12px 14px;
      background: linear-gradient(135deg, rgba(233, 30, 99, 0.08) 0%, rgba(194, 24, 91, 0.04) 100%);
      border-radius: 10px;
      border: 1px solid rgba(233, 30, 99, 0.12);
      font-size: 0.9rem;
      line-height: 1.4;
    }

    .auth-register-link a {
      color: #e91e63;
      font-weight: 600;
      text-decoration: none;
      transition: all 0.3s ease;
    }

    .auth-register-link a:hover {
      color: #c2185b;
      text-shadow: 0 0 8px rgba(233, 30, 99, 0.3);
    }

    .auth-form {
      display: flex;
      flex-direction: column;
      gap: 18px;
    }

    .form-group {
      display: flex;
      flex-direction: column;
      gap: 6px;
    }

    .auth-form label {
      display: block;
      color: #333;
      font-weight: 600;
      font-size: 0.9rem;
      letter-spacing: 0.2px;
    }

    .auth-form input {
      width: 100%;
      padding: 11px 14px;
      border: 2px solid #e8e8e8;
      border-radius: 9px;
      font-size: 0.95rem;
      background: #fafafa;
      transition: all 0.3s ease;
      font-family: 'Poppins', sans-serif;
      height: 44px;
    }

    .auth-form input::placeholder {
      color: #ccc;
    }

    .auth-form input:focus {
      border-color: #e91e63;
      background: #fff;
      outline: none;
      box-shadow: 0 0 0 4px rgba(233, 30, 99, 0.1);
    }

    .password-actions {
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 0.85rem;
    }

    .forgot-password {
      text-decoration: none;
      color: #e91e63;
      font-weight: 500;
      transition: all 0.3s ease;
    }

    .forgot-password:hover {
      color: #c2185b;
      text-decoration: underline;
    }

    .auth-form button {
      width: 100%;
      padding: 0;
      background: linear-gradient(135deg, #e91e63 0%, #c2185b 100%);
      color: #fff;
      border: none;
      border-radius: 9px;
      font-size: 1rem;
      font-weight: 700;
      cursor: pointer;
      box-shadow: 0 4px 16px rgba(233, 30, 99, 0.25);
      font-family: 'Poppins', sans-serif;
      height: 48px;
      margin-top: 8px;
      transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
      position: relative;
      overflow: hidden;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .auth-form button::before {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      width: 0;
      height: 0;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.2);
      transform: translate(-50%, -50%);
      transition: width 0.6s ease, height 0.6s ease;
    }

    .auth-form button:hover::before {
      width: 500px;
      height: 500px;
    }

    .auth-form button:hover {
      background: linear-gradient(135deg, #c2185b 0%, #e91e63 100%);
      transform: translateY(-2px);
      box-shadow: 0 8px 28px rgba(233, 30, 99, 0.35);
    }

    .auth-form button:active {
      transform: translateY(0);
    }

    .auth-message {
      text-align: center;
      margin-bottom: 16px;
      padding: 12px 14px;
      border-radius: 9px;
      font-size: 0.9rem;
      font-weight: 500;
      border: 1px solid transparent;
      animation: slideDown 0.4s ease;
      line-height: 1.4;
    }

    @keyframes slideDown {
      from {
        opacity: 0;
        transform: translateY(-20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .auth-message.error {
      color: #b71c1c;
      background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%);
      border-color: rgba(229, 57, 53, 0.2);
    }

    .auth-message.success {
      color: #1b5e20;
      background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
      border-color: rgba(76, 175, 80, 0.2);
    }

    .field-error {
      color: #d32f2f;
      font-size: 0.75rem;
      margin-top: -2px;
      margin-bottom: 0;
      min-height: 14px;
      font-weight: 500;
      animation: shake 0.3s ease;
    }

    @keyframes shake {
      0%, 100% { transform: translateX(0); }
      25% { transform: translateX(-5px); }
      75% { transform: translateX(5px); }
    }

    .auth-link {
      text-align: center;
      margin-top: 20px;
      font-size: 0.9rem;
      color: #666;
    }

    .auth-link a {
      color: #e91e63;
      font-weight: 600;
      text-decoration: none;
      transition: all 0.3s ease;
    }

    .auth-link a:hover {
      color: #c2185b;
      text-decoration: underline;
    }

    /* Desktop (1025px+) */
    @media (min-width: 1025px) {
      .auth-container {
        max-width: 440px;
        padding: 44px 36px;
      }
      .auth-title {
        font-size: 2.1rem;
        margin-bottom: 10px;
      }
      .auth-form {
        gap: 20px;
      }
      .auth-form input {
        height: 44px;
        padding: 12px 15px;
        font-size: 0.95rem;
        border-radius: 9px;
      }
      .auth-form button {
        height: 48px;
        font-size: 1rem;
        margin-top: 8px;
        border-radius: 9px;
      }
    }

    /* Tablet (768px to 1024px) */
    @media (min-width: 768px) and (max-width: 1024px) {
      .auth-container {
        max-width: 400px;
        padding: 38px 28px;
        margin-top: 70px;
      }
      .auth-title {
        font-size: 1.85rem;
        margin-bottom: 6px;
      }
      .auth-form {
        gap: 16px;
      }
      .auth-form input {
        height: 42px;
        padding: 10px 12px;
        font-size: 0.9rem;
      }
      .auth-form button {
        height: 46px;
        font-size: 0.95rem;
      }
    }

    /* Small Phone (480px to 767px) */
    @media (max-width: 767px) {
      .auth-container {
        max-width: 100%;
        padding: 32px 20px;
        border-radius: 14px;
        margin-top: 50px;
      }
      .auth-header {
        margin-bottom: 20px;
      }
      .auth-title {
        font-size: 1.65rem;
        margin-bottom: 6px;
      }
      .auth-subtitle {
        font-size: 0.85rem;
      }
      .auth-register-link {
        margin-bottom: 20px;
        padding: 10px 12px;
        font-size: 0.85rem;
      }
      .auth-form {
        gap: 16px;
      }
      .auth-form label {
        font-size: 0.85rem;
      }
      .auth-form input {
        height: 42px;
        padding: 10px 12px;
        font-size: 0.9rem;
      }
      .auth-form button {
        height: 46px;
        font-size: 0.9rem;
        margin-top: 6px;
      }
      .password-actions {
        font-size: 0.8rem;
      }
      .field-error {
        font-size: 0.7rem;
        min-height: 12px;
      }
      .auth-message {
        padding: 10px 12px;
        font-size: 0.85rem;
      }
      .auth-link {
        font-size: 0.85rem;
        margin-top: 16px;
      }
    }

    /* Extra Small Phone (480px and below) */
    @media (max-width: 479px) {
      body {
        padding: 12px;
      }
      .auth-container {
        padding: 24px 16px;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(233, 30, 99, 0.1);
        margin-top: 40px;
      }
      .auth-header {
        margin-bottom: 16px;
      }
      .auth-title {
        font-size: 1.5rem;
        margin-bottom: 4px;
      }
      .auth-subtitle {
        font-size: 0.8rem;
      }
      .auth-register-link {
        margin-bottom: 16px;
        padding: 9px 11px;
        font-size: 0.8rem;
        border-radius: 8px;
      }
      .auth-form {
        gap: 14px;
      }
      .form-group {
        gap: 4px;
      }
      .auth-form label {
        font-size: 0.8rem;
        font-weight: 600;
      }
      .auth-form input {
        height: 40px;
        padding: 9px 11px;
        font-size: 0.85rem;
        border-radius: 8px;
      }
      .auth-form input::placeholder {
        font-size: 0.8rem;
      }
      .auth-form button {
        height: 44px;
        font-size: 0.85rem;
        border-radius: 8px;
        margin-top: 4px;
      }
      .password-actions {
        font-size: 0.75rem;
        gap: 6px;
      }
      .field-error {
        font-size: 0.65rem;
        min-height: 12px;
      }
      .auth-message {
        padding: 10px 11px;
        font-size: 0.8rem;
        border-radius: 8px;
        margin-bottom: 12px;
      }
      .auth-link {
        font-size: 0.8rem;
        margin-top: 14px;
      }
    }

    /* Landscape mode */
    @media (max-height: 600px) and (orientation: landscape) {
      body {
        padding: 10px;
        min-height: auto;
      }
      .auth-container {
        padding: 20px 24px;
        max-height: 95vh;
        overflow-y: auto;
        border-radius: 12px;
        margin-top: 30px;
      }
      .auth-header {
        margin-bottom: 12px;
      }
      .auth-title {
        font-size: 1.4rem;
        margin-bottom: 2px;
      }
      .auth-form {
        gap: 12px;
      }
      .auth-form input {
        height: 38px;
        padding: 8px 10px;
        font-size: 0.85rem;
      }
      .auth-form button {
        height: 40px;
        font-size: 0.9rem;
        margin-top: 6px;
      }
    }
  </style>
</head>
<body>
  <div class="auth-container">
    <?php if (isset($_SESSION['register_success'])): ?>
      <div class="auth-message success"><?= htmlspecialchars($_SESSION['register_success']); ?></div>
      <?php unset($_SESSION['register_success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['logout_success'])): ?>
      <div class="auth-message success"><?= htmlspecialchars($_SESSION['logout_success']); ?></div>
      <?php unset($_SESSION['logout_success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['verify_success'])): ?>
      <div class="auth-message success"><?= htmlspecialchars($_SESSION['verify_success']); ?></div>
      <?php unset($_SESSION['verify_success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['login_error'])): ?>
      <div class="auth-message error">
        <?= htmlspecialchars($_SESSION['login_error']); ?>
        <?php if (isset($_SESSION['unverified_email'])): ?>
          <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid rgba(255,255,255,0.2);">
            <a href="resend-verification.php" style="color: inherit; text-decoration: underline; font-weight: 600;">Resend verification email →</a>
          </div>
          <?php unset($_SESSION['unverified_email']); ?>
        <?php endif; ?>
      </div>
      <?php unset($_SESSION['login_error']); ?>
    <?php endif; ?>

    <div class="auth-title">Login to Fashion Bloom</div>

    <form class="auth-form" id="loginForm" method="POST" action="" novalidate>
      <label for="email">Email Address</label>
      <input type="email" id="email" name="email" autocomplete="email" required>
      <div class="field-error" id="email_error"></div>

      <label for="password">Password</label>
      <input type="password" id="password" name="password" autocomplete="current-password" required>
      <div class="field-error" id="password_error"></div>

      <button type="submit">Login</button>
    </form>

    <div class="auth-link">
      Don’t have an account? <a href="register.php">Register</a>
    </div>
  </div>

<script>
const form = document.getElementById('loginForm');

function showError(id, msg) {
    document.getElementById(id + '_error').textContent = msg;
}
function clearError(id) {
    document.getElementById(id + '_error').textContent = '';
}

form.addEventListener('submit', function (e) {
    let ok = true;

    const email = form.email.value.trim();
    const password = form.password.value.trim();

    if (!email) {
        showError('email', 'Email is required');
        ok = false;
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        showError('email', 'Enter a valid email');
        ok = false;
    } else clearError('email');

    if (!password) {
        showError('password', 'Password is required');
        ok = false;
    } else clearError('password');

    if (!ok) e.preventDefault();
});
</script>
</body>
</html>