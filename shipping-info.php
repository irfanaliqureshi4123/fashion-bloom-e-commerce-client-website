<?php
session_start();
include "includes/db.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shipping Info - Fashion Bloom</title>
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
            <h1>📦 Shipping Information</h1>
            <p>Get your orders delivered quickly, safely, and with style.</p>
        </div>

        <div class="policy-content-wrapper">
            
            <!-- How It Works Section -->
            <h2 class="section-title">How Your Order Gets to You</h2>
            <div class="grid-4">
                <div class="policy-card">
                    <div class="policy-card-icon">📍</div>
                    <h3>You Order</h3>
                    <p>Add items and checkout. We send you a confirmation email instantly.</p>
                </div>
                <div class="policy-card">
                    <div class="policy-card-icon">📦</div>
                    <h3>We Pack</h3>
                    <p>Our team carefully packs your items to ensure they arrive in perfect condition.</p>
                </div>
                <div class="policy-card">
                    <div class="policy-card-icon">🚚</div>
                    <h3>We Ship</h3>
                    <p>Your package goes on its way. You receive a tracking number via email.</p>
                </div>
                <div class="policy-card">
                    <div class="policy-card-icon">✅</div>
                    <h3>You Receive</h3>
                    <p>Your package arrives safe and sound. Enjoy your Fashion Bloom purchase!</p>
                </div>
            </div>

            <!-- Shipping Options Section -->
            <h2 class="section-title">Shipping Speeds</h2>
            <div class="grid-3">
                <div class="policy-card">
                    <div class="policy-card-icon">⚡</div>
                    <h3>Standard</h3>
                    <p style="color: var(--gold-dark); font-weight: 700;">5-7 business days</p>
                    <p>The most affordable option. Perfect if you’re not in a rush.</p>
                </div>
                <div class="policy-card" style="border-color: var(--gold-primary); background: #fffcf5;">
                    <div class="policy-card-icon">🚀</div>
                    <h3>Express</h3>
                    <p style="color: var(--gold-dark); font-weight: 700;">2-3 business days</p>
                    <p><strong>Most Popular!</strong> Great balance of speed and cost.</p>
                </div>
                <div class="policy-card">
                    <div class="policy-card-icon">✈️</div>
                    <h3>Overnight</h3>
                    <p style="color: var(--gold-dark); font-weight: 700;">Next business day</p>
                    <p>Need it ASAP? Place your order before 2 PM for next-day delivery.</p>
                </div>
            </div>

            <!-- Pricing Section -->
            <h2 class="section-title">Transparent Shipping Costs</h2>
            <div class="grid-2">
                <div class="policy-card">
                    <h3>Orders Under PKR 5,000</h3>
                    <p>Standard shipping rates apply based on your location and package weight. Calculated at checkout.</p>
                </div>
                <div class="policy-card" style="background: linear-gradient(135deg, #111 0%, #222 100%); color: white; border: none;">
                    <h3 style="color: var(--gold-primary);">Orders Over PKR 5,000</h3>
                    <p style="color: rgba(255,255,255,0.9);"><strong>FREE Standard Shipping!</strong> 🎉<br>Shop more and save on delivery charges.</p>
                </div>
            </div>

            <!-- Tracking Section -->
            <div class="policy-alert">
                <h3>📍 Track Your Order Real-Time</h3>
                <p>Once your order ships, you will receive an email with a <strong>Tracking Link</strong>. Click it to see exactly where your package is and when it will arrive at your doorstep.</p>
            </div>

            <!-- Address Warning -->
            <div class="policy-alert" style="border-color: #d9534f; background: #fff5f5;">
                <h3 style="color: #c9302c;">⚠️ Double-Check Your Address</h3>
                <p style="color: #a94442;">Please verify your shipping details before confirming. We cannot redirect orders once they have been dispatched to the courier.</p>
            </div>

            <!-- Service Areas -->
            <div class="policy-card" style="margin-top: 3rem; text-align: center;">
                <h3>🌍 Nationwide Delivery</h3>
                <p>We ship to <strong>all cities across Pakistan</strong> including Karachi, Lahore, Islamabad, Faisalabad, and more.</p>
            </div>

            <!-- CTA -->
            <div class="policy-cta">
                <h3>Still Have Questions?</h3>
                <p>Our customer service team is here to help with any shipping inquiries.</p>
                <a href="contact.php" class="btn-white">Contact Us</a>
            </div>

        </div>
    </div>

    <?php include "includes/footer.php"; ?>
    <script src="/js/main.js"></script>
</body>
</html>
