// Image preview helper
function previewImage(event, previewId) {
    const preview = document.getElementById(previewId);
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        }
        reader.readAsDataURL(file);
    } else {
        preview.style.display = 'none';
    }
}

// Modal functions
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) {
        console.error('Modal element not found:', modalId);
        return;
    }
    modal.classList.remove('active');
}

function openAddProductModal() {
    try {
        const modal = document.getElementById('addProductModal');
        if (!modal) {
            console.error('Modal element not found:', 'addProductModal');
            alert('Error: Modal not found');
            return;
        }
        
        // Ensure modal is displayed
        modal.classList.add('active');
        modal.style.display = 'flex';
        modal.style.visibility = 'visible';
        modal.style.opacity = '1';
        
        console.log('Modal opened successfully');
        console.log('Modal classes:', modal.className);
        console.log('Modal display:', window.getComputedStyle(modal).display);
        
        // Reset form
        const form = document.getElementById('addProductForm');
        if (form) {
            form.reset();
        }
        
        // Hide image preview
        const preview = document.getElementById('addImagePreview');
        if (preview) {
            preview.style.display = 'none';
            preview.src = '#';
        }
        
        return false;
    } catch (error) {
        console.error('Error opening modal:', error);
        alert('Error: ' + error.message);
        return false;
    }
}

// Open edit product modal from filters (called by product-filters.js)
function openEditProductModal(productId, productName, productDescription, productPrice, productImageUrl, productCategory, productStock) {
    document.getElementById('editProductId').value = productId;
    document.getElementById('editProductName').value = productName;
    document.getElementById('editProductDescription').value = productDescription;
    document.getElementById('editProductPrice').value = productPrice;
    document.getElementById('editProductCurrentImage').value = productImageUrl;
    document.getElementById('editProductCategory').value = productCategory;
    document.getElementById('editProductStock').value = productStock;
    
    // Handle image preview
    const currentImagePreview = document.getElementById('currentImagePreview');
    const noImageText = document.getElementById('noImageText');
    
    if (productImageUrl && productImageUrl.trim() !== '') {
        currentImagePreview.src = productImageUrl;
        currentImagePreview.style.display = 'block';
        noImageText.style.display = 'none';
    } else {
        currentImagePreview.style.display = 'none';
        noImageText.style.display = 'block';
    }
    
    document.getElementById('editProductModal').classList.add('active');
}

// Navigation
document.addEventListener('DOMContentLoaded', function() {
    const sidebarLinks = document.querySelectorAll('.sidebar-nav-link');
    const contentSections = document.querySelectorAll('.content-section');
    const pageTitle = document.getElementById('pageTitle');
    const pageIcon = document.getElementById('pageIcon');
    const adminSidebar = document.getElementById('adminSidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    // Function to close sidebar
    function closeSidebar() {
        adminSidebar.classList.remove('active');
        if (sidebarOverlay) {
            sidebarOverlay.classList.remove('active');
        }
    }

    // Function to open sidebar
    function openSidebar() {
        adminSidebar.classList.add('active');
        if (sidebarOverlay) {
            sidebarOverlay.classList.add('active');
        }
    }

    // Sidebar toggle
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            if (adminSidebar.classList.contains('active')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });
    }

    // Close sidebar when clicking on overlay
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function(e) {
            e.stopPropagation();
            closeSidebar();
        });
    }

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768) {
            if (adminSidebar && adminSidebar.classList.contains('active')) {
                if (!adminSidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                    closeSidebar();
                }
            }
        }
    });

    // Close sidebar when clicking on a sidebar link
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();

            const sectionId = this.getAttribute('data-section');
            
            // Validate section exists
            const section = document.getElementById(sectionId);
            if (!section) {
                console.error('Section not found:', sectionId);
                return;
            }

            // Remove active class from all links and sections
            sidebarLinks.forEach(l => l.classList.remove('active'));
            contentSections.forEach(s => s.classList.remove('active'));

            // Add active class to clicked link and corresponding section
            this.classList.add('active');
            section.classList.add('active');

            // Update page title and icon
            const linkText = this.querySelector('span').textContent;
            const iconClass = this.querySelector('i').className;
            
            if (pageTitle) pageTitle.textContent = linkText;
            if (pageIcon) pageIcon.className = iconClass;

            // Close sidebar on mobile
            if (window.innerWidth <= 768) {
                closeSidebar();
            }

            // Smooth scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });

    // Edit user modal
    document.querySelectorAll('.edit-user').forEach(btn => {
        btn.addEventListener('click', function() {
            const userId = this.getAttribute('data-id');
            const row = this.closest('tr');
            const cells = row.querySelectorAll('td');
            
            document.getElementById('userId').value = userId;
            document.getElementById('userFirstName').value = cells[0].textContent.split(' ')[0];
            document.getElementById('userLastName').value = cells[0].textContent.split(' ')[1] || '';
            document.getElementById('userEmail').value = cells[1].textContent;
            document.getElementById('userPhone').value = cells[2].textContent;
            
            document.getElementById('userModal').classList.add('active');
        });
    });

    // Edit product modal - for cards with data attributes
    document.querySelectorAll('.edit-product[data-id]').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            const productName = this.getAttribute('data-name');
            const productDescription = this.getAttribute('data-description');
            const productPrice = this.getAttribute('data-price');
            const productImageUrl = this.getAttribute('data-image-url');
            const productCategory = this.getAttribute('data-category');
            const productStock = this.getAttribute('data-stock-quantity');
            
            document.getElementById('editProductId').value = productId;
            document.getElementById('editProductName').value = productName;
            document.getElementById('editProductDescription').value = productDescription;
            document.getElementById('editProductPrice').value = productPrice;
            document.getElementById('editProductCurrentImage').value = productImageUrl;
            document.getElementById('editProductCategory').value = productCategory;
            document.getElementById('editProductStock').value = productStock;
            
            // Handle image preview
            const currentImagePreview = document.getElementById('currentImagePreview');
            const noImageText = document.getElementById('noImageText');
            
            if (productImageUrl && productImageUrl.trim() !== '') {
                currentImagePreview.src = productImageUrl;
                currentImagePreview.style.display = 'block';
                noImageText.style.display = 'none';
            } else {
                currentImagePreview.style.display = 'none';
                noImageText.style.display = 'block';
            }
            
            document.getElementById('editProductModal').classList.add('active');
        });
    });

    // Modal close buttons
    document.querySelectorAll('.modal-close').forEach(btn => {
        btn.addEventListener('click', function() {
            const modal = this.closest('.modal');
            modal.classList.remove('active');
        });
    });

    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.classList.remove('active');
        }
    });

    // Message Modal Functions
    window.openMessageModal = function(messageId, customerName, customerEmail, subject, message) {
        document.getElementById('messageFrom').textContent = customerName;
        document.getElementById('messageEmail').textContent = customerEmail;
        document.getElementById('messageSubject').textContent = subject;
        document.getElementById('messageContent').textContent = message;
        
        // Set form values
        document.getElementById('messageId').value = messageId;
        document.getElementById('replyEmail').value = customerEmail;
        document.getElementById('replyName').value = customerName;
        document.getElementById('replySubject').value = 'Re: ' + subject;
        document.getElementById('replyText').value = '';
        document.getElementById('replyForm').style.display = 'block';
        
        document.getElementById('messageModal').classList.add('active');
        // Mark as read
        const row = document.querySelector(`tr:has(button[onclick*="openMessageModal(${messageId}"])`);
        if (row) {
            const badge = row.querySelector('.status-unread');
            if (badge) badge.textContent = 'Read';
        }
    };

    window.closeMessageModal = function() {
        document.getElementById('messageModal').classList.remove('active');
    };

    // Handle reply form submission
    document.getElementById('replyForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = document.getElementById('sendReplyBtn');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
        
        const formData = new FormData(this);
        
        fetch('/api/send_reply.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Reply sent successfully!');
                closeMessageModal();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error sending reply. Please try again.');
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    });
});

// Orders Search and Filter Functionality
function initializeOrdersFilter() {
    console.log('Initializing orders filter...');
    
    const ordersData = document.getElementById('ordersData');
    const ordersSearch = document.getElementById('ordersSearch');
    const orderStatusFilter = document.getElementById('orderStatusFilter');
    const paymentStatusFilter = document.getElementById('paymentStatusFilter');
    const ordersList = document.getElementById('ordersList');
    const ordersResultsCount = document.getElementById('ordersResultsCount');
    const noOrdersMessage = document.getElementById('noOrdersMessage');
    const ordersTable = document.getElementById('ordersTable');

    console.log('Elements found:', {
        ordersData: !!ordersData,
        ordersSearch: !!ordersSearch,
        orderStatusFilter: !!orderStatusFilter,
        paymentStatusFilter: !!paymentStatusFilter,
        ordersList: !!ordersList,
        ordersResultsCount: !!ordersResultsCount,
        ordersTable: !!ordersTable
    });

    // Check if all required elements exist
    if (!ordersData) {
        console.warn('Orders data element not found');
        return;
    }

    if (!ordersSearch || !orderStatusFilter || !paymentStatusFilter || !ordersList) {
        console.warn('One or more filter elements missing. Orders filter disabled.');
        return;
    }

    let allOrders = [];
    
    try {
        allOrders = JSON.parse(ordersData.textContent);
        if (!Array.isArray(allOrders)) {
            console.warn('Orders data is not an array');
            return;
        }
        console.log('Orders filter initialized with ' + allOrders.length + ' orders');
    } catch (e) {
        console.error('Failed to parse orders data:', e);
        return;
    }

    // Define filter function
    const filterOrders = function() {
        const searchTerm = (ordersSearch.value || '').toLowerCase();
        const selectedStatus = orderStatusFilter.value || '';
        const selectedPaymentStatus = paymentStatusFilter.value || '';

        let visibleCount = 0;

        // Filter existing rows
        const rows = ordersList.querySelectorAll('tr.order-row');
        console.log('Filtering ' + rows.length + ' rows');

        rows.forEach(row => {
            const orderId = row.getAttribute('data-order-id');
            const status = row.getAttribute('data-status');
            const paymentStatus = row.getAttribute('data-payment-status') || 'pending';
            
            const cells = row.querySelectorAll('td');
            const customerName = (cells[1]?.textContent || '').toLowerCase();
            const email = (cells[2]?.textContent || '').toLowerCase();
            const orderNumber = (cells[0]?.textContent || '').toLowerCase();

            // Check if row matches filters
            const matchesSearch = !searchTerm || 
                orderId.includes(searchTerm) ||
                orderNumber.includes(searchTerm) ||
                customerName.includes(searchTerm) ||
                email.includes(searchTerm);

            const matchesStatus = !selectedStatus || status === selectedStatus;
            const matchesPaymentStatus = !selectedPaymentStatus || paymentStatus === selectedPaymentStatus;

            if (matchesSearch && matchesStatus && matchesPaymentStatus) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        // Update results count and no results message
        if (ordersResultsCount) {
            ordersResultsCount.innerHTML = `Showing <strong>${visibleCount}</strong> orders`;
        }
        
        if (noOrdersMessage) {
            noOrdersMessage.style.display = visibleCount === 0 ? 'block' : 'none';
        }
        
        if (ordersTable) {
            ordersTable.style.display = visibleCount === 0 ? 'none' : 'table';
        }

        console.log('Filter applied, ' + visibleCount + ' rows visible');
    };

    // Attach filter event listeners
    ordersSearch.addEventListener('input', filterOrders);
    orderStatusFilter.addEventListener('change', filterOrders);
    paymentStatusFilter.addEventListener('change', filterOrders);

    // Trigger initial filter
    console.log('Running initial filter...');
    filterOrders();
}

window.resetOrderFilters = function() {
    const ordersSearch = document.getElementById('ordersSearch');
    const orderStatusFilter = document.getElementById('orderStatusFilter');
    const paymentStatusFilter = document.getElementById('paymentStatusFilter');

    if (ordersSearch) ordersSearch.value = '';
    if (orderStatusFilter) orderStatusFilter.value = '';
    if (paymentStatusFilter) paymentStatusFilter.value = '';

    // Retrigger filter
    if (ordersSearch) ordersSearch.dispatchEvent(new Event('input'));
};

// Initialize orders filter when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeOrdersFilter);
} else {
    // DOM is already loaded, initialize immediately
    initializeOrdersFilter();
}

// Users Search and Filter Functionality
function initializeUsersFilter() {
    console.log('Initializing users filter...');
    
    const usersSearch = document.getElementById('usersSearch');
    const userStatusFilter = document.getElementById('userStatusFilter');
    const usersList = document.getElementById('usersList');
    const usersResultsCount = document.getElementById('usersResultsCount');
    const noUsersMessage = document.getElementById('noUsersMessage');
    const usersTable = document.getElementById('usersTable');

    console.log('Users elements found:', {
        usersSearch: !!usersSearch,
        userStatusFilter: !!userStatusFilter,
        usersList: !!usersList,
        usersResultsCount: !!usersResultsCount,
        usersTable: !!usersTable
    });

    // Check if all required elements exist
    if (!usersSearch || !userStatusFilter || !usersList) {
        console.warn('One or more user filter elements missing. Users filter disabled.');
        return;
    }

    // Define filter function
    const filterUsers = function() {
        const searchTerm = (usersSearch.value || '').toLowerCase();
        const selectedStatus = userStatusFilter.value || '';

        let visibleCount = 0;

        // Filter existing rows
        const rows = usersList.querySelectorAll('tr.user-row');
        console.log('Filtering ' + rows.length + ' user rows');

        rows.forEach(row => {
            const userId = row.getAttribute('data-user-id');
            const status = row.getAttribute('data-status');
            
            const cells = row.querySelectorAll('td');
            const name = (cells[0]?.textContent || '').toLowerCase();
            const email = (cells[1]?.textContent || '').toLowerCase();

            // Check if row matches filters
            const matchesSearch = !searchTerm || 
                userId.includes(searchTerm) ||
                name.includes(searchTerm) ||
                email.includes(searchTerm);

            const matchesStatus = !selectedStatus || status === selectedStatus;

            if (matchesSearch && matchesStatus) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        // Update results count and no results message
        if (usersResultsCount) {
            usersResultsCount.innerHTML = `Showing <strong>${visibleCount}</strong> users`;
        }
        
        if (noUsersMessage) {
            noUsersMessage.style.display = visibleCount === 0 ? 'block' : 'none';
        }
        
        if (usersTable) {
            usersTable.style.display = visibleCount === 0 ? 'none' : 'table';
        }

        console.log('Users filter applied, ' + visibleCount + ' rows visible');
    };

    // Attach filter event listeners
    usersSearch.addEventListener('input', filterUsers);
    userStatusFilter.addEventListener('change', filterUsers);

    // Trigger initial filter
    console.log('Running initial users filter...');
    filterUsers();
}

window.resetUserFilters = function() {
    const usersSearch = document.getElementById('usersSearch');
    const userStatusFilter = document.getElementById('userStatusFilter');

    if (usersSearch) usersSearch.value = '';
    if (userStatusFilter) userStatusFilter.value = '';

    // Retrigger filter
    if (usersSearch) usersSearch.dispatchEvent(new Event('input'));
};

// Initialize users filter when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        initializeOrdersFilter();
        initializeUsersFilter();
        initializeReviewsFilter();
        initializeMessagesFilter();
    });
} else {
    // DOM is already loaded, initialize immediately
    initializeOrdersFilter();
    initializeUsersFilter();
    initializeReviewsFilter();
    initializeMessagesFilter();
}

// Reviews Search and Filter Functionality
function initializeReviewsFilter() {
    console.log('Initializing reviews filter...');
    
    const reviewsSearch = document.getElementById('reviewsSearch');
    const reviewRatingFilter = document.getElementById('reviewRatingFilter');
    const reviewsList = document.getElementById('reviewsList');
    const reviewsResultsCount = document.getElementById('reviewsResultsCount');
    const noReviewsMessage = document.getElementById('noReviewsMessage');
    const reviewsTable = document.getElementById('reviewsTable');

    console.log('Reviews elements found:', {
        reviewsSearch: !!reviewsSearch,
        reviewRatingFilter: !!reviewRatingFilter,
        reviewsList: !!reviewsList,
        reviewsResultsCount: !!reviewsResultsCount,
        reviewsTable: !!reviewsTable
    });

    // Check if all required elements exist
    if (!reviewsSearch || !reviewRatingFilter || !reviewsList) {
        console.warn('One or more review filter elements missing. Reviews filter disabled.');
        return;
    }

    // Define filter function
    const filterReviews = function() {
        const searchTerm = (reviewsSearch.value || '').toLowerCase();
        const selectedRating = reviewRatingFilter.value || '';

        let visibleCount = 0;

        // Filter existing rows
        const rows = reviewsList.querySelectorAll('tr.review-row');
        console.log('Filtering ' + rows.length + ' review rows');

        rows.forEach(row => {
            const reviewId = row.getAttribute('data-review-id');
            const rating = row.getAttribute('data-rating');
            
            const cells = row.querySelectorAll('td');
            const user = (cells[0]?.textContent || '').toLowerCase();
            const product = (cells[1]?.textContent || '').toLowerCase();
            const comment = (cells[3]?.textContent || '').toLowerCase();

            // Check if row matches filters
            const matchesSearch = !searchTerm || 
                reviewId.includes(searchTerm) ||
                user.includes(searchTerm) ||
                product.includes(searchTerm) ||
                comment.includes(searchTerm);

            const matchesRating = !selectedRating || rating === selectedRating;

            if (matchesSearch && matchesRating) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        // Update results count and no results message
        if (reviewsResultsCount) {
            reviewsResultsCount.innerHTML = `Showing <strong>${visibleCount}</strong> reviews`;
        }
        
        if (noReviewsMessage) {
            noReviewsMessage.style.display = visibleCount === 0 ? 'block' : 'none';
        }
        
        if (reviewsTable) {
            reviewsTable.style.display = visibleCount === 0 ? 'none' : 'table';
        }

        console.log('Reviews filter applied, ' + visibleCount + ' rows visible');
    };

    // Attach filter event listeners
    reviewsSearch.addEventListener('input', filterReviews);
    reviewRatingFilter.addEventListener('change', filterReviews);

    // Trigger initial filter
    console.log('Running initial reviews filter...');
    filterReviews();
}

window.resetReviewFilters = function() {
    const reviewsSearch = document.getElementById('reviewsSearch');
    const reviewRatingFilter = document.getElementById('reviewRatingFilter');

    if (reviewsSearch) reviewsSearch.value = '';
    if (reviewRatingFilter) reviewRatingFilter.value = '';

    // Retrigger filter
    if (reviewsSearch) reviewsSearch.dispatchEvent(new Event('input'));
};

// Messages Search and Filter Functionality
function initializeMessagesFilter() {
    console.log('Initializing messages filter...');
    
    const messagesSearch = document.getElementById('messagesSearch');
    const messageStatusFilter = document.getElementById('messageStatusFilter');
    const messagesList = document.getElementById('messagesList');
    const messagesResultsCount = document.getElementById('messagesResultsCount');
    const noMessagesMessage = document.getElementById('noMessagesMessage');
    const messagesTable = document.getElementById('messagesTable');

    console.log('Messages elements found:', {
        messagesSearch: !!messagesSearch,
        messageStatusFilter: !!messageStatusFilter,
        messagesList: !!messagesList,
        messagesResultsCount: !!messagesResultsCount,
        messagesTable: !!messagesTable
    });

    // Check if all required elements exist
    if (!messagesSearch || !messageStatusFilter || !messagesList) {
        console.warn('One or more message filter elements missing. Messages filter disabled.');
        return;
    }

    // Define filter function
    const filterMessages = function() {
        const searchTerm = (messagesSearch.value || '').toLowerCase();
        const selectedStatus = messageStatusFilter.value || '';

        let visibleCount = 0;

        // Filter existing rows
        const rows = messagesList.querySelectorAll('tr.message-row');
        console.log('Filtering ' + rows.length + ' message rows');

        rows.forEach(row => {
            const messageId = row.getAttribute('data-message-id');
            const status = row.getAttribute('data-status');
            
            const cells = row.querySelectorAll('td');
            const name = (cells[0]?.textContent || '').toLowerCase();
            const email = (cells[1]?.textContent || '').toLowerCase();
            const subject = (cells[2]?.textContent || '').toLowerCase();

            // Check if row matches filters
            const matchesSearch = !searchTerm || 
                messageId.includes(searchTerm) ||
                name.includes(searchTerm) ||
                email.includes(searchTerm) ||
                subject.includes(searchTerm);

            const matchesStatus = !selectedStatus || status === selectedStatus;

            if (matchesSearch && matchesStatus) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        // Update results count and no results message
        if (messagesResultsCount) {
            messagesResultsCount.innerHTML = `Showing <strong>${visibleCount}</strong> messages`;
        }
        
        if (noMessagesMessage) {
            noMessagesMessage.style.display = visibleCount === 0 ? 'block' : 'none';
        }
        
        if (messagesTable) {
            messagesTable.style.display = visibleCount === 0 ? 'none' : 'table';
        }

        console.log('Messages filter applied, ' + visibleCount + ' rows visible');
    };

    // Attach filter event listeners
    messagesSearch.addEventListener('input', filterMessages);
    messageStatusFilter.addEventListener('change', filterMessages);

    // Trigger initial filter
    console.log('Running initial messages filter...');
    filterMessages();
}

window.resetMessageFilters = function() {
    const messagesSearch = document.getElementById('messagesSearch');
    const messageStatusFilter = document.getElementById('messageStatusFilter');

    if (messagesSearch) messagesSearch.value = '';
    if (messageStatusFilter) messageStatusFilter.value = '';

    // Retrigger filter
    if (messagesSearch) messagesSearch.dispatchEvent(new Event('input'));
};
