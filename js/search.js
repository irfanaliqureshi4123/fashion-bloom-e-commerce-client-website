// Product Search Functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('product-search');
    const searchDropdown = document.getElementById('search-dropdown');
    const searchContainer = searchInput ? searchInput.parentElement : null;
    let searchTimeout;

    if (!searchInput || !searchContainer) return;

    // Function to position the dropdown below search input
    function positionDropdown() {
        const rect = searchContainer.getBoundingClientRect();
        searchDropdown.style.left = rect.left + 'px';
        searchDropdown.style.top = (rect.bottom + 4) + 'px'; // 4px gap below search bar
        searchDropdown.style.width = rect.width + 'px';
    }

    // Listen for input changes
    searchInput.addEventListener('input', function(e) {
        const query = e.target.value.trim();
        
        // Clear previous timeout
        clearTimeout(searchTimeout);
        
        if (query.length < 2) {
            searchDropdown.classList.remove('active');
            return;
        }

        // Debounce search (wait 300ms after user stops typing)
        searchTimeout = setTimeout(() => {
            performSearch(query);
        }, 300);
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchDropdown.contains(e.target)) {
            searchDropdown.classList.remove('active');
        }
    });

    // Allow reopening dropdown when input is focused
    searchInput.addEventListener('focus', function() {
        if (searchInput.value.trim().length >= 2 && searchDropdown.innerHTML) {
            positionDropdown();
            searchDropdown.classList.add('active');
        }
    });

    // Handle Enter key to search
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const query = searchInput.value.trim();
            if (query.length > 0) {
                window.location.href = `/search.php?q=${encodeURIComponent(query)}`;
            }
        }
    });

    // Reposition dropdown on window resize and scroll
    window.addEventListener('resize', function() {
        if (searchDropdown.classList.contains('active')) {
            positionDropdown();
        }
    });

    window.addEventListener('scroll', function() {
        if (searchDropdown.classList.contains('active')) {
            positionDropdown();
        }
    });
});

// Perform search and show results
function performSearch(query) {
    const searchInput = document.getElementById('product-search');
    const searchDropdown = document.getElementById('search-dropdown');
    const searchContainer = searchInput ? searchInput.parentElement : null;
    
    // Position dropdown before showing
    if (searchContainer) {
        const rect = searchContainer.getBoundingClientRect();
        searchDropdown.style.left = rect.left + 'px';
        searchDropdown.style.top = (rect.bottom + 4) + 'px';
        searchDropdown.style.width = rect.width + 'px';
    }
    
    // Show loading state
    searchDropdown.innerHTML = '<div class="search-result-item"><i class="fas fa-spinner fa-spin"></i> Searching...</div>';
    searchDropdown.classList.add('active');

    // Fetch search results from server
    fetch('/api/search.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ query: query, limit: 8 })
    })
    .then(response => response.json())
    .then(data => {
        displaySearchResults(data, query);
    })
    .catch(error => {
        console.error('Search error:', error);
        searchDropdown.innerHTML = '<div class="search-empty"><i class="fas fa-exclamation-circle"></i> Error searching products</div>';
    });
}

// Display search results in dropdown
function displaySearchResults(data, query) {
    const searchDropdown = document.getElementById('search-dropdown');
    
    if (!data.success || !data.results || data.results.length === 0) {
        searchDropdown.innerHTML = `<div class="search-empty"><i class="fas fa-search"></i> No products found for "${query}"</div>`;
        return;
    }

    let html = '';
    
    data.results.forEach(product => {
        html += `
            <div class="search-result-item" onclick="selectProduct('${product.name}', '${product.category}')">
                <img src="${product.image}" alt="${product.name}" class="search-result-image" onerror="this.src='/assets/images/placeholder.png'">
                <div class="search-result-info">
                    <div class="search-result-name">${product.name}</div>
                    <div class="search-result-category">${product.category}</div>
                </div>
                <div class="search-result-price">PKR ${product.price.toLocaleString()}</div>
            </div>
        `;
    });

    // Add "View All Results" option
    html += `
        <div class="search-view-all" onclick="viewAllResults('${query}')">
            <i class="fas fa-arrow-right"></i> View All Results
        </div>
    `;

    searchDropdown.innerHTML = html;
}

// Handle product selection from dropdown
function selectProduct(productName, category) {
    const searchInput = document.getElementById('product-search');
    const searchDropdown = document.getElementById('search-dropdown');
    
    // Clear search and close dropdown
    searchInput.value = '';
    searchDropdown.classList.remove('active');
    
    // Scroll to the product or navigate to it
    // This assumes products are displayed on index.php with data attributes
    scrollToProduct(productName, category);
}

// Scroll to product on products page
function scrollToProduct(productName, category) {
    // If on products page, find and highlight the product
    const productElements = document.querySelectorAll('[data-product-name]');
    let found = false;

    productElements.forEach(element => {
        if (element.dataset.productName === productName) {
            element.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            // Add highlight effect
            element.style.transform = 'scale(1.02)';
            element.style.boxShadow = '0 0 20px rgba(212, 175, 55, 0.5)';
            
            setTimeout(() => {
                element.style.transform = 'scale(1)';
                element.style.boxShadow = 'none';
            }, 1500);
            
            found = true;
        }
    });

    if (!found) {
        // If not found, navigate to search results page
        window.location.href = `/search.php?q=${encodeURIComponent(productName)}&category=${encodeURIComponent(category)}`;
    }
}

// View all search results
function viewAllResults(query) {
    window.location.href = `/search.php?q=${encodeURIComponent(query)}`;
}
