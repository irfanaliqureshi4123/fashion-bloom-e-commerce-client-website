<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include "includes/db.php";

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';
$profile_photo = '';

// Handle profile photo upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_photo') {
    if (isset($_FILES['profile_photo'])) {
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

// Fetch admin data
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
    <title>Admin Profile - Fashion Bloom</title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(120deg, #ffffff 55%, #f8a1b6 100%);
            font-family: 'Poppins', sans-serif;
            padding: 20px;
            min-height: 100vh;
        }

        .admin-profile-wrapper {
            max-width: 500px;
            margin: 20px auto;
            background: #fff;
            border-radius: 12px;
            padding: 30px 20px;
            box-shadow: 0 10px 40px rgba(233, 30, 99, 0.1);
        }

        .back-to-admin {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
            padding: 10px 16px;
            background: rgba(233, 30, 99, 0.1);
            border: 2px solid #e91e63;
            border-radius: 8px;
            color: #e91e63;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .back-to-admin:hover {
            background: #e91e63;
            color: white;
            transform: translateX(-5px);
        }

        .profile-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }

        .profile-photo-container {
            position: relative;
            display: inline-block;
            margin-bottom: 15px;
        }

        .profile-photo {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #e91e63;
            box-shadow: 0 4px 15px rgba(233, 30, 99, 0.2);
        }

        .profile-icon {
            font-size: 120px;
            color: #e91e63;
            margin-bottom: 15px;
        }

        .profile-title {
            font-size: 1.8rem;
            color: #e91e63;
            margin-bottom: 5px;
            font-weight: 700;
        }

        .profile-subtitle {
            font-size: 0.9rem;
            color: #999;
        }

        .message {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.9rem;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .message.success {
            background: #e8f5e9;
            color: #1b5e20;
            border: 1px solid #c8e6c9;
        }

        .message.error {
            background: #ffebee;
            color: #b71c1c;
            border: 1px solid #ffcdd2;
        }

        .photo-upload-section {
            background: linear-gradient(135deg, rgba(233, 30, 99, 0.05) 0%, rgba(233, 30, 99, 0.02) 100%);
            border: 2px dashed #e91e63;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
            text-align: center;
        }

        .photo-upload-section h3 {
            color: #e91e63;
            font-size: 1rem;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .file-input-wrapper input[type="file"] {
            display: none;
        }

        .file-label {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 20px;
            background: #e91e63;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            color: white;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .file-label:hover {
            background: #c2185b;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(233, 30, 99, 0.25);
        }

        .file-label:active {
            transform: translateY(0);
        }

        .file-info {
            font-size: 0.75rem;
            color: #999;
            margin-top: 8px;
        }

        .info-box {
            background: #f9f9f9;
            border-left: 4px solid #e91e63;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }

        .info-box strong {
            color: #333;
        }

        .info-box p {
            font-size: 0.85rem;
            color: #999;
            margin-top: 5px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            color: #333;
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 6px;
        }

        .form-group input {
            width: 100%;
            padding: 10px 12px;
            border: 2px solid #e8e8e8;
            border-radius: 8px;
            font-size: 0.9rem;
            background: #fafafa;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }

        .form-group input:focus {
            border-color: #e91e63;
            background: #fff;
            outline: none;
            box-shadow: 0 0 0 3px rgba(233, 30, 99, 0.1);
        }

        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 25px;
        }

        .btn-primary {
            flex: 1;
            padding: 12px 16px;
            background: linear-gradient(135deg, #e91e63 0%, #c2185b 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(233, 30, 99, 0.3);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        /* Responsive Design */
        @media (max-width: 640px) {
            body {
                padding: 15px;
            }

            .admin-profile-wrapper {
                padding: 20px 15px;
                margin: 15px auto;
            }

            .profile-title {
                font-size: 1.5rem;
            }

            .profile-photo {
                width: 100px;
                height: 100px;
            }

            .profile-icon {
                font-size: 100px;
            }

            .photo-upload-section {
                padding: 15px;
                margin-bottom: 20px;
            }

            .photo-upload-section h3 {
                font-size: 0.9rem;
            }

            .file-label {
                padding: 10px 16px;
                font-size: 0.85rem;
            }

            .form-group input {
                padding: 9px 11px;
                font-size: 0.85rem;
            }

            .button-group {
                flex-direction: column;
            }
        }

        @media (max-width: 480px) {
            .admin-profile-wrapper {
                padding: 15px;
                border-radius: 10px;
            }

            .back-to-admin {
                padding: 8px 12px;
                font-size: 0.8rem;
            }

            .profile-title {
                font-size: 1.3rem;
            }

            .profile-photo {
                width: 80px;
                height: 80px;
            }

            .profile-icon {
                font-size: 80px;
            }

            .photo-upload-section {
                padding: 12px;
            }

            .message {
                font-size: 0.85rem;
                padding: 10px 12px;
            }
        }
    </style>
</head>
<body>
    <!-- NO HEADER/NAVBAR - Admin Profile only -->
    
    <div class="admin-profile-wrapper">
        <a href="admin.php" class="back-to-admin">
            <i class="fas fa-arrow-left"></i> Back
        </a>

        <div class="profile-header">
            <?php if ($profile_photo): ?>
                <div class="profile-photo-container">
                    <img src="/uploads/admin/<?= htmlspecialchars($profile_photo); ?>" alt="Admin Profile" class="profile-photo">
                </div>
            <?php else: ?>
                <i class="fas fa-user-circle profile-icon"></i>
            <?php endif; ?>
            <h1 class="profile-title">Admin Profile</h1>
            <p class="profile-subtitle">Manage your profile</p>
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

        <!-- Simple Photo Upload -->
        <div class="photo-upload-section">
            <h3><i class="fas fa-camera"></i> Change Photo</h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="upload_photo">
                <div class="file-input-wrapper">
                    <input type="file" id="profile_photo" name="profile_photo" accept="image/*" required>
                    <label for="profile_photo" class="file-label">
                        <i class="fas fa-upload"></i> Choose Photo
                    </label>
                    <p class="file-info">JPG, PNG, GIF, WebP (Max 5MB)</p>
                </div>
                <button type="submit" class="btn-primary" style="width: 100%; margin-top: 12px;">
                    <i class="fas fa-save"></i> Upload
                </button>
            </form>
        </div>

        <?php if ($user): ?>
            <div class="info-box">
                <strong>Email:</strong> <?= htmlspecialchars($user['email']); ?>
            </div>

            <form method="POST" novalidate>
                <input type="hidden" name="action" value="update">

                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($user['first_name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($user['last_name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($user['phone']); ?>" placeholder="10-15 digits" required>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn-primary">
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
</body>
</html>
