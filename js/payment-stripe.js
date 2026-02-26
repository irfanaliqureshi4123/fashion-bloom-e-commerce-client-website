/**
 * Payment Stripe Integration - JavaScript Functions
 * Handles Stripe Elements, payment processing, and order completion
 */

// Stripe setup variables
let stripe, elements, cardElement;

/**
 * Initialize Stripe on page load
 * Sets up Stripe Elements and card input field
 */
document.addEventListener('DOMContentLoaded', function() {
    const stripePublicKey = '<?php echo STRIPE_PUBLIC_KEY; ?>';

    if (!stripePublicKey) {
        const cardDetails = document.getElementById('card-details');
        if (cardDetails) {
            const errorMsg = document.createElement('div');
            errorMsg.className = 'alert alert-error';
            errorMsg.innerHTML = '<i class="fas fa-exclamation-circle"></i> Stripe is not configured. Please contact support.';
            cardDetails.insertBefore(errorMsg, cardDetails.firstChild);
        }
        return;
    }

    if (stripePublicKey.startsWith('pk_')) {
        try {
            stripe = Stripe(stripePublicKey);
            elements = stripe.elements();
        } catch (error) {
            console.error('Stripe initialization error:', error);
        }
    } else if (stripePublicKey !== 'pk_test_YOUR_STRIPE_PUBLIC_KEY_HERE') {
        console.warn('Stripe public key appears to be a placeholder');
    }
});

/**
 * Update payment method display
 * Shows/hides card or bank transfer details based on selection
 * @param {string} method - Payment method ('card' or 'bank_transfer')
 */
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

/**
 * Initialize Stripe Card Element
 * Creates and mounts the secure card input field
 */
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
        console.error('Card element initialization error:', error);
        const displayError = document.getElementById('card-errors');
        if (displayError) {
            displayError.textContent = 'Error loading payment form. Please refresh the page.';
            displayError.style.display = 'block';
        }
    }
}

/**
 * Show notification toast
 * Displays success or error messages to the user
 * @param {string} message - Message to display
 * @param {string} type - Type of notification ('success' or 'error')
 */
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

/**
 * Complete order processing
 * Validates payment method and initiates order placement
 */
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
        console.error('Order processing error:', error);
        alert('Error processing order. Please check the console for details.');
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

/**
 * Process Stripe payment
 * Handles the complete Stripe payment flow
 * @param {string} orderId - Order ID from server
 * @param {HTMLElement} btn - Submit button element
 * @param {string} originalText - Original button text
 */
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
                amount: window.GRAND_TOTAL,
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
        console.error('Stripe payment error:', error);
        const displayError = document.getElementById('card-errors');
        displayError.textContent = 'An error occurred. Please try again.';
        displayError.style.display = 'block';
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}