<?php
session_start();
include "includes/db.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Returns Policy - Fashion Bloom</title>
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
            <h1>↩️ Returns Policy</h1>
            <p>Not happy? Return it easily within 30 days for a full refund.</p>
        </div>

        <div class="policy-content-wrapper">
            
            <!-- Main Benefit -->
            <div class="policy-alert" style="text-align: center; border-color: #4CAF50; background: #f0fff4;">
                <h3 style="color: #2E7D32; font-size: 1.5rem;">✓ 30-Day Easy Returns</h3>
                <p style="color: #1B5E20; font-size: 1.1rem;">You have a full 30 days from delivery to return any unused item in its original condition. No hassle, no stress.</p>
            </div>

            <!-- Return Process -->
            <h2 class="section-title">How to Return (4 Simple Steps)</h2>
            <div class="step-wizard">
                <div class="step-item">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <h3>Contact Us</h3>
                        <p>Send us a message with your order number and reason for return. This helps us improve our products.</p>
                    </div>
                </div>
                <div class="step-item">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <h3>Get Label</h3>
                        <p>We'll approve your return and email you a prepaid shipping label. <strong>You don't pay for return shipping!</strong></p>
                    </div>
                </div>
                <div class="step-item">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <h3>Pack & Ship</h3>
                        <p>Pack the item in its original box (if possible), attach the free label, and drop it off at any courier office.</p>
                    </div>
                </div>
                <div class="step-item">
                    <div class="step-number" style="background: #4CAF50;">4</div>
                    <div class="step-content">
                        <h3>Get Refund</h3>
                        <p>Once we receive and inspect the item, we'll process your refund to your original payment method within 5-7 business days.</p>
                    </div>
                </div>
            </div>

            <!-- Comparison Section -->
            <div class="grid-2" style="margin-top: 4rem;">
                <div class="policy-card" style="border-top: 5px solid #4CAF50;">
                    <h3 style="color: #2E7D32;">✓ What You CAN Return</h3>
                    <ul class="policy-list">
                        <li>Unused items (never worn)</li>
                        <li>Original tags attached</li>
                        <li>Original packaging included</li>
                        <li>Within 30 days of receiving</li>
                        <li>Regular priced items</li>
                    </ul>
                </div>
                <div class="policy-card" style="border-top: 5px solid #d9534f;">
                    <h3 style="color: #c9302c;">✗ What You CANNOT Return</h3>
                    <ul class="policy-list">
                        <li>Worn or washed items</li>
                        <li>Items without original tags</li>
                        <li>Damaged items (caused by user)</li>
                        <li>Clearance / Final Sale items</li>
                        <li>Custom-made orders</li>
                    </ul>
                </div>
            </div>

            <!-- Defective Items -->
            <h2 class="section-title">Defective or Broken Items</h2>
            <div class="policy-card" style="background: #fffcf5; border-left: 4px solid var(--gold-primary);">
                <h3>Arrived Damaged? It's On Us.</h3>
                <p>If your item arrives broken or defective, please contact us immediately with photos. we will offer a <strong>Free Replacement</strong> or a <strong>Full Refund</strong> instantly. We want to ensure you are 100% satisfied.</p>
            </div>

            <!-- CTA -->
            <div class="policy-cta">
                <h3>Ready to Start a Return?</h3>
                <p>It takes less than 2 minutes to initiate your return request.</p>
                <a href="contact.php" class="btn-white">Start Return Process</a>
            </div>

        </div>
    </div>

    <?php include "includes/footer.php"; ?>
    <script src="/js/main.js"></script>
</body>
</html>
