<?php
session_start();
include "includes/db.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms & Conditions - Fashion Bloom</title>
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
            <h1>Terms & Conditions</h1>
            <p>Important legal agreements and rules of use.</p>
        </div>

        <div class="policy-content-wrapper">
            
            <!-- Agreement -->
            <div class="policy-alert" style="border-color: var(--black-primary);">
                <h3><i class="fas fa-file-contract"></i> Agreement to Terms</h3>
                <p>By accessing and using the Fashion Bloom website, you <strong>accept and agree</strong> to be bound by these terms using the rules outlined below. If you do not agree to these terms, please discontinue use of the site immediately.</p>
            </div>

            <!-- Use License -->
            <h2 class="section-title">Use License</h2>
            <div class="policy-card">
                <p>Permission is granted to download and view materials from our website for <strong>personal, non-commercial use only</strong>. Under this license, you may not:</p>
                <div class="grid-2" style="margin-top: 1.5rem;">
                    <div>
                        <ul class="policy-list">
                            <li>Modify or copy materials</li>
                            <li>Use for any commercial purpose</li>
                            <li>Attempt to decompile or reverse engineer any software</li>
                        </ul>
                    </div>
                    <div>
                        <ul class="policy-list">
                            <li>Remove any copyright or proprietary notations</li>
                            <li>"Mirror" the materials on any other server</li>
                            <li>Transfer the materials to another person</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Disclaimers -->
            <h2 class="section-title">Legal Disclaimers</h2>
            <div class="grid-2">
                <div class="policy-card">
                    <h3><i class="fas fa-shield-alt"></i> No Warranties</h3>
                    <p>The materials on Fashion Bloom's website are provided on an 'as is' basis. We make no warranties, expressed or implied, regarding the accuracy or reliability of the use of the materials.</p>
                </div>
                <div class="policy-card">
                    <h3><i class="fas fa-exclamation-triangle"></i> Limited Liability</h3>
                    <p>In no event shall Fashion Bloom or its suppliers be liable for any damages (including loss of data or profit) arising out of the use or inability to use the materials on our website.</p>
                </div>
            </div>

            <!-- External Links & Changes -->
            <h2 class="section-title">General Provisions</h2>
            <div class="grid-2">
                <div class="policy-card">
                    <h3><i class="fas fa-link"></i> External Links</h3>
                    <p>Fashion Bloom has not reviewed all of the sites linked to its website and is not responsible for the contents of any such linked site. Use of any such linked website is at the user's own risk.</p>
                </div>
                <div class="policy-card">
                    <h3><i class="fas fa-sync-alt"></i> Modifications</h3>
                    <p>Fashion Bloom may revise these terms of service for its website at any time without notice. By using this website you are agreeing to be bound by the then current version of these terms.</p>
                </div>
            </div>

            <!-- CTA -->
            <div class="policy-cta">
                <h3>Questions About Our Terms?</h3>
                <p>We believe in clear and fair terms. Contact us if you need clarification.</p>
                <a href="contact.php" class="btn-white">Contact Us</a>
            </div>

        </div>
    </div>

    <?php include "includes/footer.php"; ?>
    <script src="/js/main.js"></script>
</body>
</html>
