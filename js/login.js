/* LOGIN PAGE JAVASCRIPT */

// Get form element
const form = document.getElementById('loginForm');

/**
 * Display error message for a form field
 * @param {string} id - Field ID
 * @param {string} msg - Error message
 */
function showError(id, msg) {
    document.getElementById(id + '_error').textContent = msg;
}

/**
 * Clear error message for a form field
 * @param {string} id - Field ID
 */
function clearError(id) {
    document.getElementById(id + '_error').textContent = '';
}

/**
 * Form submission validation
 * Validates email and password fields before submission
 */
form.addEventListener('submit', function (e) {
    let ok = true;

    const email = form.email.value.trim();
    const password = form.password.value.trim();

    // Email validation
    if (!email) {
        showError('email', 'Email is required');
        ok = false;
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        showError('email', 'Enter a valid email');
        ok = false;
    } else clearError('email');

    // Password validation
    if (!password) {
        showError('password', 'Password is required');
        ok = false;
    } else clearError('password');

    // Prevent form submission if validation fails
    if (!ok) e.preventDefault();
});
