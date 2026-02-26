// Product.js
// Product categories and their data
const productCategories = {
    bracelets: [
        { id: 1, name: "Premium Gold Bracelet", price: 12500, image: "/assets/images/products/bracelets/bracelet_1.jpg" },
        { id: 2, name: "Silver Chain Bracelet", price: 4500, image: "/assets/images/products/bracelets/bracelet_2.jpg" },
        { id: 3, name: "Diamond Tennis Bracelet", price: 28000, image: "/assets/images/products/bracelets/bracelet_3.jpg" },
        { id: 4, name: "Platinum Bracelet", price: 35000, image: "/assets/images/products/bracelets/bracelet_4.jpg" },
        { id: 5, name: "Gold Bracelet", price: 15000, image: "/assets/images/products/bracelets/bracelet_5.jpg" },
        { id: 6, name: "Diamond Bracelet", price: 45000, image: "/assets/images/products/bracelets/bracelet_6.jpg" },
        { id: 7, name: "Diamond Bracelet", price: 55000, image: "/assets/images/products/bracelets/bracelet_7.jpg" },
        { id: 8, name: "Diamond Bracelet", price: 75000, image: "/assets/images/products/bracelets/bracelet_8.jpg" },

    ],
    digital_watches: [
        { id: 101, name: "Smart Digital Watch", price: 8500, image: "/assets/images/products/digital_watches/digital_watch_1.jpg" },
        { id: 102, name: "Sports Digital Watch", price: 4200, image: "/assets/images/products/digital_watches/digital_watch_2.jpg" },
        { id: 103, name: "LED Digital Watch", price: 2500, image: "/assets/images/products/digital_watches/digital_watch_3.jpg" },
        { id: 104, name: "Retro Digital Watch", price: 5500, image: "/assets/images/products/digital_watches/digital_watch_4.jpg" },
        { id: 105, name: "Retro Digital Watch", price: 6500, image: "/assets/images/products/digital_watches/digital_watch_5.png" },
        { id: 106, name: "Retro Digital Watch", price: 8000, image: "/assets/images/products/digital_watches/digital_watch_6.jpg" },
        { id: 107, name: "Retro Digital Watch", price: 9500, image: "/assets/images/products/digital_watches/digital_watch_7.jpg" },
        { id: 108, name: "Retro Digital Watch", price: 11500, image: "/assets/images/products/digital_watches/digital_watch_8.png" },

    ],
    normal_watches: [
        { id: 201, name: "Classic Analog Watch", price: 7500, image: "/assets/images/products/normal_watches/normal_watch_1.jpg" },
        { id: 202, name: "Luxury Business Watch", price: 18000, image: "/assets/images/products/normal_watches/normal_watch_2.jpg" },
        { id: 203, name: "Casual Day Watch", price: 5500, image: "/assets/images/products/normal_watches/normal_watch_3.png" },
        { id: 204, name: "Luxury Day Watch", price: 9500, image: "/assets/images/products/normal_watches/normal_watch_4.jpg" },
        { id: 205, name: "Luxury Day Watch", price: 12000, image: "/assets/images/products/normal_watches/normal_watch_5.png" },
        { id: 206, name: "Luxury Day Watch", price: 15000, image: "/assets/images/products/normal_watches/normal_watch_6.png" },
        { id: 207, name: "Luxury Day Watch", price: 18500, image: "/assets/images/products/normal_watches/normal_watch_7.png" },
        { id: 208, name: "Luxury Day Watch", price: 22000, image: "/assets/images/products/normal_watches/normal_watch_8.jpg" },
    ],
    gold_chains: [
        { id: 301, name: "18K Gold Chain", price: 32000, image: "/assets/images/products/gold_chains/gold_chain_1.jpg" },
        { id: 302, name: "Gold Rope Chain", price: 26500, image: "/assets/images/products/gold_chains/gold_chain_2.jpg" },
        { id: 303, name: "Cuban Link Gold Chain", price: 42000, image: "/assets/images/products/gold_chains/gold_chain_3.jpg" },
        { id: 304, name: "Gold Chain", price: 18500, image: "/assets/images/products/gold_chains/gold_chain_4.jpg" },
        { id: 305, name: "Gold Chain", price: 22000, image: "/assets/images/products/gold_chains/gold_chain_5.jpg" },
        { id: 306, name: "Gold Chain", price: 28000, image: "/assets/images/products/gold_chains/gold_chain_6.jpg" },
        { id: 307, name: "Gold Chain", price: 35000, image: "/assets/images/products/gold_chains/gold_chain_7.jpg" },
        { id: 308, name: "Gold Chain", price: 45000, image: "/assets/images/products/gold_chains/gold_chain_8.jpg" },
    ],
    silver_chains: [
        { id: 401, name: "Sterling Silver Chain", price: 6500, image: "/assets/images/products/silver_chains/silver_chain_1.jpg" },
        { id: 402, name: "Silver Box Chain", price: 4500, image: "/assets/images/products/silver_chains/silver_chain_2.jpg" },
        { id: 403, name: "Silver Snake Chain", price: 5500, image: "/assets/images/products/silver_chains/silver_chain_3.jpg" },
        { id: 404, name: "Silver Chain", price: 8000, image: "/assets/images/products/silver_chains/silver_chain_4.jpg" },
        { id: 405, name: "Silver Chain", price: 10000, image: "/assets/images/products/silver_chains/silver_chain_5.jpg" },
        { id: 406, name: "Silver Chain", price: 12500, image: "/assets/images/products/silver_chains/silver_chain_6.jpg" },
        { id: 407, name: "Silver Chain", price: 15000, image: "/assets/images/products/silver_chains/silver_chain_7.jpg" },
        { id: 408, name: "Silver Chain", price: 18500, image: "/assets/images/products/silver_chains/silver_chain_8.jpg" },
    ]
};

// Shopping cart
let cart = [];

// Initialize the page
document.addEventListener('DOMContentLoaded', function () {
    displayAllProducts();
    setupCategoryFilter();
    setupCartFunctionality();
    loadCartFromServer();
});

// Display all products
function displayAllProducts() {
    const productsGrid = document.getElementById('products-grid');

    if (!productsGrid) {
        console.error('products-grid element not found!');
        return;
    }

    let allProducts = [];

    // Combine all products from different categories
    Object.keys(productCategories).forEach(category => {
        allProducts = allProducts.concat(productCategories[category].map(product => ({
            ...product,
            category: category
        })));
    });

    const html = allProducts.map(product => createProductCard(product)).join('');
    productsGrid.innerHTML = html;
}

// Display products by category
function displayProductsByCategory(category) {
    const productsGrid = document.getElementById('products-grid');

    if (category === 'all') {
        displayAllProducts();
        return;
    }

    const products = productCategories[category] || [];
    const html = products.map(product => createProductCard({ ...product, category })).join('');
    productsGrid.innerHTML = html;
}

// Create product card HTML
function createProductCard(product) {
    return `
        <div class="product-card" data-category="${product.category}" data-product-id="${product.id}">
            <div class="product-img">
                <img src="${product.image}" alt="${product.name}" onerror="this.src='assets/images/placeholder.jpg'">
                <div class="product-overlay">
                    <button class="quick-view-btn" onclick="quickView(${product.id})">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <button class="wishlist-btn" onclick="toggleWishlist(${product.id}, '${product.category}', '${product.name.replace(/'/g, "\\'")}', ${product.price}, '${product.image.replace(/'/g, "\\'")}')">
                    <i class="far fa-heart"></i>
                </button>
            </div>
            <div class="product-info">
                <h3 class="product-name">${product.name}</h3>
                <p class="product-category">${formatCategoryName(product.category)}</p>
                <div class="product-price">PKR ${product.price.toLocaleString('en-PK')}</div>
                <div class="quantity-addcart-container">
                    <label for="quantity-${product.id}" class="quantity-label">Quantity:</label>
                    <input type="number" id="quantity-${product.id}" name="quantity-${product.id}" value="1" min="1" class="quantity-input">
                    <button class="add-to-cart-btn" onclick="addToCart(${product.id}, '${product.category}')">
                        <i class="fas fa-shopping-cart"></i> Add to Cart
                    </button>
                </div>
            </div>
        </div>
    `;
}

// Format category name for display
function formatCategoryName(category) {
    return category.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
}

// Setup category filter functionality
function setupCategoryFilter() {
    const filterBtns = document.querySelectorAll('.filter-btn');

    filterBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            // Remove active class from all buttons
            filterBtns.forEach(b => b.classList.remove('active'));
            // Add active class to clicked button
            this.classList.add('active');

            // Filter products
            const category = this.getAttribute('data-category');
            displayProductsByCategory(category);
        });
    });
}

// Add to cart functionality
function addToCart(productId, category) {
    const quantityInput = document.getElementById(`quantity-${productId}`);
    let quantity = 1;
    if (quantityInput) {
        quantity = parseInt(quantityInput.value);
        if (isNaN(quantity) || quantity < 1) {
            quantity = 1;
        }
    }

    // Try to find product in productCategories (for hardcoded products)
    let product = null;
    if (productCategories[category]) {
        product = productCategories[category].find(p => p.id === productId);
    }

    // If not found, try to get from the product card data attributes
    if (!product) {
        const productCard = document.querySelector(`[data-product-id="${productId}"]`);
        if (productCard) {
            const nameEl = productCard.querySelector('.product-name');
            const priceEl = productCard.querySelector('.product-price');
            const imageEl = productCard.querySelector('img');

            product = {
                id: productId,
                name: nameEl ? nameEl.textContent : 'Product',
                price: priceEl ? parseInt(priceEl.textContent.replace(/\D/g, '')) : 0,
                image: imageEl ? imageEl.src : '/assets/images/placeholder.jpg'
            };
        }
    }

    if (!product) {
        console.error('Product not found');
        return;
    }

    // Send to server
    const formData = new FormData();
    formData.append('action', 'add');
    formData.append('product_id', productId);
    formData.append('category', category);
    formData.append('product_name', product.name);
    formData.append('product_price', product.price);
    formData.append('product_image', product.image);
    formData.append('quantity', quantity);

    fetch('includes/cart.php', {
        method: 'POST',
        body: formData
    })
        .then(response => {
            // Check if redirected (user not logged in)
            if (response.redirected) {
                window.location.href = response.url;
                return;
            }
            return response.json();
        })
        .then(data => {
            if (data && data.success) {
                // Add to local cart for UI
                const existingItem = cart.find(item => item.id === productId);
                if (existingItem) {
                    existingItem.quantity += quantity;
                } else {
                    cart.push({
                        ...product,
                        quantity: quantity,
                        category: category
                    });
                }
                updateCartUI();
                updateNavbarCartCount();
                fetchCartCountFromServer(); // Also fetch from server to ensure sync
                showAddToCartNotification(product.name);
            } else if (data && data.message) {
                alert(data.message || 'Failed to add item to cart');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error adding item to cart');
        });
}

// Setup cart functionality
function setupCartFunctionality() {
    const closeCartBtn = document.getElementById('close-cart');
    const overlay = document.getElementById('overlay');

    // Cart close button
    if (closeCartBtn) {
        closeCartBtn.addEventListener('click', () => closeCart());
    }

    // Overlay for menu (not cart overlay)
    if (overlay) {
        overlay.addEventListener('click', () => {
            closeCart();
            closeModal();
        });
    }
}

// Update cart UI
function updateCartUI() {
    const cartItems = document.getElementById('cart-items');
    const cartTotal = document.getElementById('cart-total');

    if (cart.length === 0) {
        cartItems.innerHTML = `
            <div class="cart-empty">
                <i class="fas fa-shopping-cart"></i>
                <p>Your cart is empty</p>
                <p style="font-size: 0.85rem; color: var(--text-light);">Add items to get started</p>
            </div>
        `;
        cartTotal.textContent = '0.00';
        return;
    }

    const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);

    cartItems.innerHTML = cart.map(item => `
        <div class="cart-item">
            <div class="cart-item-image">
                <img src="${item.image}" alt="${item.name}">
            </div>
            <div class="cart-item-content">
                <div class="cart-item-header">
                    <h4 class="cart-item-name">${item.name}</h4>
                    <p class="cart-item-price">PKR ${item.price.toLocaleString('en-PK')}</p>
                </div>
                <div class="cart-item-footer">
                    <div class="quantity-control">
                        <button class="qty-btn" onclick="updateQuantity(${item.id}, ${item.quantity - 1})" title="Decrease quantity">−</button>
                        <span class="qty-display">${item.quantity}</span>
                        <button class="qty-btn" onclick="updateQuantity(${item.id}, ${item.quantity + 1})" title="Increase quantity">+</button>
                    </div>
                    <button onclick="removeFromCart(${item.id})" class="cart-item-delete" title="Remove from cart">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `).join('');

    cartTotal.textContent = total.toLocaleString('en-PK');
}

// Update navbar cart count
function updateNavbarCartCount() {
    const cartCountElement = document.querySelector('.cart-count');
    if (cartCountElement) {
        const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
        cartCountElement.textContent = totalItems.toString();
    }
}

// Fetch cart count from server
function fetchCartCountFromServer() {
    const formData = new FormData();
    formData.append('action', 'count');

    fetch('includes/cart.php', {
        method: 'POST',
        body: formData
    })
        .then(response => {
            if (response.redirected) {
                return;
            }
            return response.json();
        })
        .then(data => {
            if (data && data.success) {
                const cartCountElement = document.querySelector('.cart-count');
                if (cartCountElement) {
                    cartCountElement.textContent = data.count.toString();
                }
            }
        })
        .catch(error => console.error('Error fetching cart count:', error));
}

// Update quantity
function updateQuantity(productId, newQuantity) {
    if (newQuantity <= 0) {
        removeFromCart(productId);
        return;
    }

    // Send to server
    const formData = new FormData();
    formData.append('action', 'update');
    formData.append('product_id', productId);
    formData.append('quantity', newQuantity);

    fetch('includes/cart.php', {
        method: 'POST',
        body: formData
    })
        .then(response => {
            // Check if redirected (user not logged in)
            if (response.redirected) {
                window.location.href = response.url;
                return;
            }
            return response.json();
        })
        .then(data => {
            if (data && data.success) {
                const item = cart.find(item => item.id === productId);
                if (item) {
                    item.quantity = newQuantity;
                    updateCartUI();
                    updateNavbarCartCount();
                    fetchCartCountFromServer();
                }
            }
        })
        .catch(error => console.error('Error:', error));
}

// Remove from cart
function removeFromCart(productId) {
    // Send to server
    const formData = new FormData();
    formData.append('action', 'remove');
    formData.append('product_id', productId);

    fetch('includes/cart.php', {
        method: 'POST',
        body: formData
    })
        .then(response => {
            // Check if redirected (user not logged in)
            if (response.redirected) {
                window.location.href = response.url;
                return;
            }
            return response.json();
        })
        .then(data => {
            if (data && data.success) {
                cart = cart.filter(item => item.id !== productId);
                updateCartUI();
                updateNavbarCartCount();
                fetchCartCountFromServer();
            }
        })
        .catch(error => console.error('Error:', error));
}

// Show add to cart notification
function showAddToCartNotification(productName) {
    // Create toast notification
    const toast = document.createElement('div');
    toast.className = 'toast-notification';
    toast.innerHTML = `
        <div class="toast-content">
            <i class="fas fa-check-circle"></i>
            <span>${productName} added to cart!</span>
        </div>
    `;

    document.body.appendChild(toast);

    // Trigger animation
    setTimeout(() => toast.classList.add('show'), 10);

    // Remove after 3 seconds
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Load cart from server
function loadCartFromServer() {
    const formData = new FormData();
    formData.append('action', 'get');

    fetch('includes/cart.php', {
        method: 'POST',
        body: formData
    })
        .then(response => {
            // Check if redirected (response URL changed)
            if (response.redirected) {
                window.location.href = response.url;
                return;
            }
            return response.json();
        })
        .then(data => {
            if (data && data.success && data.items) {
                // Rebuild cart array from server data
                cart = data.items.map(item => ({
                    id: item.product_id,
                    name: item.product_name,
                    price: parseFloat(item.product_price),
                    image: item.product_image,
                    quantity: parseInt(item.quantity),
                    category: item.category
                }));
                updateCartUI();
                updateNavbarCartCount();
            }
        })
        .catch(error => console.error('Error loading cart:', error));
}

// Quick view functionality - Navigate to product detail page
function quickView(productId) {
    // Redirect to the product detail page instead of opening a modal
    window.location.href = `product-detail.php?id=${productId}`;
}

// Checkout functionality
function checkout() {
    if (cart.length === 0) {
        alert('Your cart is empty!');
        return;
    }

    const orderId = Math.random().toString(36).substr(2, 9).toUpperCase();
    document.getElementById('order-id').textContent = orderId;

    // Clear cart on server
    const formData = new FormData();
    formData.append('action', 'clear');

    fetch('includes/cart.php', {
        method: 'POST',
        body: formData
    })
        .then(response => {
            // Check if redirected (user not logged in)
            if (response.redirected) {
                window.location.href = response.url;
                return;
            }
            return response.json();
        })
        .then(data => {
            if (data && data.success) {
                // Clear local cart
                cart = [];
                updateCartUI();
                updateNavbarCartCount();
                fetchCartCountFromServer();
                closeCart();

                // Show success modal
                document.getElementById('checkout-modal').classList.add('active');
                document.getElementById('overlay').classList.add('active');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error processing checkout');
        });
}

// Close modal
function closeModal() {
    document.getElementById('checkout-modal').classList.remove('active');
    document.getElementById('overlay').classList.remove('active');
}

// FAQ initialization
function initFAQ() {
    const faqItems = document.querySelectorAll('.faq-item');
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        question.addEventListener('click', () => {
            item.classList.toggle('active');
        });
    });
}

// Initialize on DOMContentLoaded
document.addEventListener('DOMContentLoaded', () => {
    initFAQ();
    checkUserLoggedIn();
});

// Check if user is logged in
function checkUserLoggedIn() {
    fetch('includes/check_session.php')
        .then(response => response.json())
        .then(data => {
            window.userLoggedIn = data.logged_in;
            if (data.logged_in) {
                window.userId = data.user_id;
                updateWishlistUI();
            }
            // Update wishlist counter for both logged-in and guest users
            updateWishlistCounter();
        })
        .catch(error => {
            console.error('Error checking session:', error);
            window.userLoggedIn = false;
            // Still update counter for guests
            updateWishlistCounter();
        });
}

// Toggle wishlist
function toggleWishlist(productId, category, productName, productPrice, productImage) {
    const btn = document.querySelector(`[data-product-id="${productId}"] .wishlist-btn`);
    const isInWishlist = btn.classList.contains('active');

    const formData = new FormData();
    formData.append('action', isInWishlist ? 'remove' : 'add');
    formData.append('product_id', productId);
    formData.append('category', category);
    formData.append('product_name', productName);
    formData.append('product_price', productPrice);
    formData.append('product_image', productImage);

    fetch('includes/wishlist.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                btn.classList.toggle('active');
                if (isInWishlist) {
                    btn.innerHTML = '<i class="far fa-heart"></i>';
                } else {
                    btn.innerHTML = '<i class="fas fa-heart"></i>';
                }
                updateWishlistCounter();
                showNotification(data.message);
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating wishlist');
        });
}

// Update wishlist UI (check which items are in wishlist)
function updateWishlistUI() {
    fetch('includes/wishlist.php?action=get')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.items) {
                data.items.forEach(item => {
                    const btn = document.querySelector(`[data-product-id="${item.product_id}"] .wishlist-btn`);
                    if (btn) {
                        btn.classList.add('active');
                        btn.innerHTML = '<i class="fas fa-heart"></i>';
                    }
                });
                updateWishlistCounter();
            }
        });
}

// Update wishlist counter in header
function updateWishlistCounter() {
    fetch('includes/wishlist.php?action=count')
        .then(response => response.json())
        .then(data => {
            const badge = document.querySelector('.wishlist-count');
            if (badge && data.count > 0) {
                badge.textContent = data.count;
                badge.style.display = 'flex';
            } else if (badge) {
                badge.style.display = 'none';
            }
        });
}

// Show notification
function showNotification(message) {
    const toast = document.createElement('div');
    toast.className = 'toast-notification';
    toast.innerHTML = `
        <div class="toast-content">
            <i class="fas fa-check-circle"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(toast);
    setTimeout(() => toast.classList.add('show'), 10);
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}