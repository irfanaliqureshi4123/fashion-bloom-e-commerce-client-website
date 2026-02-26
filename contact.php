<?php include 'includes/header.php'; ?>

<!-- Page-Specific CSS -->
<link rel="stylesheet" href="/css/pages/contact.css">

<!-- Hero Section -->
<section class="contact-hero">
    <div class="container">
        <div class="hero-content">
            <h1>Get in Touch</h1>
            <p>We're here to help you with any questions, concerns, or feedback you may have</p>
        </div>
    </div>
</section>

<!-- Contact Information -->
<section class="contact-info">
    <div class="container">
        <div class="info-grid">
            <div class="info-card">
                <div class="info-icon"><i class="fas fa-map-marker-alt"></i></div>
                <h3>Visit Our Store</h3>
                <p>123 Fashion Street<br>Gulberg III, Lahore<br>Punjab, Pakistan 54000</p>
            </div>
            <div class="info-card">
                <div class="info-icon"><i class="fas fa-phone"></i></div>
                <h3>Call Us</h3>
                <p>Main: +92 42 1234 5678<br>Customer Service: +92 300 1234567<br>WhatsApp: +92 321 9876543</p>
            </div>
            <div class="info-card">
                <div class="info-icon"><i class="fas fa-envelope"></i></div>
                <h3>Email Us</h3>
                <p>General: info@fashionbloom.com<br>Support: support@fashionbloom.com<br>Sales: sales@fashionbloom.com</p>
            </div>
            <div class="info-card">
                <div class="info-icon"><i class="fas fa-clock"></i></div>
                <h3>Business Hours</h3>
                <p>Mon–Fri: 9:00 AM – 8:00 PM<br>Sat: 10:00 AM – 6:00 PM<br>Sun: 12:00 PM – 5:00 PM</p>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="contact-info" style="background: white; padding: 60px 0;">
    <div class="container">
        <h2 style="text-align: center; color: #d4af37; font-size: 2rem; margin-bottom: 50px;">Why Choose Us</h2>
        <div class="info-grid">
            <div class="info-card">
                <div class="info-icon"><i class="fas fa-headset"></i></div>
                <h3>24/7 Customer Support</h3>
                <p>Our dedicated support team is available around the clock to assist you</p>
            </div>
            <div class="info-card">
                <div class="info-icon"><i class="fas fa-shipping-fast"></i></div>
                <h3>Order Tracking</h3>
                <p>Track your orders in real-time and get updates on delivery status</p>
            </div>
            <div class="info-card">
                <div class="info-icon"><i class="fas fa-undo"></i></div>
                <h3>Easy Returns</h3>
                <p>Hassle-free return policy with free return shipping</p>
            </div>
        </div>
    </div>
</section>

<!-- Contact Form Section -->
<section class="contact-form-section">
    <div class="container">
        <div class="form-content">
            <div class="form-text">
                <h2>Send Us a Message</h2>
                <p>Have a question, suggestion, or need assistance? Fill out the form below and our team will get back to you within 24 hours.</p>
            </div>

            <div class="contact-form">
                <form id="contactForm" method="POST" action="/api/contact.php">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="firstName">First Name *</label>
                            <input type="text" id="firstName" name="firstName" autocomplete="given-name" required>
                        </div>
                        <div class="form-group">
                            <label for="lastName">Last Name *</label>
                            <input type="text" id="lastName" name="lastName" autocomplete="family-name" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input type="email" id="email" name="email" autocomplete="email" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" autocomplete="tel">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="subject">Subject *</label>
                        <select id="subject" name="subject" required>
                            <option value="">Select a subject</option>
                            <option value="general">General Inquiry</option>
                            <option value="order">Order Support</option>
                            <option value="product">Product Information</option>
                            <option value="technical">Technical Support</option>
                            <option value="billing">Billing & Payment</option>
                            <option value="returns">Returns & Exchanges</option>
                            <option value="feedback">Feedback & Suggestions</option>
                            <option value="partnership">Business Partnership</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="orderNumber">Order Number (if applicable)</label>
                        <input type="text" id="orderNumber" name="orderNumber" placeholder="FB-12345678" autocomplete="off">
                    </div>

                    <div class="form-group">
                        <label for="message">Message *</label>
                        <textarea id="message" name="message" rows="6" required></textarea>
                    </div>

                    <button type="submit" class="submit-btn"><i class="fas fa-paper-plane"></i> Send Message</button>
                </form>
            </div>
        </div>
    </div>
</section>



<!-- Success Modal -->
<div id="successModal" class="modal">
    <div class="modal-content">
        <div class="modal-header"><i class="fas fa-check-circle"></i><h3>Message Sent!</h3></div>
        <div class="modal-body"><p>Thank you for contacting us. We'll respond within 24 hours.</p></div>
        <div class="modal-footer"><button class="modal-close">Close</button></div>
    </div>
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

<!-- Page-Specific JS -->
<script src="/js/main.js"></script>
<script src="/js/contact.js"></script>

<?php include 'includes/footer.php'; ?>
