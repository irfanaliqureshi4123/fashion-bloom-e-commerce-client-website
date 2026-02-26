<?php
session_start();
include "includes/db.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - Fashion Bloom</title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/pages/policies.css">
</head>
<body>
    <?php include "includes/header.php"; ?>

    <div class="policy-page">
        <!-- Hero Section -->
        <div class="policy-hero">
            <h1>Privacy Policy</h1>
            <p>Your trust is our top priority. We protect your data securely.</p>
        </div>

        <div class="policy-content-wrapper">
            
            <div class="policy-card">
                <h3>Information We Collect</h3>
                <p style="margin-bottom: 1rem;">To provide you with the best shopping experience, we confirm the following details:</p>
                <ul class="policy-list">
                    <li><strong>Personal Details:</strong> Name, email address, phone number</li>
                    <li><strong>Shipping Data:</strong> Delivery address and billing information</li>
                    <li><strong>Payment Info:</strong> Securely processed (we do not store full credit card numbers)</li>
                    <li><strong>Account History:</strong> Your past orders and favorites</li>
                </ul>
            </div>

            <h2 class="section-title">How We Use Your Data</h2>
            <div class="grid-3">
                <div class="policy-card">
                    <div class="policy-card-icon">🛍️</div>
                    <h3>Process Orders</h3>
                    <p>To fulfill your purchases, ship items to the correct address, and send order confirmations.</p>
                </div>
                <div class="policy-card">
                    <div class="policy-card-icon">🛡️</div>
                    <h3>Fraud Prevention</h3>
                    <p>To detect and prevent fraudulent transactions, keeping your account and money safe.</p>
                </div>
                <div class="policy-card">
                    <div class="policy-card-icon">📧</div>
                    <h3>Updates & Offers</h3>
                    <p>To send you shipping updates and (optionally) exclusive promotions. You can opt-out anytime.</p>
                </div>
            </div>

            <h2 class="section-title">Data Security & Sharing</h2>
            <div class="grid-2">
                <div class="policy-card">
                    <h3><i class="fas fa-lock"></i> Security Measures</h3>
                    <p>We use industry-standard SSL encryption to protect your data during transmission. Your payment details are processed by secure, PCI-compliant payment gateways.</p>
                </div>
                <div class="policy-card">
                    <h3><i class="fas fa-handshake"></i> Third Parties</h3>
                    <p>We share limited data only with trusted partners essential to our service, such as:</p>
                    <ul class="policy-list" style="margin-top: 0.5rem;">
                        <li>Shipping Carriers (to deliver your package)</li>
                        <li>Payment Processors (to verify payments)</li>
                    </ul>
                </div>
            </div>

            <div class="policy-alert" style="margin-top: 3rem;">
                <h3>🍪 Cookies and Tracking</h3>
                <p>We use cookies to remember your cart items and preferences, and to analyze how our site works to improve it. You can control cookie settings in your browser at any time.</p>
            </div>

            <div class="policy-cta">
                <h3>Privacy Concerns?</h3>
                <p>We believe in full transparency. Contact our Data Privacy officer if you have questions.</p>
                <a href="contact.php" class="btn-white">Contact Us</a>
            </div>

        </div>
    </div>

    <?php include "includes/footer.php"; ?>
    <script src="/js/main.js"></script>
</body>
</html>
