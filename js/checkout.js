/**
 * Checkout Page - JavaScript Functions
 * Handles shipping form submission and order processing
 */

/**
 * Scroll to payment section smoothly
 * Used for smooth navigation on the page
 */
function scrollToPayment() {
    const paymentSection = document.querySelector('.payment-methods').closest('.form-card');
    paymentSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

/**
 * Continue to payment page
 * Validates shipping form and saves shipping info to session
 * Then redirects to payment page
 */
function continueToPayment() {
    // ============================================================
    // STEP 1: Get form data
    // ============================================================
    const formData = new FormData(document.getElementById('checkout-form'));
    const first_name = formData.get('first_name');
    const last_name = formData.get('last_name');
    const email = formData.get('email');
    const phone = formData.get('phone');
    const address = formData.get('address');
    const city = formData.get('city');
    const postal = formData.get('postal');
    
    // ============================================================
    // STEP 2: Validate all required fields
    // ============================================================
    if (!first_name || !last_name || !email || !phone || !address || !city || !postal) {
        alert('Please fill in all required fields');
        return;
    }
    
    // ============================================================
    // STEP 3: Send shipping info to server
    // ============================================================
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
            // ========================================================
            // STEP 4: Redirect to payment page if successful
            // ========================================================
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

/**
 * Place order
 * Validates all form data and sends order to server
 * Creates order and redirects to confirmation page
 */
function placeOrder() {
    // ============================================================
    // STEP 1: Get form data
    // ============================================================
    const formData = new FormData(document.getElementById('checkout-form'));
    const paymentMethod = document.querySelector('input[name="payment"]:checked').value;
    
    // ============================================================
    // STEP 2: Prepare order data
    // ============================================================
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

    // ============================================================
    // STEP 3: Validate all required fields
    // ============================================================
    if (!orderData.first_name || !orderData.last_name || !orderData.email || !orderData.phone || !orderData.address || !orderData.city || !orderData.postal) {
        alert('Please fill in all required fields');
        return;
    }

    // ============================================================
    // STEP 4: Disable button to prevent double submission
    // ============================================================
    const btn = event.target;
    btn.disabled = true;
    btn.textContent = 'Processing...';

    // ============================================================
    // STEP 5: Send order to server
    // ============================================================
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
            // ====================================================
            // STEP 6: Order successful - show confirmation
            // ====================================================
            alert('Order placed successfully! Order ID: ' + data.order_id);
            window.location.href = 'order-confirmation.php?order_id=' + data.order_id;
        } else {
            // ====================================================
            // STEP 7: Order failed - re-enable button
            // ====================================================
            alert('Error: ' + (data.message || 'Failed to place order'));
            btn.disabled = false;
            btn.textContent = 'Place Order';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error processing order. Please check the console for details.');
        btn.disabled = false;
        btn.textContent = 'Place Order';
    });
}
