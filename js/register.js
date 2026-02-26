/* REGISTER PAGE JAVASCRIPT */

// Get form element
const form = document.getElementById('registerForm');
const passwordInput = document.getElementById('password');
const togglePassword = document.getElementById('togglePassword');

/**
 * Display error message for a form field
 * @param {string} id - Field ID
 * @param {string} msg - Error message
 */
function showError(id, msg) {
    const errorElement = document.getElementById(id + '_error');
    if (errorElement) {
        errorElement.textContent = msg;
    }
}

/**
 * Clear error message for a form field
 * @param {string} id - Field ID
 */
function clearError(id) {
    const errorElement = document.getElementById(id + '_error');
    if (errorElement) {
        errorElement.textContent = '';
    }
}

/**
 * Toggle password visibility
 */
if (togglePassword && passwordInput) {
    togglePassword.addEventListener('click', function(e) {
        e.preventDefault();
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        
        // Toggle icon
        this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
    });
}

/**
 * Form submission validation
 * Validates all required fields before form submission
 */
if (form) {
    form.addEventListener('submit', function (e) {
        let ok = true;

        // First name validation
        if (!form.first_name.value.trim()) {
            showError('first_name', 'First name is required');
            ok = false;
        } else clearError('first_name');

        // Last name validation
        if (!form.last_name.value.trim()) {
            showError('last_name', 'Last name is required');
            ok = false;
        } else clearError('last_name');

        // Email validation
        const email = form.email.value.trim();
        if (!email) {
            showError('email', 'Email is required');
            ok = false;
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            showError('email', 'Enter a valid email');
            ok = false;
        } else clearError('email');

        // Phone validation
        const phone = form.phone.value.trim();
        if (!phone) {
            showError('phone', 'Phone is required');
            ok = false;
        } else if (!/^[0-9]{10,15}$/.test(phone)) {
            showError('phone', '10-15 digits only');
            ok = false;
        } else clearError('phone');

        // Password validation
        if (form.password.value.length < 6) {
            showError('password', 'At least 6 characters');
            ok = false;
        } else clearError('password');

        // Terms checkbox validation
        const termsCheckbox = document.getElementById('terms');
        if (termsCheckbox && !termsCheckbox.checked) {
            alert('Please agree to the terms and conditions');
            ok = false;
        }

        // Prevent form submission if validation fails
        if (!ok) e.preventDefault();
    });
}