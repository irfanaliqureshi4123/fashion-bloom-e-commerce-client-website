/**
 * Shopping Cart - JavaScript Functions
 * Handles cart operations: update quantity, remove items, checkout
 */

/**
 * Proceed to checkout page
 * Redirects user to the checkout process
 */
function proceedToCheckout() {
    window.location.href = 'checkout.php';
}

/**
 * Update cart item quantity
 * Sends updated quantity to server and reloads cart
 * 
 * @param {number} productId - The product ID to update
 * @param {number} quantity - The new quantity
 */
function updateCartItem(productId, quantity) {
    const formData = new FormData();
    formData.append('action', 'update');
    formData.append('product_id', productId);
    formData.append('quantity', quantity);

    fetch('includes/cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error updating cart:', error);
    });
}

/**
 * Remove item from cart
 * Shows confirmation dialog before removing
 * 
 * @param {number} productId - The product ID to remove
 */
function removeCartItem(productId) {
    if (confirm('Remove this item from cart?')) {
        const formData = new FormData();
        formData.append('action', 'remove');
        formData.append('product_id', productId);

        fetch('includes/cart.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error removing item:', error);
        });
    }
}

/**
 * Initialize cart functionality
 * Attach event listeners to quantity and remove buttons
 */
function initializeCart() {
    // Handle quantity increase button clicks
    document.querySelectorAll('.qty-plus').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.dataset.productId;
            const input = document.querySelector(`.qty-input[data-product-id="${productId}"]`);
            input.value = parseInt(input.value) + 1;
            updateCartItem(productId, parseInt(input.value));
        });
    });

    // Handle quantity decrease button clicks
    document.querySelectorAll('.qty-minus').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.dataset.productId;
            const input = document.querySelector(`.qty-input[data-product-id="${productId}"]`);
            if (parseInt(input.value) > 1) {
                input.value = parseInt(input.value) - 1;
                updateCartItem(productId, parseInt(input.value));
            }
        });
    });

    // Handle manual quantity input changes
    document.querySelectorAll('.qty-input').forEach(input => {
        input.addEventListener('change', function() {
            const productId = this.dataset.productId;
            const quantity = parseInt(this.value);
            if (quantity > 0) {
                updateCartItem(productId, quantity);
            }
        });
    });

    // Handle remove item button clicks
    document.querySelectorAll('.remove-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.dataset.productId;
            removeCartItem(productId);
        });
    });
}

/**
 * Initialize cart when DOM is ready
 */
document.addEventListener('DOMContentLoaded', function() {
    initializeCart();
});
