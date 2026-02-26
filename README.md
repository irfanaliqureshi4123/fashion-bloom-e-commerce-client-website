# Fashion Bloom - E-Commerce Client Website

A modern, feature-rich e-commerce platform specializing in fashion accessories including watches, chains, and bracelets.

## Project Overview

Fashion Bloom is a full-stack e-commerce application built with PHP, MySQL, JavaScript, and Stripe payment integration. The platform provides a seamless shopping experience with product browsing, cart management, user authentication, and secure payment processing.

## Features

### Customer Features
- **Product Catalog**: Browse watches, chains, and bracelets with detailed product information
- **Advanced Search & Filters**: Filter products by category, price, and other attributes
- **User Authentication**: Secure registration and login system with email verification
- **Shopping Cart**: Add/remove items, manage quantities, and view cart summary
- **Wishlist**: Save favorite products for later purchase
- **Order Management**: Track orders and view order history
- **User Dashboard**: Personal profile, order history, and account settings
- **Secure Checkout**: Multi-step checkout process with shipping and payment options
- **Stripe Payment Integration**: Secure credit card payments using Stripe API
- **Product Reviews**: Leave and view product reviews and ratings
- **Responsive Design**: Mobile-friendly interface optimized for all devices

### Admin Features
- **Admin Dashboard**: Overview of sales, orders, and metrics
- **Product Management**: Add, edit, and delete products
- **Order Management**: View and manage customer orders
- **User Management**: Manage customer accounts
- **Analytics**: Sales reports and business insights

## Tech Stack

### Backend
- **PHP 7.4+**
- **MySQL 5.7+**
- **Stripe API** for payments
- **Composer** for dependency management

### Frontend
- **HTML5**
- **CSS3** with responsive design
- **Vanilla JavaScript**
- **No external framework dependencies**

### Additional Technologies
- **XAMPP** (Local development environment)
- **Git** for version control

## Project Structure

```
fashion_bloom/
├── admin/                      # Admin panel files
├── api/                        # API endpoints
│   ├── contact.php
│   ├── reviews.php
│   ├── search.php
│   └── send_reply.php
├── assets/                     # Images and media
│   └── images/
│       └── products/
├── checkout-new/              # New checkout flow
├── config/                     # Configuration files
│   └── config.php
├── css/                        # Stylesheets
│   ├── admin/                 # Admin styles
│   ├── base/                  # Base/reset styles
│   ├── components/            # Component styles
│   ├── layout/                # Layout styles
│   ├── pages/                 # Page-specific styles
│   └── utilities/             # Utility styles
├── includes/                   # Shared PHP includes
│   ├── cart.php
│   ├── check_session.php
│   ├── db.php
│   ├── email.php
│   ├── footer.php
│   ├── header.php
│   ├── process_order.php
│   ├── save_shipping.php
│   ├── stripe-charge.php
│   └── wishlist.php
├── js/                        # JavaScript files
│   ├── admin.js
│   ├── cart.js
│   ├── checkout.js
│   ├── payment-stripe.js
│   ├── product-filters.js
│   └── ...
├── uploads/                   # User uploads directory
│   ├── admin/
│   └── products/
├── vendor/                    # Composer dependencies
├── .gitignore
├── composer.json              # PHP dependencies
└── README.md                  # This file
```

## Setup Instructions

### Prerequisites
- XAMPP (Apache, MySQL, PHP)
- Composer
- Git
- Stripe Account (for payment processing)

### Local Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/irfanaliqureshi4123/fashion-bloom-e-commerce-client-website.git
   cd fashion_bloom
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Configure database**
   - Create MySQL database: `fashion_bloom`
   - Update `config/config.php` with your database credentials
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'fashion_bloom');
   ```

4. **Set up Stripe keys**
   - Add your Stripe API keys to `config/config.php`
   ```php
   define('STRIPE_PUBLIC_KEY', 'your_public_key');
   define('STRIPE_SECRET_KEY', 'your_secret_key');
   ```

5. **Create required directories**
   ```bash
   mkdir -p uploads/admin
   mkdir -p uploads/products
   chmod 755 uploads uploads/admin uploads/products
   ```

6. **Access the application**
   - Navigate to: `http://localhost/fashion_bloom`

## Configuration

### Email Setup
Update email configuration in `includes/email.php` for email notifications:
- Email verification
- Order confirmations
- Password resets
- Contact form responses

### Database Setup
Import the database schema (if provided) or create tables manually:
- Users
- Products
- Orders
- Cart Items
- Reviews
- Wishlist Items

## API Endpoints

### Public Endpoints
- `GET /api/search.php?q=query` - Search products
- `GET /api/reviews.php?product_id=id` - Get product reviews
- `POST /api/contact.php` - Submit contact form
- `POST /api/send_reply.php` - Send review reply (admin)

## Usage

### For Customers
1. Register or login to your account
2. Browse products or use search/filters
3. Add items to cart or wishlist
4. Proceed to checkout
5. Enter shipping information
6. Complete payment via Stripe
7. View order confirmation

### For Admins
1. Access admin panel at `/admin.php`
2. Manage products, orders, and users
3. View sales analytics
4. Respond to customer reviews

## Payment Processing

This application uses Stripe for secure payment processing:
- PCI DSS compliant
- Supports multiple payment methods
- Automatic invoice generation
- Payment status tracking

## Security Features

- SQL injection prevention
- XSS protection
- CSRF tokens
- Password hashing
- Secure session management
- Input validation and sanitization

## Contributing

1. Create a feature branch: `git checkout -b feature/your-feature`
2. Commit changes: `git commit -am 'Add your feature'`
3. Push to branch: `git push origin feature/your-feature`
4. Submit a pull request

## License

This project is proprietary software. All rights reserved.

## Support

For issues, questions, or feature requests, please contact:
- Email: admin@fashionbloom.local
- GitHub Issues: [Project Issues](https://github.com/irfanaliqureshi4123/fashion-bloom-e-commerce-client-website/issues)

## Changelog

### v1.0.0 (Initial Release)
- Core e-commerce functionality
- Product catalog with categories
- User authentication system
- Shopping cart and checkout
- Stripe payment integration
- Admin dashboard
- Product reviews system
- Email notifications

## Roadmap

- [ ] Mobile app (React Native)
- [ ] Advanced analytics dashboard
- [ ] Inventory management system
- [ ] Multi-vendor support
- [ ] Social media integration
- [ ] AI-powered recommendations
- [ ] Enhanced search with Elasticsearch

## Author

Irfan Ali Qureshi

---

**Last Updated**: February 26, 2026
