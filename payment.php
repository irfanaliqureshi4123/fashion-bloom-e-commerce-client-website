<?php 
session_start();
require_once(dirname(__FILE__) . '/config/config.php');
include 'includes/header.php';
include 'includes/db.php';

$user_id = $_SESSION['user_id'] ?? null;

// Redirect to login if not logged in
if (!$user_id) {
    header('Location: login.php');
    exit();
}

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

// Validate cart not empty
if ($total <= 0) {
    header('Location: cart.php');
    exit();
}

$tax = $total * 0.17;
$grand_total = $total + $tax;

// Get shipping info from session
$shipping_info = $_SESSION['shipping_info'] ?? null;
if (!$shipping_info) {
    header('Location: checkout.php');
    exit();
}
?>

<link rel="stylesheet" href="/css/pages/checkout-shared.css">
<link rel="stylesheet" href="/css/pages/checkout.css">
<link rel="stylesheet" href="/css/pages/payment-modern.css">

<main class="checkout-container" style="margin-top: 80px;">
    <div class="container">
        <!-- Progress Indicator -->
        <div class="checkout-progress" style="--progress-width: 66%;">
            <div class="progress-step completed">
                <div class="progress-step-circle">
                    <i class="fas fa-check"></i>
                </div>
                <span class="progress-step-label">Cart</span>
            </div>
            <div class="progress-step completed">
                <div class="progress-step-circle">
                    <i class="fas fa-check"></i>
                </div>
                <span class="progress-step-label">Shipping</span>
            </div>
            <div class="progress-step active">
                <div class="progress-step-circle">
                    <i class="fas fa-credit-card"></i>
                </div>
                <span class="progress-step-label">Payment</span>
            </div>
            <div class="progress-step">
                <div class="progress-step-circle">4</div>
                <span class="progress-step-label">Confirmation</span>
            </div>
        </div>

        <div class="checkout-header">
            <h1>Payment Method</h1>
            <p class="checkout-subtitle">Review your order and choose how you'd like to pay</p>
        </div>

        <!-- Order Summary - Full Width at Top -->
        <div class="payment-summary-top">
            <div class="summary-card">
                <h3><i class="fas fa-list-check"></i> Order Summary</h3>
                
                <div class="summary-items">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="summary-item">
                            <div class="item-info">
                                <span class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></span>
                                <span class="item-qty">Qty: <?php echo $item['quantity']; ?></span>
                            </div>
                            <span class="price">PKR <?php echo number_format($item['product_price'] * $item['quantity']); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="summary-totals">
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span>PKR <?php echo number_format($total); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Shipping:</span>
                        <span class="free">Free</span>
                    </div>
                    <div class="summary-row">
                        <span>Tax (17%):</span>
                        <span>PKR <?php echo number_format($tax); ?></span>
                    </div>
                    <div class="summary-row summary-total">
                        <span>Total:</span>
                        <span>PKR <?php echo number_format($grand_total); ?></span>
                    </div>
                </div>

                <!-- Shipping Info -->
                <div class="shipping-info">
                    <h4>Shipping To:</h4>
                    <p>
                        <strong><?php echo htmlspecialchars($shipping_info['first_name']); ?> <?php echo htmlspecialchars($shipping_info['last_name']); ?></strong><br>
                        <?php echo htmlspecialchars($shipping_info['address']); ?><br>
                        <?php echo htmlspecialchars($shipping_info['city']); ?>, <?php echo htmlspecialchars($shipping_info['postal']); ?><br>
                        <i class="fas fa-phone"></i> <?php echo htmlspecialchars($shipping_info['phone']); ?>
                    </p>
                    <a href="checkout.php" class="edit-link">Edit Shipping</a>
                </div>
            </div>
        </div>

        <div class="payment-layout">
            <!-- Left Section - Payment Methods -->
            <div class="payment-main">
                <div class="form-card">
                    <h2><i class="fas fa-credit-card"></i> Select Payment Method</h2>

                    <form id="payment-form">
                        <div class="payment-methods">
                            <!-- COD -->
                            <div class="payment-method-item">
                                <label for="cod-method" class="payment-radio">
                                    <input id="cod-method" type="radio" name="payment_method" value="cod" required checked onchange="updatePaymentMethod('cod')">
                                    <div class="payment-method-content">
                                        <div class="payment-icon">
                                            <i class="fas fa-money-bill-wave"></i>
                                        </div>
                                        <div class="payment-text">
                                            <h3>Cash on Delivery</h3>
                                            <p>Pay when you receive your order</p>
                                        </div>
                                        <div class="payment-check">
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                                    </div>
                                </label>
                            </div>

                            <!-- Credit/Debit Card -->
                            <div class="payment-method-item">
                                <label for="card-method" class="payment-radio">
                                    <input id="card-method" type="radio" name="payment_method" value="card" required onchange="updatePaymentMethod('card')">
                                    <div class="payment-method-content">
                                        <div class="payment-icon">
                                            <i class="fas fa-credit-card"></i>
                                        </div>
                                        <div class="payment-text">
                                            <h3>Credit/Debit Card</h3>
                                            <p>Secure online payment</p>
                                        </div>
                                        <div class="payment-check">
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                                    </div>
                                </label>
                            </div>

                            <!-- Bank Transfer -->
                            <div class="payment-method-item">
                                <label for="bank-method" class="payment-radio">
                                    <input id="bank-method" type="radio" name="payment_method" value="bank_transfer" required onchange="updatePaymentMethod('bank_transfer')">
                                    <div class="payment-method-content">
                                        <div class="payment-icon">
                                            <i class="fas fa-university"></i>
                                        </div>
                                        <div class="payment-text">
                                            <h3>Bank Transfer</h3>
                                            <p>Direct bank transfer</p>
                                        </div>
                                        <div class="payment-check">
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Card Details Section (Hidden by default) -->
                        <div id="card-details" class="card-details" style="display: none;">
                            <h3><i class="fas fa-lock"></i> Card Information</h3>
                            <p class="payment-info">Your card information is secure and encrypted by Stripe</p>
                            
                            <div class="form-group">
                                <label for="card_name">Cardholder Name *</label>
                                <input type="text" id="card_name" name="card_name" placeholder="John Doe" autocomplete="cc-name" required>
                            </div>

                            <div class="form-group">
                                <label for="card_email">Email Address *</label>
                                <input type="email" id="card_email" name="card_email" placeholder="john@example.com" autocomplete="email" required>
                            </div>

                            <div class="form-group">
                                <span style="display: block; margin-bottom: 8px; font-weight: 600; color: #1a1a1a; font-size: 0.9rem;">Card Details *</span>
                                <div id="card-element" class="stripe-card-input"></div>
                                <div id="card-errors" class="card-error-message"></div>
                            </div>

                            <div class="security-info">
                                <p><i class="fas fa-shield-alt"></i> Your payment is processed securely by Stripe</p>
                            </div>
                        </div>

                        <!-- Bank Transfer Details Section (Hidden by default) -->
                        <div id="bank-details" class="bank-details" style="display: none;">
                            <h3><i class="fas fa-university"></i> Bank Transfer Details</h3>
                            <div class="bank-info">
                                <p><strong>Account Holder:</strong> Fashion Bloom</p>
                                <p><strong>Bank Name:</strong> NBP (National Bank of Pakistan)</p>
                                <p><strong>Account Number:</strong> 1234567890</p>
                                <p><strong>IBAN:</strong> PK36ABNA0000001234567890</p>
                                <p><strong>Swift Code:</strong> ABNAPKKA</p>
                                <p class="bank-note">Please transfer the amount and mention your order ID as reference. Your order will be confirmed after we verify the payment.</p>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Buttons -->
                <div class="payment-actions">
                    <a href="checkout.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Shipping
                    </a>
                    <button type="button" class="btn btn-primary" onclick="completeOrder()">
                        <i class="fas fa-lock"></i> Place Order
                    </button>
                </div>
            </div>
        </div>
    </div>
</main>

<?php 
// Only load Stripe if valid keys are configured
$hasValidStripeKey = defined('STRIPE_PUBLIC_KEY') && 
                     STRIPE_PUBLIC_KEY !== 'pk_test_YOUR_STRIPE_PUBLIC_KEY_HERE' && 
                     strpos(STRIPE_PUBLIC_KEY, 'pk_') === 0;
?>
<?php if ($hasValidStripeKey): ?>
<script src="https://js.stripe.com/v3/"></script>
<?php endif; ?>
<script>
// Stripe setup variables
let stripe, elements, cardElement;

// Initialize Stripe on page load
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($hasValidStripeKey): ?>
    const stripePublicKey = '<?php echo STRIPE_PUBLIC_KEY; ?>';
    
    if (stripePublicKey && stripePublicKey.startsWith('pk_')) {
        try {
            stripe = Stripe(stripePublicKey);
            elements = stripe.elements();
        } catch (error) {
            console.error('Stripe initialization error:', error);
        }
    }
    <?php else: ?>
    // Stripe not configured - disable card payment option
    const cardMethodRadio = document.getElementById('card-method');
    if (cardMethodRadio) {
        cardMethodRadio.disabled = true;
        cardMethodRadio.parentElement.style.opacity = '0.5';
        cardMethodRadio.parentElement.style.cursor = 'not-allowed';
        cardMethodRadio.parentElement.title = 'Card payments are currently unavailable';
    }
    <?php endif; ?>
});

function updatePaymentMethod(method) {
    const cardDetails = document.getElementById('card-details');
    const bankDetails = document.getElementById('bank-details');
    
    // Hide all detail sections first
    cardDetails.style.display = 'none';
    bankDetails.style.display = 'none';
    
    if (method === 'card') {
        cardDetails.style.display = 'block';
        // Initialize Stripe card element when card method is selected
        if (stripe && !cardElement) {
            initializeCardElement();
        }
    } else if (method === 'bank_transfer') {
        bankDetails.style.display = 'block';
    }
}

// Initialize Stripe Card Element
function initializeCardElement() {
    if (!stripe || !elements || cardElement) {
        return;
    }
    
    try {
        cardElement = elements.create('card', {
            style: {
                base: {
                    fontSize: '16px',
                    color: '#424770',
                    fontFamily: 'Poppins, sans-serif',
                    '::placeholder': {
                        color: '#aab7c4'
                    }
                },
                invalid: {
                    color: '#ff6b6b'
                }
            }
        });
        
        cardElement.mount('#card-element');
        
        // Handle real-time validation errors
        cardElement.on('change', function(event) {
            const displayError = document.getElementById('card-errors');
            if (event.error) {
                displayError.textContent = event.error.message;
                displayError.style.display = 'block';
            } else {
                displayError.textContent = '';
                displayError.style.display = 'none';
            }
        });
    } catch (error) {
        const displayError = document.getElementById('card-errors');
        if (displayError) {
            displayError.textContent = 'Error loading payment form. Please refresh the page.';
            displayError.style.display = 'block';
        }
    }
}

// Show notification toast
function showNotification(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = 'toast-notification show';
    toast.style.cssText = type === 'success' ? 
        'background: #4caf50; color: white;' : 
        'background: #f44336; color: white;';
    toast.innerHTML = `
        <div class="toast-content">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

function completeOrder() {
    const formData = new FormData(document.getElementById('payment-form'));
    const paymentMethod = formData.get('payment_method');
    
    if (!paymentMethod) {
        alert('Please select a payment method');
        return;
    }

    // Validate card details if card payment selected
    if (paymentMethod === 'card') {
        const cardName = document.getElementById('card_name').value;
        const cardEmail = document.getElementById('card_email').value;

        if (!cardName || !cardEmail) {
            alert('Please fill in cardholder name and email');
            return;
        }

        if (!stripe || !cardElement) {
            alert('Payment system is not ready. Please refresh the page.');
            return;
        }
    }

    // Get the button that was clicked
    const btn = event?.target || document.querySelector('button[onclick="completeOrder()"]');
    
    if (!btn) {
        alert('Error: Could not find submit button');
        return;
    }
    
    btn.disabled = true;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

    // Prepare order data
    const orderData = {
        payment_method: paymentMethod
    };

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
            if (paymentMethod === 'card') {
                // Process card payment with Stripe
                processStripePayment(data.order_id, btn, originalText);
            } else {
                // For COD and Bank Transfer
                showNotification('Order placed successfully! Order ID: ' + data.order_id);
                setTimeout(() => {
                    window.location.href = 'order-confirmation.php?order_id=' + data.order_id;
                }, 1500);
            }
        } else {
            alert('Error: ' + (data.message || 'Failed to place order'));
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    })
    .catch(error => {
        alert('Error processing order. Please try again.');
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

async function processStripePayment(orderId, btn, originalText) {
    if (!stripe || !cardElement) {
        alert('Stripe payment is not properly configured. Please refresh the page and try again.');
        btn.disabled = false;
        btn.innerHTML = originalText;
        return;
    }
    
    const cardName = document.getElementById('card_name').value;
    const cardEmail = document.getElementById('card_email').value;
    
    if (!cardName || !cardEmail) {
        alert('Please enter your name and email');
        btn.disabled = false;
        btn.innerHTML = originalText;
        return;
    }
    
    try {
        // Create payment method with Stripe
        const { paymentMethod, error } = await stripe.createPaymentMethod({
            type: 'card',
            card: cardElement,
            billing_details: {
                name: cardName,
                email: cardEmail
            }
        });

        if (error) {
            const displayError = document.getElementById('card-errors');
            displayError.textContent = error.message || 'Payment failed. Please try again.';
            displayError.style.display = 'block';
            btn.disabled = false;
            btn.innerHTML = originalText;
            return;
        }

        // Send payment method to backend
        const response = await fetch('includes/stripe-charge.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                order_id: orderId,
                payment_method_id: paymentMethod.id,
                email: cardEmail,
                amount: parseInt(<?php echo (int)$grand_total; ?>),
                currency: 'pkr'
            })
        });

        const result = await response.json();

        if (result.success) {
            showNotification('Payment successful! Order ID: ' + orderId);
            setTimeout(() => {
                window.location.href = 'order-confirmation.php?order_id=' + orderId;
            }, 1500);
        } else {
            const displayError = document.getElementById('card-errors');
            displayError.textContent = result.message || 'Payment failed. Please try again.';
            displayError.style.display = 'block';
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    } catch (error) {
        console.error('Error:', error);
        const displayError = document.getElementById('card-errors');
        displayError.textContent = 'An error occurred. Please try again.';
        displayError.style.display = 'block';
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}
</script>

<?php include 'includes/footer.php'; ?>
