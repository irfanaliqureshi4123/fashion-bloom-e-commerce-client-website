<?php
session_start();
require_once(dirname(__FILE__) . '/config/config.php');
include 'includes/db.php';

$user_id = $_SESSION['user_id'] ?? null;
$order_id = $_GET['order_id'] ?? null;

// Redirect to login if not logged in
if (!$user_id) {
    header('Location: login.php');
    exit();
}

// Validate order exists
if (!$order_id) {
    header('Location: payment.php');
    exit();
}

// Get order details
$sql = "SELECT * FROM orders WHERE id = ? AND user_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: payment.php');
    exit();
}

$stripe_public_key = STRIPE_PUBLIC_KEY;
$stripe_secret_key = STRIPE_SECRET_KEY;

// Check if Stripe keys are configured
if (!$stripe_public_key || !$stripe_secret_key) {
    $error = 'Stripe is not properly configured. Please contact support.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stripe Payment - Fashion Bloom</title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e8eef7 100%);
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            padding-top: 80px;
        }

        .stripe-container {
            max-width: 600px;
            margin: 60px auto;
            padding: 0 20px;
        }

        .stripe-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            animation: slideDown 0.4s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .stripe-header {
            background: linear-gradient(135deg, #d4af37 0%, #b8941f 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }

        .stripe-header h1 {
            font-size: 1.8rem;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .stripe-header p {
            font-size: 0.95rem;
            opacity: 0.95;
        }

        .stripe-body {
            padding: 40px 30px;
        }

        .order-summary {
            background: rgba(212, 175, 55, 0.05);
            border-left: 4px solid #d4af37;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .order-summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 0.95rem;
        }

        .order-summary-row:last-child {
            margin-bottom: 0;
        }

        .order-summary-row strong {
            color: #1a1a1a;
        }

        .order-amount {
            font-size: 1.4rem;
            font-weight: 700;
            color: #d4af37;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid rgba(212, 175, 55, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #1a1a1a;
            font-size: 0.95rem;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 0.95rem;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s;
            outline: none;
        }

        .form-group input:focus {
            border-color: #d4af37;
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.1);
        }

        #card-element {
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 0.95rem;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s;
        }

        .StripeElement--focus {
            border-color: #d4af37;
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.1);
        }

        .error-message {
            color: #d32f2f;
            font-size: 0.9rem;
            margin-top: 8px;
            display: none;
        }

        .error-message.show {
            display: block;
        }

        .stripe-footer {
            padding: 30px;
            background: #f9f9f9;
            display: flex;
            gap: 15px;
        }

        .btn {
            flex: 1;
            padding: 14px 24px;
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #d4af37 0%, #b8941f 100%);
            color: white;
        }

        .btn-primary:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(212, 175, 55, 0.3);
        }

        .btn-primary:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .btn-secondary {
            background: #e0e0e0;
            color: #1a1a1a;
        }

        .btn-secondary:hover {
            background: #d0d0d0;
        }

        .security-badge {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            font-size: 0.85rem;
            color: #666;
        }

        .security-badge i {
            color: #4caf50;
            font-size: 1.2rem;
            margin-right: 8px;
        }

        .loading-spinner {
            display: none;
            text-align: center;
            padding: 40px;
        }

        .spinner {
            display: inline-block;
            width: 40px;
            height: 40px;
            border: 4px solid rgba(212, 175, 55, 0.2);
            border-top-color: #d4af37;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .error-state {
            background: #ffebee;
            border: 2px solid #ef5350;
            padding: 20px;
            border-radius: 8px;
            color: #c62828;
            margin-bottom: 20px;
        }

        .error-state i {
            margin-right: 10px;
            font-size: 1.2rem;
        }

        @media (max-width: 600px) {
            .stripe-container {
                margin: 30px auto;
            }

            .stripe-header {
                padding: 30px 20px;
            }

            .stripe-header h1 {
                font-size: 1.5rem;
            }

            .stripe-body {
                padding: 25px 20px;
            }

            .stripe-footer {
                flex-direction: column;
                padding: 20px;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="stripe-container">
        <?php if (isset($error)): ?>
            <div class="stripe-card">
                <div class="stripe-body">
                    <div class="error-state">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= htmlspecialchars($error); ?>
                    </div>
                    <a href="payment.php" class="btn btn-secondary" style="display: flex; justify-content: center;">
                        <i class="fas fa-arrow-left"></i> Back to Payment
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="stripe-card">
                <div class="stripe-header">
                    <h1><i class="fas fa-credit-card"></i> Complete Payment</h1>
                    <p>Secure payment powered by Stripe</p>
                </div>

                <div class="stripe-body">
                    <!-- Order Summary -->
                    <div class="order-summary">
                        <div class="order-summary-row">
                            <strong>Order Number:</strong>
                            <span><?= htmlspecialchars($order['order_number']); ?></span>
                        </div>
                        <div class="order-summary-row">
                            <strong>Subtotal:</strong>
                            <span>PKR <?= number_format($order['subtotal'], 0); ?></span>
                        </div>
                        <div class="order-summary-row">
                            <strong>Tax (17%):</strong>
                            <span>PKR <?= number_format($order['tax'], 0); ?></span>
                        </div>
                        <div class="order-summary-row">
                            <strong>Shipping:</strong>
                            <span><?= $order['shipping'] == 0 ? 'Free' : 'PKR ' . number_format($order['shipping'], 0); ?></span>
                        </div>
                        <div class="order-amount">
                            <span>Total Amount:</span>
                            <span>PKR <?= number_format($order['total_price'], 0); ?></span>
                        </div>
                    </div>

                    <!-- Payment Form -->
                    <form id="stripe-form">
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                placeholder="you@example.com"
                                value="<?= htmlspecialchars($order['email']); ?>"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <span style="display: block; margin-bottom: 8px; font-weight: 600; color: #1a1a1a; font-size: 0.9rem;">Card Details</span>
                            <div id="card-element"></div>
                            <div id="card-errors" class="error-message"></div>
                        </div>

                        <div class="form-group">
                            <label for="card-name">Cardholder Name</label>
                            <input 
                                type="text" 
                                id="card-name" 
                                name="card_name" 
                                placeholder="Full name on card"
                                value="<?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?>"
                                required
                            >
                        </div>
                    </form>

                    <!-- Security Badge -->
                    <div class="security-badge">
                        <i class="fas fa-shield-alt"></i>
                        <span>Your payment information is secured by Stripe</span>
                    </div>
                </div>

                <div class="stripe-footer">
                    <a href="payment.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                    <button type="button" class="btn btn-primary" id="submit-button" onclick="submitPayment()">
                        <i class="fas fa-lock"></i> Pay PKR <?= number_format($order['total_price'], 0); ?>
                    </button>
                </div>

                <!-- Loading State -->
                <div class="loading-spinner" id="loading-spinner">
                    <div class="spinner"></div>
                    <p style="margin-top: 20px; color: #666;">Processing your payment...</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://js.stripe.com/v3/"></script>
    <script>
        const stripePublicKey = '<?= htmlspecialchars($stripe_public_key); ?>';
        const orderId = <?= $order_id; ?>;
        const orderTotal = <?= $order['total_price']; ?>;

        let stripe, elements, cardElement;

        document.addEventListener('DOMContentLoaded', function() {
            if (!stripePublicKey) {
                console.error('Stripe public key not set');
                return;
            }

            // Initialize Stripe
            stripe = Stripe(stripePublicKey);
            elements = stripe.elements();
            cardElement = elements.create('card', {
                style: {
                    base: {
                        fontSize: '16px',
                        color: '#1a1a1a',
                        fontFamily: 'Poppins, sans-serif'
                    }
                }
            });

            cardElement.mount('#card-element');

            // Handle card errors
            cardElement.on('change', function(event) {
                const displayError = document.getElementById('card-errors');
                if (event.error) {
                    displayError.textContent = event.error.message;
                    displayError.classList.add('show');
                } else {
                    displayError.textContent = '';
                    displayError.classList.remove('show');
                }
            });
        });

        async function submitPayment() {
            const submitButton = document.getElementById('submit-button');
            const form = document.getElementById('stripe-form');
            const spinner = document.getElementById('loading-spinner');
            const email = document.getElementById('email').value;
            const cardName = document.getElementById('card-name').value;

            // Validate form
            if (!form.checkValidity()) {
                alert('Please fill in all required fields');
                return;
            }

            // Disable button and show spinner
            submitButton.disabled = true;
            spinner.style.display = 'block';

            try {
                // Create payment method
                const { error, paymentMethod } = await stripe.createPaymentMethod({
                    type: 'card',
                    card: cardElement,
                    billing_details: {
                        email: email,
                        name: cardName
                    }
                });

                if (error) {
                    const errorElement = document.getElementById('card-errors');
                    errorElement.textContent = error.message;
                    errorElement.classList.add('show');
                    submitButton.disabled = false;
                    spinner.style.display = 'none';
                    return;
                }

                // Send to backend for payment processing
                const response = await fetch('includes/stripe-charge.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        order_id: orderId,
                        payment_method_id: paymentMethod.id,
                        email: email,
                        amount: orderTotal,
                        currency: 'pkr'
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Payment successful
                    showSuccessMessage('Payment processed successfully!');
                    setTimeout(() => {
                        window.location.href = 'order-confirmation.php?order_id=' + orderId;
                    }, 1500);
                } else if (data.requires_action) {
                    // Payment requires additional action (3D Secure, etc.)
                    const { client_secret } = data;
                    
                    const { error: confirmError } = await stripe.confirmCardPayment(client_secret, {
                        payment_method: paymentMethod.id
                    });

                    if (confirmError) {
                        const errorElement = document.getElementById('card-errors');
                        errorElement.textContent = confirmError.message;
                        errorElement.classList.add('show');
                    } else {
                        showSuccessMessage('Payment completed successfully!');
                        setTimeout(() => {
                            window.location.href = 'order-confirmation.php?order_id=' + orderId;
                        }, 1500);
                    }
                } else {
                    const errorElement = document.getElementById('card-errors');
                    errorElement.textContent = data.message || 'Payment failed. Please try again.';
                    errorElement.classList.add('show');
                    submitButton.disabled = false;
                    spinner.style.display = 'none';
                }
            } catch (error) {
                console.error('Error:', error);
                const errorElement = document.getElementById('card-errors');
                errorElement.textContent = 'An error occurred. Please try again.';
                errorElement.classList.add('show');
                submitButton.disabled = false;
                spinner.style.display = 'none';
            }
        }

        function showSuccessMessage(message) {
            const toast = document.createElement('div');
            toast.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: #4caf50;
                color: white;
                padding: 15px 25px;
                border-radius: 8px;
                z-index: 9999;
                display: flex;
                align-items: center;
                gap: 10px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                animation: slideInRight 0.3s ease-out;
            `;
            toast.innerHTML = '<i class="fas fa-check-circle"></i> ' + message;
            document.body.appendChild(toast);
        }
    </script>

    <style>
        @keyframes slideInRight {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    </style>
</body>
</html>
