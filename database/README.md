# Fashion Bloom Database Schema

## Overview

This folder contains the complete SQL database schema for the Fashion Bloom e-commerce platform.

## Files

- **schema.sql** - Complete database schema including all tables, relationships, and indexes

## Database Setup

### Option 1: Using MySQL Command Line

```bash
# Open MySQL command line
mysql -u root -p

# Run the schema file
source /path/to/database/schema.sql;
```

### Option 2: Using phpMyAdmin (XAMPP)

1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Click on "Import" tab
3. Choose the `schema.sql` file
4. Click "Go" to execute

### Option 3: Using PHP Script

```php
<?php
$connection = new mysqli('localhost', 'root', '', '');

if ($connection->connect_error) {
    die('Connection failed: ' . $connection->connect_error);
}

$sql = file_get_contents('database/schema.sql');
$statements = array_filter(array_map('trim', explode(';', $sql)), function($s) {
    return !empty($s) && !preg_match('/^\-\-/', $s);
});

foreach ($statements as $statement) {
    if (!$connection->query($statement)) {
        die('Error: ' . $connection->error);
    }
}

echo 'Database schema created successfully!';
$connection->close();
?>
```

## Database Structure

### User Management
- **users** - Customer and admin accounts
- **addresses** - Shipping and billing addresses
- **email_logs** - Email verification and notification logs

### Product Catalog
- **categories** - Product categories (hierarchical)
- **products** - Product information and inventory
- **product_images** - Multiple images per product
- **product_attributes** - Product specifications (color, size, etc.)

### Shopping & Orders
- **cart_items** - Shopping cart items
- **wishlist** - Saved wishlist items
- **orders** - Customer orders
- **order_items** - Individual items in orders
- **returns** - Product returns and refunds

### Payments & Transactions
- **payments** - Payment transaction records
- **contact_messages** - Customer inquiries

### Reviews & Ratings
- **reviews** - Product reviews and ratings

### Administration
- **settings** - Application settings
- **audit_logs** - System activity logs

## Key Features

### Data Integrity
- Foreign key constraints to maintain referential integrity
- Unique constraints to prevent duplicates
- Check constraints for valid data ranges
- Cascade and restrict rules for data consistency

### Performance Optimization
- Strategic indexes on frequently queried columns
- Full-text search index on product names and descriptions
- Composite indexes for common query patterns

### Security
- UTF8MB4 character set for international support
- Prepared statement support via ORM/framework
- Password fields for secure storage (use hashing in application)

### Audit Trail
- `created_at` and `updated_at` timestamps on all tables
- Audit logs for tracking administrative changes
- Email logs for sent communications

## Important Notes

1. **Admin User**: A default admin account is provided
   - Email: admin@fashionbloom.local
   - Password: admin123 (CHANGE THIS IN PRODUCTION!)

2. **Password Handling**: The schema expects hashed passwords
   - Use bcrypt or similar hashing in the application
   - Never store plaintext passwords

3. **Production Considerations**
   - Backup your database regularly
   - Set appropriate user permissions
   - Use strong passwords for database accounts
   - Enable SSL/TLS for database connections
   - Implement proper access controls

4. **Stripe Integration**
   - The `payments` table stores Stripe transaction details
   - Configure Stripe API keys in your application

5. **Email Configuration**
   - Update email settings in the application configuration
   - Configure SMTP for email notifications

## Database Relationships

```
Users
├── Orders (1:Many)
├── Addresses (1:Many)
├── Cart Items (1:Many)
├── Wishlist (1:Many)
├── Reviews (1:Many)
└── Payments (1:Many)

Products
├── Orders (Many:Many via Order Items)
├── Cart Items (Many:Many)
├── Wishlist (Many:Many)
├── Reviews (1:Many)
├── Images (1:Many)
└── Attributes (1:Many)

Categories
└── Products (1:Many)

Orders
├── Order Items (1:Many)
├── Payments (1:Many)
├── Returns (1:Many)
├── Shipping Address
└── Billing Address
```

## Customization

To modify the schema:

1. Edit the `schema.sql` file
2. Drop the existing database (if needed)
3. Re-run the schema file
4. Update your application code accordingly

For production deployments, use database migrations instead of running the full schema.

## Support

For database-related questions or issues, refer to:
- MySQL Documentation: https://dev.mysql.com/doc/
- Project README: ../README.md

---

**Last Updated**: February 26, 2026
