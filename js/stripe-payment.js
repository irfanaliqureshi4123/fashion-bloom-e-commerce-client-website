/**
 * Stripe Payment Page JavaScript
 * Handles Stripe card element, payment submission, and payment processing
 */

const stripePublicKey = window.STRIPE_PUBLIC_KEY;
const orderId = window.ORDER_ID;
const orderTotal = window.ORDER_TOTAL;

let stripe, elements, cardElement;

/**
 * Initialize Stripe and card element on page load
 */
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

/**
 * Submit payment and process Stripe payment
 */
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

/**
 * Display success message toast notification
 * @param {string} message - Message to display
 */
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