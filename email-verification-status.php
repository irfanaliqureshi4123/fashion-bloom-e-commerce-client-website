<?php
/**
 * Email Verification Status Check
 * Quick reference page to verify that the email verification system is working
 */

require_once __DIR__ . '/includes/db.php';

// Get statistics
try {
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_users,
            SUM(CASE WHEN email_verified = 1 THEN 1 ELSE 0 END) as verified_users,
            SUM(CASE WHEN email_verified = 0 THEN 1 ELSE 0 END) as unverified_users
        FROM users
    ");
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get unverified users list
    $stmt = $pdo->query("
        SELECT id, first_name, last_name, email, created_at, token_expires
        FROM users 
        WHERE email_verified = 0
        ORDER BY created_at DESC
        LIMIT 20
    ");
    $unverified = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get recently verified users
    $stmt = $pdo->query("
        SELECT id, first_name, last_name, email, created_at
        FROM users 
        WHERE email_verified = 1
        ORDER BY created_at DESC
        LIMIT 10
    ");
    $recent_verified = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

$verification_rate = $stats['total_users'] > 0 
    ? round(($stats['verified_users'] / $stats['total_users']) * 100, 1) 
    : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification Status - Fashion Bloom Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #f5f5f5 0%, #e91e63 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        h1 {
            color: white;
            text-align: center;
            margin-bottom: 30px;
            font-size: 28px;
            text-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-card h3 {
            color: #999;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 12px;
        }

        .stat-card .value {
            font-size: 36px;
            font-weight: 700;
            color: #e91e63;
        }

        .stat-card .subtext {
            color: #999;
            font-size: 12px;
            margin-top: 8px;
        }

        .stat-card.verified .value {
            color: #4caf50;
        }

        .stat-card.unverified .value {
            color: #ff9800;
        }

        .progress-bar {
            width: 100%;
            height: 8px;
            background: #f0f0f0;
            border-radius: 10px;
            overflow: hidden;
            margin-top: 12px;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #4caf50, #66bb6a);
            border-radius: 10px;
        }

        .section {
            background: white;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .section h2 {
            color: #333;
            font-size: 18px;
            margin-bottom: 20px;
            border-bottom: 2px solid #e91e63;
            padding-bottom: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #f9f9f9;
            color: #333;
            font-weight: 600;
            text-align: left;
            padding: 12px;
            border-bottom: 2px solid #e91e63;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #f0f0f0;
            color: #666;
            font-size: 14px;
        }

        tr:hover {
            background: #f9f9f9;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-verified {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .status-unverified {
            background: #fff3e0;
            color: #e65100;
        }

        .time-remaining {
            font-size: 12px;
            color: #ff9800;
        }

        .time-expired {
            font-size: 12px;
            color: #f44336;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        .empty-state p {
            font-size: 16px;
        }

        .button {
            display: inline-block;
            background: #e91e63;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            margin-top: 20px;
            transition: all 0.3s ease;
        }

        .button:hover {
            background: #c2185b;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(233,30,99,0.3);
        }

        .refresh-note {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 12px 16px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #1565c0;
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            h1 {
                font-size: 22px;
            }

            table {
                font-size: 12px;
            }

            th, td {
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📧 Email Verification Status</h1>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Users</h3>
                <div class="value"><?= $stats['total_users'] ?></div>
                <div class="subtext">registered accounts</div>
            </div>

            <div class="stat-card verified">
                <h3>Verified Users</h3>
                <div class="value"><?= $stats['verified_users'] ?></div>
                <div class="subtext">emails confirmed</div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?= $verification_rate ?>%"></div>
                </div>
                <div class="subtext"><?= $verification_rate ?>% verification rate</div>
            </div>

            <div class="stat-card unverified">
                <h3>Unverified Users</h3>
                <div class="value"><?= $stats['unverified_users'] ?></div>
                <div class="subtext">pending confirmation</div>
            </div>
        </div>

        <!-- Unverified Users Section -->
        <div class="section">
            <h2>⏳ Unverified Users (<?= count($unverified) ?>)</h2>

            <?php if (empty($unverified)): ?>
                <div class="empty-state">
                    <p>✅ All users have verified their emails!</p>
                </div>
            <?php else: ?>
                <div class="refresh-note">
                    💡 These users registered but haven't verified their emails yet. They won't be able to login.
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Registered</th>
                            <th>Token Expires</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($unverified as $user): ?>
                            <tr>
                                <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td><?= date('M d, Y H:i', strtotime($user['created_at'])) ?></td>
                                <td>
                                    <?php 
                                    $expires = strtotime($user['token_expires']);
                                    $now = time();
                                    if ($expires > $now) {
                                        $hours = floor(($expires - $now) / 3600);
                                        echo "<span class='time-remaining'>Expires in {$hours}h</span>";
                                    } else {
                                        echo "<span class='time-expired'>⚠️ Expired</span>";
                                    }
                                    ?>
                                </td>
                                <td><span class="status-badge status-unverified">Pending</span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Recently Verified Section -->
        <div class="section">
            <h2>✅ Recently Verified Users (Last 10)</h2>

            <?php if (empty($recent_verified)): ?>
                <div class="empty-state">
                    <p>No verified users yet</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Registered</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_verified as $user): ?>
                            <tr>
                                <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td><?= date('M d, Y H:i', strtotime($user['created_at'])) ?></td>
                                <td><span class="status-badge status-verified">Verified</span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <a href="index.php" class="button">← Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
