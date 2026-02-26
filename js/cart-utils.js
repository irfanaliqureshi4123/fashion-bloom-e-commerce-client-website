// Open cart sidebar
function openCart() {
    const cartSidebar = document.getElementById('cart-sidebar');
    const cartOverlay = document.getElementById('cart-overlay');
    if (cartSidebar) cartSidebar.classList.add('active');
    if (cartOverlay) cartOverlay.classList.add('active');
    document.body.style.overflow = 'hidden';
    
    // Refresh cart display when opened
    loadAndDisplayCart();
}

// Close cart sidebar
function closeCart() {
    const cartSidebar = document.getElementById('cart-sidebar');
    const cartOverlay = document.getElementById('cart-overlay');
    if (cartSidebar) cartSidebar.classList.remove('active');
    if (cartOverlay) cartOverlay.classList.remove('active');
    document.body.style.overflow = 'auto';
}

// Update navbar cart count
function updateNavbarCartCount() {
    const cartCountElement = document.querySelector('.cart-count');
    if (cartCountElement) {
        if (typeof cart !== 'undefined' && Array.isArray(cart)) {
            const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
            cartCountElement.textContent = totalItems.toString();
        }
    }
}

// Fetch cart count from server
function fetchCartCountFromServer() {
    const formData = new FormData();
    formData.append('action', 'get');
    
    fetch('/includes/cart.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
    .then(response => {
        if (response.status === 401) {
            // User not logged in
            const cartCountElement = document.querySelector('.cart-count');
            if (cartCountElement) {
                cartCountElement.textContent = '0';
            }
            return null;
        }
        return response.json();
    })
    .then(data => {
        const cartCountElement = document.querySelector('.cart-count');
        if (!cartCountElement) return;
        
        if (data && data.success && data.items && Array.isArray(data.items)) {
            // Sum up all quantities
            const totalQuantity = data.items.reduce((sum, item) => {
                return sum + parseInt(item.quantity || 0);
            }, 0);
            cartCountElement.textContent = totalQuantity.toString();
        } else {
            cartCountElement.textContent = '0';
        }
    })
    .catch(error => {
        console.error('Error fetching cart count:', error);
        const cartCountElement = document.querySelector('.cart-count');
        if (cartCountElement) {
            cartCountElement.textContent = '0';
        }
    });
}

// Show notification/toast
function showNotification(message) {
    const toast = document.createElement('div');
    toast.className = 'toast-notification show';
    toast.innerHTML = `
        <div class="toast-content">
            <i class="fas fa-check-circle"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Checkout function for pages without product.js
function checkout() {
    showNotification('Redirecting to checkout...');
    // Redirect to products page where full checkout is available
    setTimeout(() => {
        window.location.href = '/index.php#products';
    }, 1500);
}

// Load and display cart from server
function loadAndDisplayCart() {
    const formData = new FormData();
    formData.append('action', 'get');

    fetch('/includes/cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (response.redirected) {
            window.location.href = response.url;
            return null;
        }
        return response.json();
    })
    .then(data => {
        if (!data) return;
        
        const cartItems = document.getElementById('cart-items');
        const cartTotal = document.getElementById('cart-total');
        
        if (data && data.success && data.items && data.items.length > 0) {
            let total = 0;
            const itemsHTML = data.items.map(item => {
                const itemTotal = parseFloat(item.product_price) * parseInt(item.quantity);
                total += itemTotal;
                
                return `
                    <div class="cart-item">
                        <div class="cart-item-image">
                            <img src="${item.product_image}" alt="${item.product_name}">
                        </div>
                        <div class="cart-item-content">
                            <div class="cart-item-header">
                                <h4 class="cart-item-name">${item.product_name}</h4>
                                <p class="cart-item-price">PKR ${parseFloat(item.product_price).toLocaleString('en-PK')}</p>
                            </div>
                            <div class="cart-item-footer">
                                <div class="quantity-control">
                                    <button class="qty-btn" onclick="updateQuantityFromCart(${item.product_id}, ${parseInt(item.quantity) - 1})" title="Decrease quantity">−</button>
                                    <span class="qty-display">${item.quantity}</span>
                                    <button class="qty-btn" onclick="updateQuantityFromCart(${item.product_id}, ${parseInt(item.quantity) + 1})" title="Increase quantity">+</button>
                                </div>
                                <button onclick="removeFromCartSidebar(${item.product_id})" class="cart-item-delete" title="Remove from cart">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
            
            cartItems.innerHTML = itemsHTML;
            cartTotal.textContent = total.toLocaleString('en-PK');
            
            // Update navbar count
            fetchCartCountFromServer();
        } else {
            // Empty cart
            cartItems.innerHTML = `
                <div class="cart-empty">
                    <i class="fas fa-shopping-cart"></i>
                    <p>Your cart is empty</p>
                    <p style="font-size: 0.85rem; color: var(--text-light);">Add items to get started</p>
                </div>
            `;
            cartTotal.textContent = '0.00';
        }
    })
    .catch(error => {
        console.error('Error loading cart:', error);
        const cartItems = document.getElementById('cart-items');
        cartItems.innerHTML = `
            <div class="cart-empty">
                <i class="fas fa-exclamation-circle"></i>
                <p>Error loading cart</p>
                <p style="font-size: 0.85rem; color: var(--text-light);">Please try again</p>
            </div>
        `;
    });
}

// Update quantity from cart sidebar
function updateQuantityFromCart(productId, newQuantity) {
    if (newQuantity <= 0) {
        removeFromCartSidebar(productId);
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'update');
    formData.append('product_id', productId);
    formData.append('quantity', newQuantity);

    fetch('/includes/cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data && data.success) {
            loadAndDisplayCart();
        }
    })
    .catch(error => console.error('Error updating quantity:', error));
}

// Remove from cart sidebar
function removeFromCartSidebar(productId) {
    const formData = new FormData();
    formData.append('action', 'remove');
    formData.append('product_id', productId);

    fetch('/includes/cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data && data.success) {
            loadAndDisplayCart();
            showNotification('Item removed from cart');
        }
    })
    .catch(error => console.error('Error removing item:', error));
}
