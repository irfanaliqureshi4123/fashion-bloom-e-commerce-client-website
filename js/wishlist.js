// Wishlist.js - Handle wishlist page functionality

document.addEventListener('DOMContentLoaded', function() {
    setupWishlistEventListeners();
});

function setupWishlistEventListeners() {
    // Add to cart from wishlist
    const addToCartBtns = document.querySelectorAll('.add-to-cart-from-wishlist');
    addToCartBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            const productName = this.getAttribute('data-product-name');
            const productPrice = this.getAttribute('data-product-price');
            const productImage = this.getAttribute('data-product-image');
            const category = this.getAttribute('data-category');

            addToCartFromWishlist(productId, productName, productPrice, productImage, category, this);
        });
    });

    // Remove from wishlist
    const removeFromWishlistBtns = document.querySelectorAll('.remove-from-wishlist');
    removeFromWishlistBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            removeFromWishlist(productId, this);
        });
    });
}

function addToCartFromWishlist(productId, productName, productPrice, productImage, category, button) {
    const formData = new FormData();
    formData.append('action', 'add');
    formData.append('product_id', productId);
    formData.append('product_name', productName);
    formData.append('product_price', productPrice);
    formData.append('product_image', productImage);
    formData.append('category', category);
    formData.append('quantity', 1);

    fetch('includes/cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Item added to cart!');
            // Optionally remove from wishlist after adding to cart
            // removeFromWishlist(productId, null);
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error adding item to cart');
    });
}

function removeFromWishlist(productId, button) {
    const formData = new FormData();
    formData.append('action', 'remove');
    formData.append('product_id', productId);

    fetch('includes/wishlist.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove the item from DOM
            const wishlistItem = document.querySelector(`[data-product-id="${productId}"]`);
            if (wishlistItem) {
                wishlistItem.style.transition = 'all 0.3s ease';
                wishlistItem.style.opacity = '0';
                wishlistItem.style.transform = 'translateX(-20px)';
                setTimeout(() => wishlistItem.remove(), 300);
            }
            showNotification('Item removed from wishlist');
            
            // Update wishlist count
            updateWishlistCount();
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error removing item from wishlist');
    });
}

function updateWishlistCount() {
    fetch('includes/wishlist.php?action=count')
        .then(response => response.json())
        .then(data => {
            const count = document.querySelector('.wishlist-count');
            if (count) {
                if (data.count > 0) {
                    count.textContent = data.count + ' items';
                } else {
                    // Redirect to empty state
                    location.reload();
                }
            }
        });
}

function showNotification(message) {
    const notification = document.createElement('div');
    notification.className = 'notification';
    notification.textContent = message;
    document.body.appendChild(notification);

    setTimeout(() => {
        notification.classList.add('show');
    }, 100);

    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}
