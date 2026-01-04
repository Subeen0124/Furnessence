# Furnessence Admin System - Setup Guide

## Features Implemented

### 1. Admin Authentication System
- Secure login with hashed passwords
- Session management
- Admin dashboard access control

### 2. Product Management
- Add new products with images
- Edit existing products
- Delete products
- View all products with filters
- Stock management with real-time tracking

### 3. Stock Management System
- **In Stock**: Products with sufficient quantity
- **Low Stock**: Products at or below threshold (customizable per product)
- **Out of Stock**: Products with zero quantity
- Automatic stock decrease when orders are placed
- Stock validation when adding to cart

### 4. Category Management
- Create categories
- Delete categories
- View products per category

### 5. Order Management
- View all orders
- Update order status (Pending, Processing, Completed, Cancelled)
- View order details

### 6. User Management
- View all registered users
- See order count per user

### 7. Dashboard Statistics
- Total products
- Low stock alerts
- Out of stock items
- Total users
- Total orders
- Total revenue

## Installation Steps

### Step 1: Update Database
1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Select the `furnessence_db` database (or create it if it doesn't exist)
3. Import the `database.sql` file or run it in SQL tab
4. This will create all necessary tables and sample data

### Step 2: Create Upload Directory
The system needs a directory for product images:
```
Furnessence/
  └── assests/
      └── images/
          └── products/  (create this folder)
```

Make sure this folder has write permissions.

### Step 3: Admin Login Credentials
**Default Admin Credentials:**
- Username: `admin`
- Password: `admin123`

**IMPORTANT:** Change this password after first login for security!

### Step 4: Access Admin Panel
Navigate to: `http://localhost/Furnessence/Admin/Adminlogin.php`

## Admin Panel Structure

```
Admin/
├── Adminlogin.php          # Admin login page
├── admin_config.php        # Admin authentication helper
├── dashboard.php           # Main dashboard
├── logout.php              # Logout handler
├── manage_products.php     # Product listing & management
├── add_product.php         # Add new product
├── edit_product.php        # Edit existing product
├── manage_categories.php   # Category management
├── manage_orders.php       # Order management
├── manage_users.php        # User management
└── includes/
    ├── sidebar.php         # Navigation sidebar
    └── header.php          # Page header
```

## Stock Logic Explanation

### Adding to Cart
1. When user adds product to cart, system checks available stock
2. If stock is insufficient, user gets error message
3. If stock is low, user gets warning (e.g., "Only 5 left!")
4. Stock is NOT decreased at this point

### Placing Order
1. When user places order, system validates stock again
2. Creates order in database
3. **Decreases stock quantity** for each product
4. If any product has insufficient stock, entire order is rolled back
5. Transaction ensures data integrity

### Admin View
- **Dashboard**: Shows low stock alerts
- **Products Page**: Color-coded stock status
- **Edit Product**: Can update stock quantity manually

## How to Add Products

1. Login to admin panel
2. Click "Add Product" or go to Products → Add New
3. Fill in product details:
   - **Name**: Product name (required)
   - **Category**: Select category
   - **Price**: Product price (required)
   - **Stock Quantity**: Initial stock (required)
   - **Low Stock Threshold**: Alert threshold (default: 10)
   - **Image**: Upload product image (JPG, PNG, GIF, WEBP)
   - **Description**: Product description
   - **Active**: Check to show on website
4. Click "Add Product"

## How to Manage Stock

### Method 1: Edit Product
1. Go to "Manage Products"
2. Click edit icon on product
3. Update "Stock Quantity" field
4. Save changes

### Method 2: Automatic (via Orders)
- Stock automatically decreases when orders are placed
- System validates stock before order confirmation

## Testing Stock Logic

### Test Scenario 1: Low Stock Warning
1. Edit a product and set stock to 5
2. Set low stock threshold to 10
3. View dashboard - product appears in "Low Stock Alert"
4. Try adding to cart - should show "Only 5 left!" message

### Test Scenario 2: Out of Stock
1. Edit a product and set stock to 0
2. Try adding to cart - should show "Product is out of stock"
3. Dashboard shows product in "Out of Stock" count

### Test Scenario 3: Order Placement
1. Add products to cart
2. Complete checkout process
3. Check product stock in admin - should be decreased
4. Check order in "Manage Orders"

## Security Features

- Password hashing with bcrypt
- SQL injection protection
- Session-based authentication
- Admin-only access to dashboard
- File upload validation
- Transaction support for orders

## Customization

### Change Low Stock Threshold
Edit product and change "Low Stock Threshold" value per product.

### Add New Admin User
Run this SQL in phpMyAdmin:
```sql
INSERT INTO admins (username, email, password, full_name) 
VALUES ('newadmin', 'newadmin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'New Admin');
```
(Password will be: admin123)

### Modify Admin Colors
Edit `assests/css/admin.css` and change CSS variables in `:root`

## Troubleshooting

### Can't Login
- Check database connection in `config.php`
- Verify admin exists in `admins` table
- Clear browser cookies/cache

### Images Not Uploading
- Check folder permissions: `assests/images/products/`
- Verify PHP upload settings in `php.ini`
- Check file size limits

### Stock Not Decreasing
- Check if order is being created in `orders` table
- Verify transaction is completing
- Check for PHP errors in error log

## Support

For issues or questions, check:
1. PHP error log
2. Browser console for JavaScript errors
3. Database queries in phpMyAdmin

---

**Version:** 1.0  
**Created:** January 2026  
**Database:** MySQL/MariaDB  
**PHP Version:** 7.4+
