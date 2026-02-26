/**
 * Product Review System
 * Handles displaying, submitting, and voting on reviews
 */

class ReviewSystem {
    constructor(productId, containerId = 'reviews-container') {
        this.productId = productId;
        this.containerId = containerId;
        this.container = document.getElementById(containerId);
        this.currentPage = 1;
        this.currentSort = 'recent';
        
        if (this.container) {
            this.init();
        }
    }

    async init() {
        await this.loadRatingSummary();
        await this.loadReviews();
        this.setupEventListeners();
    }

    /**
     * Load and display rating summary
     */
    async loadRatingSummary() {
        try {
            const response = await fetch(`api/reviews.php?action=get_rating_summary&product_id=${this.productId}`);
            const data = await response.json();

            if (data.success) {
                this.displayRatingSummary(data.summary);
            }
        } catch (error) {
            console.error('Error loading rating summary:', error);
        }
    }

    /**
     * Display rating summary
     */
    displayRatingSummary(summary) {
        if (!summary) {
            console.warn('No summary data received for product:', this.productId);
            return;
        }
        
        const avgRating = parseFloat(summary.average_rating || 0).toFixed(1);
        const totalReviews = parseInt(summary.total_reviews) || 0;
        const rating_5 = parseInt(summary.rating_5) || 0;
        const rating_4 = parseInt(summary.rating_4) || 0;
        const rating_3 = parseInt(summary.rating_3) || 0;
        const rating_2 = parseInt(summary.rating_2) || 0;
        const rating_1 = parseInt(summary.rating_1) || 0;

        const summaryHTML = `
            <div class="review-summary">
                <div class="rating-info">
                    <div class="average-rating">
                        <div class="stars-large">${this.renderStars(avgRating)}</div>
                        <div class="rating-number">${avgRating}</div>
                        <div class="review-count">Based on ${totalReviews} review${totalReviews !== 1 ? 's' : ''}</div>
                    </div>
                </div>

                <div class="rating-breakdown">
                    ${this.renderRatingBar(5, rating_5, totalReviews)}
                    ${this.renderRatingBar(4, rating_4, totalReviews)}
                    ${this.renderRatingBar(3, rating_3, totalReviews)}
                    ${this.renderRatingBar(2, rating_2, totalReviews)}
                    ${this.renderRatingBar(1, rating_1, totalReviews)}
                </div>

                <button class="write-review-btn" onclick="reviewModal.show()">Write a Review</button>
            </div>
        `;

        // Insert summary at the beginning of the container
        if (this.container) {
            this.container.insertAdjacentHTML('afterbegin', summaryHTML);
        }
    }

    /**
     * Render rating breakdown bar
     */
    renderRatingBar(stars, count, total) {
        const percentage = total > 0 ? (count / total * 100) : 0;
        return `
            <div class="rating-bar-item">
                <span class="rating-label">${stars} <span class="star">★</span></span>
                <div class="bar-container">
                    <div class="bar-fill" style="width: ${percentage}%"></div>
                </div>
                <span class="rating-count">${count}</span>
            </div>
        `;
    }

    /**
     * Render star rating
     */
    renderStars(rating, interactive = false) {
        rating = parseFloat(rating);
        let html = '';
        
        for (let i = 1; i <= 5; i++) {
            if (i <= rating) {
                html += '<span class="star-full">★</span>';
            } else if (i - rating < 1) {
                html += '<span class="star-half">★</span>';
            } else {
                html += '<span class="star-empty">★</span>';
            }
        }
        
        return html;
    }

    /**
     * Load and display reviews
     */
    async loadReviews() {
        try {
            const url = `api/reviews.php?action=get_reviews&product_id=${this.productId}&sort=${this.currentSort}&page=${this.currentPage}`;
            const response = await fetch(url);
            const data = await response.json();

            if (data.success) {
                this.displayReviews(data.reviews, data.pagination);
            }
        } catch (error) {
            console.error('Error loading reviews:', error);
            this.container.innerHTML += '<p class="error-message">Error loading reviews</p>';
        }
    }

    /**
     * Display reviews list
     */
    displayReviews(reviews, pagination) {
        if (!reviews) {
            reviews = [];
        }
        
        const reviewsHTML = `
            <div class="reviews-section">
                <div class="reviews-controls">
                    <label for="sort-reviews-${this.productId}">Sort Reviews</label>
                    <select id="sort-reviews-${this.productId}" class="sort-select" onchange="reviewSystem.setSortOption(this.value)">
                        <option value="recent">📅 Most Recent</option>
                        <option value="helpful">👍 Most Helpful</option>
                        <option value="highest">⭐ Highest Rated</option>
                        <option value="lowest">⭐ Lowest Rated</option>
                    </select>
                </div>

                <div class="reviews-list">>
                    ${reviews.length > 0 ? reviews.map(review => this.renderReview(review)).join('') : '<p class="no-reviews">No reviews yet. Be the first to review this product!</p>'}
                </div>

                ${pagination && pagination.total_pages > 1 ? this.renderPagination(pagination) : ''}
            </div>
        `;

        // Remove existing reviews section if it exists
        const existingSection = this.container.querySelector('.reviews-section');
        if (existingSection) {
            existingSection.remove();
        }

        this.container.insertAdjacentHTML('beforeend', reviewsHTML);
        this.setupEventListeners();
    }

    /**
     * Render individual review
     */
    renderReview(review) {
        const userName = `${review.first_name} ${review.last_name[0]}.`;
        const reviewDate = this.formatDate(review.created_at);

        return `
            <div class="review-item" data-review-id="${review.id}">
                <div class="review-header">
                    <div class="reviewer-info">
                        <div class="reviewer-name">${userName}</div>
                        ${review.verified_purchase ? '<span class="verified-badge">✓ Verified Purchase</span>' : ''}
                    </div>
                    <div class="review-date">${reviewDate}</div>
                </div>

                <div class="review-rating">
                    ${this.renderStars(review.rating)}
                </div>

                <div class="review-title">${this.escapeHtml(review.title)}</div>

                <div class="review-text">${this.escapeHtml(review.review_text)}</div>

                <div class="review-footer">
                    <span class="helpful-label">Was this review helpful?</span>
                    <button class="vote-btn helpful-btn" data-vote="helpful">
                        <span class="icon">👍</span> Helpful (${review.helpful_count})
                    </button>
                    <button class="vote-btn unhelpful-btn" data-vote="unhelpful">
                        <span class="icon">👎</span> Unhelpful (${review.unhelpful_count})
                    </button>
                </div>
            </div>
        `;
    }

    /**
     * Render pagination
     */
    renderPagination(pagination) {
        let paginationHTML = '<div class="pagination">';

        if (pagination.current_page > 1) {
            paginationHTML += `<button class="pagination-btn" onclick="reviewSystem.goToPage(${pagination.current_page - 1})">← Previous</button>`;
        }

        paginationHTML += `<span class="pagination-info">Page ${pagination.current_page} of ${pagination.total_pages}</span>`;

        if (pagination.current_page < pagination.total_pages) {
            paginationHTML += `<button class="pagination-btn" onclick="reviewSystem.goToPage(${pagination.current_page + 1})">Next →</button>`;
        }

        paginationHTML += '</div>';
        return paginationHTML;
    }

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Try both old and new ID formats for sort select
        const sortSelect = this.container.querySelector('#sort-reviews') || 
                          this.container.querySelector(`#sort-reviews-${this.productId}`);
        if (sortSelect) {
            sortSelect.addEventListener('change', (e) => {
                this.currentSort = e.target.value;
                this.currentPage = 1;
                this.loadReviews();
            });
        }

        // Vote buttons
        this.container.querySelectorAll('.vote-btn').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                const reviewItem = btn.closest('.review-item');
                const reviewId = reviewItem.dataset.reviewId;
                const voteType = btn.dataset.vote;
                
                await this.voteOnReview(reviewId, voteType, btn);
            });
        });
    }
    
    /**
     * Set sort option (for inline onchange handler)
     */
    setSortOption(value) {
        this.currentSort = value;
        this.currentPage = 1;
        this.loadReviews();
    }

    /**
     * Vote on a review
     */
    async voteOnReview(reviewId, voteType, button) {
        try {
            const response = await fetch('api/reviews.php?action=vote_helpful', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    review_id: reviewId,
                    vote_type: voteType
                })
            });

            const data = await response.json();

            if (data.success) {
                // Reload reviews to show updated counts
                this.loadReviews();
            } else if (response.status === 401) {
                alert('Please log in to vote on reviews');
                window.location.href = '/fashion_bloom/login.php';
            } else {
                alert(data.message || 'Error voting on review');
            }
        } catch (error) {
            console.error('Error voting on review:', error);
            alert('Error voting on review');
        }
    }

    /**
     * Go to specific page
     */
    async goToPage(page) {
        this.currentPage = page;
        window.scrollTo({ top: this.container.offsetTop - 100, behavior: 'smooth' });
        await this.loadReviews();
    }

    /**
     * Format date
     */
    formatDate(dateString) {
        const date = new Date(dateString);
        const today = new Date();
        const yesterday = new Date(today);
        yesterday.setDate(yesterday.getDate() - 1);

        if (date.toDateString() === today.toDateString()) {
            return 'Today';
        } else if (date.toDateString() === yesterday.toDateString()) {
            return 'Yesterday';
        } else {
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: date.getFullYear() !== today.getFullYear() ? 'numeric' : undefined });
        }
    }

    /**
     * Escape HTML
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

/**
 * Review Modal for submitting reviews
 */
class ReviewModal {
    constructor(productId, onSubmit) {
        this.productId = productId;
        this.onSubmit = onSubmit;
        this.rating = 0;
        this.create();
    }

    /**
     * Create modal HTML
     */
    create() {
        const modal = document.createElement('div');
        modal.id = 'review-modal';
        modal.className = 'modal';
        modal.innerHTML = `
            <div class="modal-content review-form-modal">
                <div class="modal-header">
                    <h2>Write a Review</h2>
                    <button class="close-btn" onclick="reviewModal.close()">×</button>
                </div>

                <form id="review-form" class="review-form">
                    <div class="form-group">
                        <label>Rating</label>
                        <div class="rating-input" id="rating-stars">
                            <span class="star" data-rating="5">★</span>
                            <span class="star" data-rating="4">★</span>
                            <span class="star" data-rating="3">★</span>
                            <span class="star" data-rating="2">★</span>
                            <span class="star" data-rating="1">★</span>
                        </div>
                        <input type="hidden" id="rating-input" name="rating" value="0">
                        <span class="rating-display" id="rating-display">Select a rating</span>
                    </div>

                    <div class="form-group">
                        <label for="review-title">Review Title *</label>
                        <input type="text" id="review-title" name="title" placeholder="Summarize your experience" maxlength="255" autocomplete="off" required>
                        <small class="char-count"><span id="title-count">0</span>/255</small>
                    </div>

                    <div class="form-group">
                        <label for="review-text">Your Review *</label>
                        <textarea id="review-text" name="review_text" placeholder="Share your experience with this product" maxlength="2000" required></textarea>
                        <small class="char-count"><span id="text-count">0</span>/2000</small>
                    </div>

                    <div class="form-group checkbox">
                        <input type="checkbox" id="agree-checkbox" name="agree" required>
                        <label for="agree-checkbox">I confirm this review is my genuine experience and I have no financial interest in this product</label>
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="btn-secondary" onclick="reviewModal.close()">Cancel</button>
                        <button type="submit" class="btn-primary">Submit Review</button>
                    </div>
                </form>
            </div>
        `;

        document.body.appendChild(modal);
        this.modal = modal;
        this.form = modal.querySelector('#review-form');
        this.setupEventListeners();
    }

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Star rating
        this.modal.querySelectorAll('.rating-input .star').forEach(star => {
            star.addEventListener('click', () => {
                this.rating = parseInt(star.dataset.rating);
                this.updateRatingDisplay();
            });

            star.addEventListener('mouseover', () => {
                const hoverRating = parseInt(star.dataset.rating);
                this.updateRatingDisplay(hoverRating);
            });
        });

        this.modal.querySelector('.rating-input').addEventListener('mouseout', () => {
            this.updateRatingDisplay();
        });

        // Character counters
        this.modal.querySelector('#review-title').addEventListener('input', (e) => {
            document.querySelector('#title-count').textContent = e.target.value.length;
        });

        this.modal.querySelector('#review-text').addEventListener('input', (e) => {
            document.querySelector('#text-count').textContent = e.target.value.length;
        });

        // Form submission
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));

        // Close on Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.modal.classList.contains('show')) {
                this.close();
            }
        });

        // Close on background click
        this.modal.addEventListener('click', (e) => {
            if (e.target === this.modal) {
                this.close();
            }
        });
    }

    /**
     * Update rating display
     */
    updateRatingDisplay(hoverRating = null) {
        const displayRating = hoverRating || this.rating;
        const displayText = displayRating > 0 ? `${displayRating} star${displayRating !== 1 ? 's' : ''}` : 'Select a rating';
        
        document.querySelector('#rating-display').textContent = displayText;
        document.querySelector('#rating-input').value = this.rating;

        this.modal.querySelectorAll('.rating-input .star').forEach(star => {
            const starRating = parseInt(star.dataset.rating);
            if (starRating <= displayRating) {
                star.classList.add('active');
            } else {
                star.classList.remove('active');
            }
        });
    }

    /**
     * Handle form submission
     */
    async handleSubmit(e) {
        e.preventDefault();

        if (this.rating === 0) {
            alert('Please select a rating');
            return;
        }

        const title = this.form.querySelector('#review-title').value.trim();
        const reviewText = this.form.querySelector('#review-text').value.trim();

        if (title.length < 5) {
            alert('Review title must be at least 5 characters');
            return;
        }

        if (reviewText.length < 10) {
            alert('Review text must be at least 10 characters');
            return;
        }

        try {
            const response = await fetch('api/reviews.php?action=submit_review', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    product_id: this.productId,
                    rating: this.rating,
                    title: title,
                    review_text: reviewText
                })
            });

            const data = await response.json();

            if (data.success) {
                alert('Thank you! Your review has been submitted.');
                this.close();
                this.form.reset();
                this.rating = 0;
                this.updateRatingDisplay();
                
                if (this.onSubmit) {
                    this.onSubmit();
                }
            } else if (response.status === 401) {
                alert('Please log in to submit a review');
                window.location.href = '/fashion_bloom/login.php';
            } else {
                alert(data.message || 'Error submitting review');
            }
        } catch (error) {
            console.error('Error submitting review:', error);
            alert('Error submitting review');
        }
    }

    /**
     * Show modal
     */
    show() {
        if (!this.modal) {
            console.error('Modal not found');
            return;
        }
        this.modal.classList.add('show');
        // Reset form
        this.form.reset();
        this.rating = 0;
        this.updateRatingDisplay();
        document.querySelector('#title-count').textContent = '0';
        document.querySelector('#text-count').textContent = '0';
    }

    /**
     * Close modal
     */
    close() {
        if (!this.modal) return;
        this.modal.classList.remove('show');
        this.form.reset();
    }
}

/**
 * Global instances
 */
let reviewSystem;
let reviewModal;

/**
 * Initialize review system
 */
function initReviewSystem(productId, containerId = 'reviews-container') {
    reviewSystem = new ReviewSystem(productId, containerId);
    reviewModal = new ReviewModal(productId, () => reviewSystem.loadReviews());
}
