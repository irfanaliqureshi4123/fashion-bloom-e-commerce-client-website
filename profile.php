<?php
session_start();

// Protect profile page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include "includes/db.php";

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';
$profile_photo = '';

// Handle profile photo upload (for admin only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_photo') {
    if ($_SESSION['role'] === 'admin' && isset($_FILES['profile_photo'])) {
        $file = $_FILES['profile_photo'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5MB

        // Validate file
        if ($file['error'] === 0) {
            if ($file['size'] > $max_size) {
                $error = 'File size must be less than 5MB!';
            } elseif (!in_array($file['type'], $allowed_types)) {
                $error = 'Only JPG, PNG, GIF, and WebP files are allowed!';
            } else {
                // Create uploads/admin directory if it doesn't exist
                $upload_dir = 'uploads/admin/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                // Generate unique filename
                $filename = 'admin_profile_' . $user_id . '_' . time() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
                $filepath = $upload_dir . $filename;

                // Move uploaded file
                if (move_uploaded_file($file['tmp_name'], $filepath)) {
                    try {
                        $stmt = $pdo->prepare("UPDATE users SET profile_photo = ? WHERE id = ?");
                        if ($stmt->execute([$filename, $user_id])) {
                            $message = '✓ Profile photo uploaded successfully!';
                            $profile_photo = $filename;
                        } else {
                            unlink($filepath);
                            $error = 'Failed to save photo. Please try again.';
                        }
                    } catch (PDOException $e) {
                        unlink($filepath);
                        $error = 'Database error: ' . $e->getMessage();
                    }
                } else {
                    $error = 'Failed to upload file. Please try again.';
                }
            }
        } elseif ($file['error'] !== 4) {
            $error = 'Error uploading file. Error code: ' . $file['error'];
        }
    } elseif ($_SESSION['role'] !== 'admin') {
        $error = 'Only admins can upload profile photos!';
    }
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    // Validation
    if ($first_name === '' || $last_name === '' || $phone === '') {
        $error = 'All fields are required!';
    } elseif (!preg_match('/^[0-9]{10,15}$/', $phone)) {
        $error = 'Phone number must be 10-15 digits!';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, phone = ? WHERE id = ?");
            if ($stmt->execute([$first_name, $last_name, $phone, $user_id])) {
                $_SESSION['first_name'] = $first_name;
                $message = '✓ Profile updated successfully!';
            } else {
                $error = 'Failed to update profile. Please try again.';
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// Fetch user data
try {
    $stmt = $pdo->prepare("SELECT first_name, last_name, email, phone, profile_photo FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $profile_photo = $user['profile_photo'] ?: '';
    }
} catch (PDOException $e) {
    $error = 'Failed to load profile: ' . $e->getMessage();
    $user = null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Fashion Bloom</title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/pages/profile.css">
    <script src="/js/cart-utils.js"></script>
</head>
<body>
    <?php include "includes/header.php"; ?>

    <div class="profile-container">
        <div class="profile-header">
            <?php if ($profile_photo && $_SESSION['role'] === 'admin'): ?>
                <img src="/uploads/admin/<?= htmlspecialchars($profile_photo); ?>" alt="Profile" class="profile-photo">
            <?php else: ?>
                <i class="fas fa-user-circle profile-icon"></i>
            <?php endif; ?>
            <div>
                <h1 class="profile-title">My Profile</h1>
                <p class="profile-subtitle">Manage your account information</p>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="message success">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="message error">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($_SESSION['role'] === 'admin'): ?>
            <!-- Admin Profile Photo Section -->
            <div class="photo-upload-section">
                <h3><i class="fas fa-camera"></i> Profile Photo</h3>
                <form method="POST" enctype="multipart/form-data" class="photo-upload-form">
                    <input type="hidden" name="action" value="upload_photo">
                    <div class="file-input-wrapper">
                        <input type="file" id="profile_photo" name="profile_photo" accept="image/*" onchange="previewPhoto(event)">
                        <label for="profile_photo" class="file-label">
                            <i class="fas fa-upload"></i> Choose Photo
                        </label>
                        <p class="file-info">JPG, PNG, GIF, or WebP (Max 5MB)</p>
                    </div>
                    <div id="photo-preview" class="photo-preview"></div>
                    <button type="submit" class="btn-primary" style="width: 100%; margin-top: 10px;">
                        <i class="fas fa-save"></i> Upload Photo
                    </button>
                </form>
            </div>
        <?php endif; ?>

        <?php if ($user): ?>
            <div class="info-box">
                <strong>Email:</strong> <?= htmlspecialchars($user['email']); ?>
                <p style="margin: 5px 0 0 0; font-size: 0.9rem; color: #999;">Your email cannot be changed</p>
            </div>

            <form method="POST" novalidate>
                <input type="hidden" name="action" value="update">

                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($user['first_name']); ?>" autocomplete="given-name" required>
                </div>

                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($user['last_name']); ?>" autocomplete="family-name" required>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($user['phone']); ?>" placeholder="10-15 digits" autocomplete="tel" required>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn-primary" style="width: 100%;">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </form>
        <?php else: ?>
            <div class="message error">
                <i class="fas fa-exclamation-circle"></i>
                Unable to load profile. Please try again later.
            </div>
        <?php endif; ?>
    </div>

    <!-- Cart Overlay -->
    <div class="cart-overlay" id="cart-overlay"></div>

    <!-- Cart Sidebar -->
    <div class="cart-sidebar" id="cart-sidebar">
        <div class="cart-header">
            <h3>Shopping Cart</h3>
            <button class="close-cart" id="close-cart">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="cart-items" id="cart-items">
            <div class="cart-empty">
                <i class="fas fa-shopping-cart"></i>
                <p>Your cart is empty</p>
                <p style="font-size: 0.85rem; color: var(--text-light);">Add items to get started</p>
            </div>
        </div>
        <div class="cart-footer">
            <div class="cart-total">
                <span class="cart-total-label">Total:</span>
                <span class="cart-total-amount">PKR <span id="cart-total">0</span></span>
            </div>
            <button class="checkout-btn" onclick="checkout()">Checkout</button>
        </div>
    </div>

    <script src="/js/main.js"></script>
    <script>
    function previewPhoto(event) {
        const file = event.target.files[0];
        const preview = document.getElementById('photo-preview');
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = `<img src="${e.target.result}" alt="Preview" style="max-width: 200px; max-height: 200px; border-radius: 8px; margin-top: 10px;">`;
            };
            reader.readAsDataURL(file);
        } else {
            preview.innerHTML = '';
        }
    }
    </script>
</body>
</html>
