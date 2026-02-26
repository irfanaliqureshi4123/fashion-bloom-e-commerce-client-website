// Product Search and Filter Functionality
(function() {
    'use strict';
    
    let productsData = [];
    let productSearch = null;
    let categoryFilter = null;
    let stockFilter = null;
    let priceFilter = null;
    let productsGrid = null;
    let noResultsMessage = null;
    let resultsCount = null;

    /**
     * Initialize the product filter functionality
     */
    function init() {
        // Get product data from hidden script tag
        const productsDataElement = document.getElementById('productsData');
        if (!productsDataElement) {
            console.warn('Products data element not found');
            return false;
        }

        try {
            productsData = JSON.parse(productsDataElement.textContent);
        } catch (e) {
            console.error('Failed to parse products data:', e);
            return false;
        }

        // Get DOM elements
        productSearch = document.getElementById('productSearch');
        categoryFilter = document.getElementById('categoryFilter');
        stockFilter = document.getElementById('stockFilter');
        priceFilter = document.getElementById('priceFilter');
        productsGrid = document.getElementById('productsGrid');
        noResultsMessage = document.getElementById('noResultsMessage');
        resultsCount = document.getElementById('resultsCount');

        if (!productSearch || !categoryFilter || !stockFilter || !priceFilter || !productsGrid) {
            console.warn('Required filter elements not found');
            return false;
        }

        // Attach event listeners
        productSearch.addEventListener('input', filterProducts);
        categoryFilter.addEventListener('change', filterProducts);
        stockFilter.addEventListener('change', filterProducts);
        priceFilter.addEventListener('change', filterProducts);

        // Perform initial render
        renderProducts(productsData);

        return true;
    }

    /**
     * Escape HTML special characters
     */
    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.toString().replace(/[&<>"']/g, m => map[m]);
    }

    /**
     * Render products to the grid
     */
    function renderProducts(filteredProducts) {
        if (!productsGrid) return;

        productsGrid.innerHTML = '';
        
        if (!filteredProducts || filteredProducts.length === 0) {
            productsGrid.style.display = 'none';
            if (noResultsMessage) {
                noResultsMessage.style.display = 'block';
            }
            if (resultsCount) {
                resultsCount.innerHTML = 'Showing <strong>0</strong> products';
            }
            return;
        }

        productsGrid.style.display = 'grid';
        if (noResultsMessage) {
            noResultsMessage.style.display = 'none';
        }
        if (resultsCount) {
            resultsCount.innerHTML = `Showing <strong>${filteredProducts.length}</strong> products`;
        }

        filteredProducts.forEach(product => {
            const card = document.createElement('div');
            card.className = 'card';
            
            // Build image HTML
            let imageHtml = '';
            if (product.image_url) {
                imageHtml = `
                    <img src="${escapeHtml(product.image_url)}" alt="${escapeHtml(product.name)}" class="card-image" style="width: 100%; height: 250px; object-fit: cover;" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="card-image" style="display: none; align-items: center; justify-content: center; position: absolute; top: 0; left: 0; width: 100%; height: 250px; background: linear-gradient(135deg, #d4af37 0%, #b8941f 100%);">
                        <i class="fas fa-image" style="font-size: 3rem; color: rgba(255,255,255,0.5);"></i>
                    </div>
                `;
            } else {
                imageHtml = `
                    <div class="card-image" style="display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-image" style="font-size: 3rem; color: rgba(255,255,255,0.5);"></i>
                    </div>
                `;
            }
            
            card.innerHTML = `
                ${imageHtml}
                <div class="card-body">
                    <div class="card-category">${escapeHtml(product.category)}</div>
                    <h3 class="card-title">${escapeHtml(product.name)}</h3>
                    
                    <div class="card-meta">
                        <div class="card-price">PKR ${parseFloat(product.price).toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</div>
                        <div class="card-stock">Stock: ${product.stock_quantity}</div>
                    </div>
                    
                    <div class="card-actions">
                        <button class="btn btn-primary edit-product" style="flex: 1;">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <form method="POST" style="display:inline; flex: 1;">
                            <input type="hidden" name="action" value="delete_product">
                            <input type="hidden" name="id" value="${product.id}">
                            <button type="submit" class="btn btn-danger" style="width: 100%; justify-content: center;" onclick="return confirm('Delete this product?')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </form>
                    </div>
                </div>
            `;
            
            // Attach edit handler
            const editBtn = card.querySelector('.edit-product');
            if (editBtn && typeof openEditProductModal === 'function') {
                editBtn.addEventListener('click', function() {
                    openEditProductModal(
                        product.id,
                        product.name,
                        product.description,
                        product.price,
                        product.image_url,
                        product.category,
                        product.stock_quantity
                    );
                });
            }
            
            productsGrid.appendChild(card);
        });
    }

    /**
     * Filter products based on search and filter criteria
     */
    function filterProducts() {
        const searchTerm = (productSearch.value || '').toLowerCase();
        const selectedCategory = categoryFilter.value || '';
        const selectedStock = stockFilter.value || '';
        const selectedPrice = priceFilter.value || '';

        const filtered = productsData.filter(product => {
            // Search filter
            const matchesSearch = !searchTerm || 
                product.name.toLowerCase().includes(searchTerm) || 
                (product.category && product.category.toLowerCase().includes(searchTerm)) ||
                (product.description && product.description.toLowerCase().includes(searchTerm));

            // Category filter
            const matchesCategory = !selectedCategory || product.category === selectedCategory;

            // Stock filter
            let matchesStock = true;
            if (selectedStock === 'in-stock') {
                matchesStock = product.stock_quantity > 10;
            } else if (selectedStock === 'low-stock') {
                matchesStock = product.stock_quantity > 0 && product.stock_quantity <= 10;
            } else if (selectedStock === 'out-of-stock') {
                matchesStock = product.stock_quantity === 0;
            }

            // Price filter
            let matchesPrice = true;
            const price = parseFloat(product.price) || 0;
            if (selectedPrice === '0-5000') {
                matchesPrice = price >= 0 && price <= 5000;
            } else if (selectedPrice === '5000-15000') {
                matchesPrice = price > 5000 && price <= 15000;
            } else if (selectedPrice === '15000-50000') {
                matchesPrice = price > 15000 && price <= 50000;
            } else if (selectedPrice === '50000+') {
                matchesPrice = price > 50000;
            }

            return matchesSearch && matchesCategory && matchesStock && matchesPrice;
        });

        renderProducts(filtered);
    }

    /**
     * Reset all filters to default state
     */
    window.resetProductFilters = function() {
        if (productSearch) productSearch.value = '';
        if (categoryFilter) categoryFilter.value = '';
        if (stockFilter) stockFilter.value = '';
        if (priceFilter) priceFilter.value = '';
        renderProducts(productsData);
    };

    // Expose render function globally for navigation
    window.renderProductFilters = function() {
        renderProducts(productsData);
    };

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
