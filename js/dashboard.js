/**
 * Dashboard Page - JavaScript Functions
 * Handles responsive sidebar toggle and mobile navigation
 */

/**
 * Toggle sidebar visibility on mobile devices
 * Opens/closes sidebar and updates toggle button icon
 */
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const toggle = document.getElementById('sidebarToggle');
    
    if (sidebar) {
        sidebar.classList.toggle('active');
        
        // ============================================================
        // Update icon and position based on sidebar state
        // ============================================================
        if (sidebar.classList.contains('active')) {
            // Sidebar is now open
            toggle.innerHTML = '<i class="fas fa-times"></i>';
            toggle.style.left = 'auto';
            toggle.style.right = '15px';
        } else {
            // Sidebar is now closed
            toggle.innerHTML = '<i class="fas fa-bars"></i>';
            toggle.style.left = '15px';
            toggle.style.right = 'auto';
        }
    }
}

/**
 * Close sidebar when clicking outside on mobile devices
 * Prevents sidebar from staying open when clicking content area
 */
document.addEventListener('click', function(e) {
    const sidebar = document.querySelector('.sidebar');
    const toggle = document.getElementById('sidebarToggle');
    
    // Only apply on mobile (width < 768px)
    if (window.innerWidth < 768 && sidebar && sidebar.classList.contains('active')) {
        // Check if click is outside sidebar and toggle button
        if (!sidebar.contains(e.target) && !toggle.contains(e.target)) {
            // Close sidebar
            sidebar.classList.remove('active');
            toggle.innerHTML = '<i class="fas fa-bars"></i>';
            toggle.style.left = '15px';
            toggle.style.right = 'auto';
        }
    }
});

/**
 * Close sidebar when clicking a navigation link on mobile
 * Improves UX by closing sidebar after navigation
 */
document.querySelectorAll('.sidebar-nav-link').forEach(link => {
    link.addEventListener('click', function() {
        // Only apply on mobile (width < 768px)
        if (window.innerWidth < 768) {
            const sidebar = document.querySelector('.sidebar');
            const toggle = document.getElementById('sidebarToggle');
            
            // Close sidebar
            sidebar.classList.remove('active');
            toggle.innerHTML = '<i class="fas fa-bars"></i>';
            toggle.style.left = '15px';
            toggle.style.right = 'auto';
        }
    });
});

/**
 * Handle window resize events
 * Shows/hides toggle button based on screen width
 * Automatically closes sidebar on larger screens
 */
window.addEventListener('resize', function() {
    const sidebar = document.querySelector('.sidebar');
    const toggle = document.getElementById('sidebarToggle');
    
    if (window.innerWidth >= 768) {
        // Desktop size - close sidebar and hide toggle button
        sidebar.classList.remove('active');
        toggle.style.display = 'none';
    } else {
        // Mobile size - show toggle button
        toggle.style.display = 'block';
    }
});
