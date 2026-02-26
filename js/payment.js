/**
 * Payment Page - JavaScript Functions
 * Handles payment method selection and cart sidebar functionality
 */

/**
 * Toggle cart sidebar visibility
 * Opens/closes the cart sidebar and overlay
 */
function toggleCartSidebar() {
    const sidebar = document.getElementById('cartSidebar');
    const overlay = document.getElementById('cartOverlay');
    sidebar.classList.toggle('active');
    overlay.classList.toggle('active');
}

/**
 * Initialize payment method listeners
 * Shows/hides card details based on selected payment method
 */
function initPaymentMethodListener() {
    const paymentRadios = document.querySelectorAll('input[name="payment_method"]');
    const cardDetails = document.getElementById('cardDetails');

    paymentRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'card') {
                // Show card details form
                cardDetails.style.display = 'block';
                
                // Set card fields as required
                setCardFieldsRequired(true);
            } else {
                // Hide card details form
                cardDetails.style.display = 'none';
                
                // Remove required attribute from card fields
                setCardFieldsRequired(false);
            }
        });
    });
}

/**
 * Set card input fields as required or optional
 * @param {boolean} isRequired - Whether card fields should be required
 */
function setCardFieldsRequired(isRequired) {
    document.getElementById('card_name').required = isRequired;
    document.getElementById('card_number').required = isRequired;
    document.getElementById('card_expiry').required = isRequired;
    document.getElementById('card_cvv').required = isRequired;
}

/**
 * Format card number with spaces
 * Automatically adds spaces after every 4 digits (1234 5678 9012 3456)
 */
function formatCardNumber() {
    const cardInput = document.getElementById('card_number');
    if (cardInput) {
        cardInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s/g, '');
            let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
            e.target.value = formattedValue;
        });
    }
}

/**
 * Format card expiry date
 * Automatically formats to MM/YY format
 */
function formatCardExpiry() {
    const expiryInput = document.getElementById('card_expiry');
    if (expiryInput) {
        expiryInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.slice(0, 2) + '/' + value.slice(2, 4);
            }
            e.target.value = value;
        });
    }
}

/**
 * Allow only numbers in CVV field
 */
function restrictCVVInput() {
    const cvvInput = document.getElementById('card_cvv');
    if (cvvInput) {
        cvvInput.addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '');
        });
    }
}

/**
 * Initialize all payment page functionality
 * Call this function on page load
 */
function initPaymentPage() {
    // Initialize payment method listeners
    initPaymentMethodListener();
    
    // Format card inputs
    formatCardNumber();
    formatCardExpiry();
    restrictCVVInput();
}

/**
 * Validate card number using Luhn algorithm
 * @param {string} cardNumber - The card number to validate
 * @returns {boolean} - True if valid, false otherwise
 */
function validateCardNumber(cardNumber) {
    const digits = cardNumber.replace(/\D/g, '');
    if (digits.length < 13 || digits.length > 19) {
        return false;
    }

    let sum = 0;
    let isEven = false;

    for (let i = digits.length - 1; i >= 0; i--) {
        let digit = parseInt(digits[i], 10);

        if (isEven) {
            digit *= 2;
            if (digit > 9) {
                digit -= 9;
            }
        }

        sum += digit;
        isEven = !isEven;
    }

    return sum % 10 === 0;
}

/**
 * Validate expiry date format and expiration
 * @param {string} expiryDate - The expiry date in MM/YY format
 * @returns {boolean} - True if valid, false otherwise
 */
function validateExpiryDate(expiryDate) {
    const parts = expiryDate.split('/');
    if (parts.length !== 2) {
        return false;
    }

    const month = parseInt(parts[0], 10);
    const year = parseInt(parts[1], 10);

    if (month < 1 || month > 12) {
        return false;
    }

    const currentDate = new Date();
    const currentYear = currentDate.getFullYear() % 100;
    const currentMonth = currentDate.getMonth() + 1;

    if (year < currentYear || (year === currentYear && month < currentMonth)) {
        return false;
    }

    return true;
}

/**
 * Validate CVV (3 or 4 digits)
 * @param {string} cvv - The CVV to validate
 * @returns {boolean} - True if valid, false otherwise
 */
function validateCVV(cvv) {
    const cleanCVV = cvv.replace(/\D/g, '');
    return cleanCVV.length === 3 || cleanCVV.length === 4;
}

/**
 * Validate entire payment form
 * @returns {boolean} - True if form is valid, false otherwise
 */
function validatePaymentForm() {
    const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
    
    if (!paymentMethod) {
        alert('Please select a payment method');
        return false;
    }

    if (paymentMethod.value === 'card') {
        const cardName = document.getElementById('card_name').value.trim();
        const cardNumber = document.getElementById('card_number').value.trim();
        const expiryDate = document.getElementById('card_expiry').value.trim();
        const cvv = document.getElementById('card_cvv').value.trim();

        if (!cardName) {
            alert('Please enter cardholder name');
            return false;
        }

        if (!validateCardNumber(cardNumber)) {
            alert('Please enter a valid card number');
            return false;
        }

        if (!validateExpiryDate(expiryDate)) {
            alert('Please enter a valid expiry date (MM/YY)');
            return false;
        }

        if (!validateCVV(cvv)) {
            alert('Please enter a valid CVV (3 or 4 digits)');
            return false;
        }
    }

    return true;
}

// Initialize payment page when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    initPaymentPage();
    
    // Add form submission validation
    const paymentForm = document.getElementById('paymentForm');
    if (paymentForm) {
        paymentForm.addEventListener('submit', function(e) {
            if (!validatePaymentForm()) {
                e.preventDefault();
            }
        });
    }
});
