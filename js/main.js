// main.js - Navigation and UI utilities (main cart logic is in product.js)

// Initialize Navigation only if elements exist
document.addEventListener('DOMContentLoaded', function() {
    initializeNavigation();
    initializeCartOverlay();
    // Load cart count from server on page load
    if (typeof fetchCartCountFromServer === 'function') {
        fetchCartCountFromServer();
    }
});

function initializeNavigation() {
    const hamburger = document.getElementById('hamburger');
    const navMenu = document.getElementById('nav-menu');
    const overlay = document.getElementById('overlay');
    const closeCartBtn = document.getElementById('close-cart');
    const cartSidebar = document.getElementById('cart-sidebar');

    // Hamburger menu toggle
    if (hamburger) {
        hamburger.addEventListener('click', function() {
            hamburger.classList.toggle('active');
            if (navMenu) navMenu.classList.toggle('active');
            if (overlay) overlay.classList.toggle('active');
        });
    }

    // Overlay click to close menu
    if (overlay) {
        overlay.addEventListener('click', function(e) {
            // Check if click is from cart overlay, if so skip
            const cartOverlay = document.getElementById('cart-overlay');
            if (cartOverlay && cartOverlay.classList.contains('active')) {
                return;
            }
            
            if (hamburger) hamburger.classList.remove('active');
            if (navMenu) navMenu.classList.remove('active');
            overlay.classList.remove('active');
        });
    }

    // Cart close button
    if (closeCartBtn) {
        closeCartBtn.addEventListener('click', function() {
            if (cartSidebar) cartSidebar.classList.remove('active');
            const cartOverlay = document.getElementById('cart-overlay');
            if (cartOverlay) cartOverlay.classList.remove('active');
        });
    }

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            // Only prevent default if href is a valid selector (not just "#")
            if (href && href.length > 1) {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                    if (hamburger) hamburger.classList.remove('active');
                    if (navMenu) navMenu.classList.remove('active');
                    if (overlay) overlay.classList.remove('active');
                }
            }
        });
    });
}

// Initialize cart overlay close functionality
function initializeCartOverlay() {
    const cartOverlay = document.getElementById('cart-overlay');
    const cartSidebar = document.getElementById('cart-sidebar');
    
    if (cartOverlay) {
        cartOverlay.addEventListener('click', function() {
            if (cartSidebar) cartSidebar.classList.remove('active');
            cartOverlay.classList.remove('active');
            document.body.style.overflow = 'auto';
        });
    }
}