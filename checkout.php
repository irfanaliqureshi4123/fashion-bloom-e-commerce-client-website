<?php 
session_start();
include 'includes/header.php';
include 'includes/db.php';

$user_id = $_SESSION['user_id'] ?? null;

// Redirect to login if not logged in
if (!$user_id) {
    header('Location: login.php');
    exit();
}

// Get user details
$sql_user = "SELECT * FROM users WHERE id = ?";
$stmt_user = $pdo->prepare($sql_user);
$stmt_user->execute([$user_id]);
$user = $stmt_user->fetch();

// Get cart items from database
$sql = "SELECT * FROM shopping_cart WHERE user_id = ? ORDER BY updated_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();

// Redirect if cart is empty
if (empty($cart_items)) {
    header('Location: cart.php');
    exit();
}

// Calculate total
$total = 0;
foreach ($cart_items as $item) {
    $total += $item['product_price'] * $item['quantity'];
}
?>

<link rel="stylesheet" href="/css/pages/checkout-shared.css">
<link rel="stylesheet" href="/css/pages/checkout.css">

<main class="checkout-container" style="margin-top: 80px;">
    <div class="container">
        <!-- Progress Indicator -->
        <div class="checkout-progress" style="--progress-width: 33%;">
            <div class="progress-step completed">
                <div class="progress-step-circle">
                    <i class="fas fa-check"></i>
                </div>
                <span class="progress-step-label">Cart</span>
            </div>
            <div class="progress-step active">
                <div class="progress-step-circle">
                    <i class="fas fa-truck"></i>
                </div>
                <span class="progress-step-label">Shipping</span>
            </div>
            <div class="progress-step">
                <div class="progress-step-circle">3</div>
                <span class="progress-step-label">Payment</span>
            </div>
            <div class="progress-step">
                <div class="progress-step-circle">4</div>
                <span class="progress-step-label">Confirmation</span>
            </div>
        </div>

        <div class="checkout-header">
            <h1>Shipping Information</h1>
            <p class="checkout-subtitle">Enter your delivery address and contact details</p>
        </div>

        <div class="checkout-layout">
            <!-- Checkout Form -->
            <div class="checkout-form-section">
               
                <div class="modern-card">
                    <div class="modern-card-header">
                        <i class="fas fa-shipping-fast"></i>
                        <h2>Delivery Details</h2>
                    </div>
                    
                    <form id="checkout-form">
                        <div class="form-row">
                            <div class="form-field">
                                <label for="first_name">First Name *</label>
                                <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" autocomplete="given-name" required>
                                <span class="form-field-icon"><i class="fas fa-user"></i></span>
                                <span class="field-error-message">Please enter your first name</span>
                            </div>

                            <div class="form-field">
                                <label for="last_name">Last Name *</label>
                                <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" autocomplete="family-name" required>
                                <span class="form-field-icon"><i class="fas fa-user"></i></span>
                                <span class="field-error-message">Please enter your last name</span>
                            </div>
                        </div>

                        <div class="form-field">
                            <label for="email">Email Address *</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" autocomplete="email" required>
                            <span class="form-field-icon"><i class="fas fa-envelope"></i></span>
                            <span class="field-error-message">Please enter a valid email address</span>
                        </div>

                        <div class="form-field">
                            <label for="phone">Phone Number *</label>
                            <input type="tel" id="phone" name="phone" placeholder="+92 300 1234567" autocomplete="tel" required>
                            <span class="form-field-icon"><i class="fas fa-phone"></i></span>
                            <span class="field-error-message">Please enter your phone number</span>
                        </div>

                        <div class="form-field">
                            <label for="address">Street Address *</label>
                            <input type="text" id="address" name="address" placeholder="House number and street name" autocomplete="street-address" required>
                            <span class="form-field-icon"><i class="fas fa-map-marker-alt"></i></span>
                            <span class="field-error-message">Please enter your address</span>
                        </div>

                        <div class="form-row">
                            <div class="form-field">
                                <label for="city">City *</label>
                                <input type="text" id="city" name="city" placeholder="City" autocomplete="address-level2" required>
                                <span class="form-field-icon"><i class="fas fa-city"></i></span>
                                <span class="field-error-message">Please enter your city</span>
                            </div>

                            <div class="form-field">
                                <label for="postal">Postal Code *</label>
                                <input type="text" id="postal" name="postal" placeholder="Postal code" autocomplete="postal-code" required>
                                <span class="form-field-icon"><i class="fas fa-mail-bulk"></i></span>
                                <span class="field-error-message">Please enter postal code</span>
                            </div>
                        </div>

                        <div class="form-field">
                            <label for="notes">Order Notes (Optional)</label>
                            <textarea id="notes" name="notes" placeholder="Special delivery instructions..." rows="3"></textarea>
                            <span class="field-error-message"></span>
                        </div>
                    </form>
                </div>

                <!-- Continue to Payment Button -->
                <div class="continue-button-section" style="margin-top: 2rem;">
                    <button type="button" class="btn-modern btn-modern-primary" onclick="continueToPayment()" style="width: 100%;">
                        <span>Continue to Payment</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
           
<script>
function scrollToPayment() {
    const paymentSection = document.querySelector('.payment-methods').closest('.form-card');
    paymentSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function continueToPayment() {
    // Validate shipping form
    const formData = new FormData(document.getElementById('checkout-form'));
    const first_name = formData.get('first_name');
    const last_name = formData.get('last_name');
    const email = formData.get('email');
    const phone = formData.get('phone');
    const address = formData.get('address');
    const city = formData.get('city');
    const postal = formData.get('postal');
    
    if (!first_name || !last_name || !email || !phone || !address || !city || !postal) {
        alert('Please fill in all required fields');
        return;
    }
    
    // Store shipping info in session via AJAX
    fetch('includes/save_shipping.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            first_name: first_name,
            last_name: last_name,
            email: email,
            phone: phone,
            address: address,
            city: city,
            postal: postal,
            notes: formData.get('notes')
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = 'payment.php';
        } else {
            alert('Error saving shipping information');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error processing request');
    });
}

function placeOrder() {
    const formData = new FormData(document.getElementById('checkout-form'));
    const paymentMethod = document.querySelector('input[name="payment"]:checked').value;
    
    const orderData = {
        first_name: formData.get('first_name'),
        last_name: formData.get('last_name'),
        email: formData.get('email'),
        phone: formData.get('phone'),
        address: formData.get('address'),
        city: formData.get('city'),
        postal: formData.get('postal'),
        notes: formData.get('notes'),
        payment_method: paymentMethod
    };

    // Validate form
    if (!orderData.first_name || !orderData.last_name || !orderData.email || !orderData.phone || !orderData.address || !orderData.city || !orderData.postal) {
        alert('Please fill in all required fields');
        return;
    }

    // Disable button to prevent double submission
    const btn = event.target;
    btn.disabled = true;
    btn.textContent = 'Processing...';

    // Send order to server
    fetch('includes/process_order.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(orderData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Order placed successfully! Order ID: ' + data.order_id);
            window.location.href = 'order-confirmation.php?order_id=' + data.order_id;
        } else {
            alert('Error: ' + (data.message || 'Failed to place order'));
            btn.disabled = false;
            btn.textContent = 'Place Order';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error processing order. Please try again.');
        btn.disabled = false;
        btn.textContent = 'Place Order';
    });
}
</script>

