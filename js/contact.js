// Contact Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize contact page functionality
    initContactForm();
    initModal();
});

// Contact Form Functionality
function initContactForm() {
    const contactForm = document.getElementById('contactForm');
    const submitBtn = contactForm.querySelector('.submit-btn');
    const originalBtnText = submitBtn.innerHTML;

    contactForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Show loading state
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
        submitBtn.disabled = true;
        
        // Validate form
        if (validateForm()) {
            // Send form data via AJAX
            const formData = new FormData(contactForm);
            
            fetch(contactForm.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success modal
                    showModal(data.message);
                    
                    // Reset form
                    contactForm.reset();
                    clearErrorStates();
                } else {
                    // Show error message
                    showErrorNotification(data.message);
                }
            })
            .catch(error => {
                showErrorNotification('An error occurred. Please try again.');
            })
            .finally(() => {
                // Reset button
                submitBtn.innerHTML = originalBtnText;
                submitBtn.disabled = false;
            });
        } else {
            // Reset button if validation fails
            submitBtn.innerHTML = originalBtnText;
            submitBtn.disabled = false;
        }
    });
}

// Form Validation
function validateForm() {
    const form = document.getElementById('contactForm');
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    // Clear previous error states
    clearErrorStates();
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            showFieldError(field, 'This field is required');
            isValid = false;
        } else if (field.type === 'email' && !isValidEmail(field.value)) {
            showFieldError(field, 'Please enter a valid email address');
            isValid = false;
        }
    });
    
    // Validate phone number if provided
    const phoneField = document.getElementById('phone');
    if (phoneField.value.trim() && !isValidPhone(phoneField.value)) {
        showFieldError(phoneField, 'Please enter a valid phone number');
        isValid = false;
    }
    
    return isValid;
}

// Helper Functions
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function isValidPhone(phone) {
    const phoneRegex = /^[\+]?[0-9\s\-\(\)]{10,}$/;
    return phoneRegex.test(phone);
}

function showFieldError(field, message) {
    field.style.borderColor = '#e74c3c';
    
    // Remove existing error message
    const existingError = field.parentNode.querySelector('.error-message');
    if (existingError) {
        existingError.remove();
    }
    
    // Add error message
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.style.color = '#e74c3c';
    errorDiv.style.fontSize = '0.9rem';
    errorDiv.style.marginTop = '5px';
    errorDiv.textContent = message;
    
    field.parentNode.appendChild(errorDiv);
}

function clearErrorStates() {
    const form = document.getElementById('contactForm');
    const fields = form.querySelectorAll('input, select, textarea');
    const errorMessages = form.querySelectorAll('.error-message');
    
    fields.forEach(field => {
        field.style.borderColor = '#e9ecef';
    });
    
    errorMessages.forEach(error => {
        error.remove();
    });
}

// Modal Functionality
function initModal() {
    const modal = document.getElementById('successModal');
    const closeBtn = modal.querySelector('.modal-close');
    
    // Close modal when clicking the close button
    closeBtn.addEventListener('click', function() {
        hideModal();
    });
    
    // Close modal when clicking outside
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            hideModal();
        }
    });
    
    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.classList.contains('show')) {
            hideModal();
        }
    });
}

function showModal(message) {
    const modal = document.getElementById('successModal');
    
    // Update modal message if provided
    if (message) {
        const modalMessage = modal.querySelector('.modal-message');
        if (modalMessage) {
            modalMessage.textContent = message;
        }
    }
    
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
}

function hideModal() {
    const modal = document.getElementById('successModal');
    modal.classList.remove('show');
    document.body.style.overflow = 'auto';
}

// Show error notification
function showErrorNotification(message) {
    const notification = document.createElement('div');
    notification.className = 'error-notification';
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #ef4444;
        color: white;
        padding: 16px 24px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        z-index: 10000;
        max-width: 400px;
        animation: slideIn 0.3s ease-out;
    `;
    notification.innerHTML = `
        <div style="display: flex; align-items: center; gap: 12px;">
            <i class="fas fa-exclamation-circle"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}

// Auto-resize textarea
document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.getElementById('message');
    if (textarea) {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
    }
});

// Form field animations
document.addEventListener('DOMContentLoaded', function() {
    const formFields = document.querySelectorAll('.form-group input, .form-group select, .form-group textarea');
    
    formFields.forEach(field => {
        // Add focus animation
        field.addEventListener('focus', function() {
            this.parentNode.classList.add('focused');
        });
        
        // Remove focus animation if field is empty
        field.addEventListener('blur', function() {
            if (!this.value.trim()) {
                this.parentNode.classList.remove('focused');
            }
        });
        
        // Check if field has value on page load
        if (field.value.trim()) {
            field.parentNode.classList.add('focused');
        }
    });
});

// Smooth scrolling for anchor links
document.addEventListener('DOMContentLoaded', function() {
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    
    anchorLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId);
            
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
});

// Character counter for message field
document.addEventListener('DOMContentLoaded', function() {
    const messageField = document.getElementById('message');
    const maxLength = 1000;
    
    if (messageField) {
        // Create character counter
        const counterDiv = document.createElement('div');
        counterDiv.className = 'character-counter';
        counterDiv.style.textAlign = 'right';
        counterDiv.style.fontSize = '0.9rem';
        counterDiv.style.color = '#666';
        counterDiv.style.marginTop = '5px';
        
        messageField.parentNode.appendChild(counterDiv);
        
        // Update counter
        function updateCounter() {
            const currentLength = messageField.value.length;
            counterDiv.textContent = `${currentLength}/${maxLength} characters`;
            
            if (currentLength > maxLength * 0.9) {
                counterDiv.style.color = '#e74c3c';
            } else if (currentLength > maxLength * 0.7) {
                counterDiv.style.color = '#f39c12';
            } else {
                counterDiv.style.color = '#666';
            }
        }
        
        messageField.addEventListener('input', updateCounter);
        updateCounter(); // Initial update
    }
});

// Form auto-save (localStorage)
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('contactForm');
    const formFields = form.querySelectorAll('input, select, textarea');
    const storageKey = 'fashionbloom_contact_form';
    
    // Load saved data
    function loadFormData() {
        const savedData = localStorage.getItem(storageKey);
        if (savedData) {
            const formData = JSON.parse(savedData);
            
            formFields.forEach(field => {
                if (formData[field.name] && field.type !== 'checkbox') {
                    field.value = formData[field.name];
                } else if (field.type === 'checkbox' && formData[field.name]) {
                    field.checked = formData[field.name];
                }
            });
        }
    }
    
    // Save form data
    function saveFormData() {
        const formData = {};
        
        formFields.forEach(field => {
            if (field.type === 'checkbox') {
                formData[field.name] = field.checked;
            } else {
                formData[field.name] = field.value;
            }
        });
        
        localStorage.setItem(storageKey, JSON.stringify(formData));
    }
    
    // Clear saved data
    function clearFormData() {
        localStorage.removeItem(storageKey);
    }
    
    // Load data on page load
    loadFormData();
    
    // Save data on input
    formFields.forEach(field => {
        field.addEventListener('input', saveFormData);
        field.addEventListener('change', saveFormData);
    });
    
    // Clear data on successful submission
    form.addEventListener('submit', function() {
        setTimeout(clearFormData, 2500); // Clear after success modal
    });
});

// Add CSS for focused form groups
const style = document.createElement('style');
style.textContent = `
    .form-group.focused label {
        color: #d4af37;
        transform: translateY(-2px);
        transition: all 0.3s ease;
    }
    
    .character-counter {
        transition: color 0.3s ease;
    }
    
    .error-message {
        animation: slideInError 0.3s ease;
    }
    
    @keyframes slideInError {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
`;
document.head.appendChild(style);

