# Quick Start Guide - Furnessence Admin & Stock System

## 🚀 What Has Been Created

### ✅ Complete Admin Panel
1. **Admin Login System** (`Admin/Adminlogin.php`)
   - Secure authentication
   - Default credentials: admin / admin123

2. **Admin Dashboard** (`Admin/dashboard.php`)
   - Statistics overview
   - Low stock alerts
   - Recent orders
   - Quick access to all features

3. **Product Management**
   - Add products with images (`Admin/add_product.php`)
   - Edit products (`Admin/edit_product.php`)
   - View all products with filters (`Admin/manage_products.php`)
   - Stock tracking per product

4. **Category Management** (`Admin/manage_categories.php`)
   - Create/delete categories
   - View products per category

5. **Order Management** (`Admin/manage_orders.php`)
   - View all orders
   - Update order status

6. **User Management** (`Admin/manage_users.php`)
   - View registered users

### ✅ Stock Management System

**Features:**
- ✅ Stock quantity tracking
- ✅ Low stock threshold (customizable per product)
- ✅ Stock status badges (In Stock, Low Stock, Out of Stock)
- ✅ Automatic stock decrease when orders are placed
- ✅ Stock validation when adding to cart
- ✅ Transaction-based order processing (rollback on error)

**How It Works:**
1. Admin adds product with initial stock quantity
2. Customer adds product to cart → System validates stock availability
3. Customer places order → Stock decreases automatically
4. Admin sees low stock alerts in dashboard
5. Admin can manually update stock anytime

### ✅ Database Schema Updated
- ✅ Products table with stock fields
- ✅ Orders and order_items tables
- ✅ Categories table
- ✅ Admins table (separate from users)
- ✅ Cart and wishlist tables with foreign keys

### ✅ Separate CSS Files
- ✅ `assests/css/admin.css` - Complete admin panel styling
- ✅ `assests/css/auth.css` - Authentication pages
- ✅ `assests/css/cart.css` - Shopping cart
- ✅ `assests/css/wishlist.css` - Wishlist
- ✅ `assests/css/profile.css` - User profile
- ✅ `assests/css/checkout.css` - Checkout process
- ✅ `assests/css/style.css` - Main website styles

## 📋 Setup Instructions

### Step 1: Import Database
```bash
1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Select or create database: furnessence_db
3. Go to Import tab
4. Choose file: database.sql
5. Click "Go"
```

### Step 2: Test Admin Access
```bash
1. Navigate to: http://localhost/Furnessence/Admin/Adminlogin.php
2. Login with:
   Username: admin
   Password: admin123
```

### Step 3: Add Your First Product
```bash
1. Login to admin panel
2. Click "Add Product" in sidebar
3. Fill in:
   - Product Name
   - Category (optional)
   - Price
   - Stock Quantity (e.g., 50)
   - Low Stock Threshold (e.g., 10)
   - Upload Image
   - Description
4. Click "Add Product"
```

### Step 4: Test Stock System
```bash
# Test 1: Add to Cart with Sufficient Stock
1. Go to main website
2. Add product to cart
3. ✅ Should work normally

# Test 2: Low Stock Warning
1. In admin, edit a product
2. Set stock to 5, threshold to 10
3. Try adding to cart
4. ✅ Should show "Only 5 left!" message

# Test 3: Out of Stock
1. In admin, set stock to 0
2. Try adding to cart
3. ✅ Should show "Product is out of stock"

# Test 4: Order Placement
1. Add products to cart
2. Complete checkout
3. Check admin dashboard
4. ✅ Stock should be decreased
5. ✅ Order should appear in "Manage Orders"
```

## 🎯 Stock Logic Flow

```
Customer Side:
─────────────
Add to Cart
    ↓
Check Stock Available?
    ├── No → Show "Out of stock" error
    ├── Low → Show "Only X left!" warning
    └── Yes → Add to cart

Place Order
    ↓
Validate all cart items stock
    ↓
Create order in database
    ↓
Decrease stock for each item
    ↓
Clear cart
    ↓
Show success message

Admin Side:
──────────
Dashboard
    ↓
Shows:
• Total products
• Low stock alerts (≤ threshold)
• Out of stock count
• Recent orders

Manage Products
    ↓
View stock status badges:
• 🟢 In Stock (green)
• 🟡 Low Stock (yellow)
• 🔴 Out of Stock (red)

Edit Product
    ↓
Can manually adjust:
• Stock quantity
• Low stock threshold
```

## 📁 File Structure

```
Furnessence/
├── Admin/
│   ├── Adminlogin.php          # Login page
│   ├── admin_config.php        # Auth helper
│   ├── dashboard.php           # Main dashboard
│   ├── manage_products.php     # Product list
│   ├── add_product.php         # Add product form
│   ├── edit_product.php        # Edit product form
│   ├── manage_categories.php   # Categories
│   ├── manage_orders.php       # Orders list
│   ├── manage_users.php        # Users list
│   ├── logout.php              # Logout handler
│   ├── README.md               # Detailed admin guide
│   └── includes/
│       ├── sidebar.php         # Navigation sidebar
│       └── header.php          # Page header
│
├── assests/
│   ├── css/
│   │   ├── admin.css          # ⭐ Admin panel styles
│   │   ├── style.css          # Main website
│   │   ├── auth.css           # Login/Register
│   │   ├── cart.css           # Shopping cart
│   │   ├── wishlist.css       # Wishlist
│   │   ├── profile.css        # User profile
│   │   └── checkout.css       # Checkout
│   └── images/
│       └── products/          # Product uploads folder
│
├── config.php                  # Database config
├── database.sql               # ⭐ Complete database schema
├── index.php                  # Main website
├── cart.php                   # Shopping cart page
├── cart_wishlist_handler.php  # ⭐ Cart handler with stock logic
├── checkout.php               # ⭐ Checkout with stock decrease
└── [other files...]
```

## 🔑 Key Features

### 1. Stock Validation
- ✅ Checks stock before adding to cart
- ✅ Prevents overselling
- ✅ Shows availability status

### 2. Transaction Safety
- ✅ Uses database transactions
- ✅ Rolls back if any item fails
- ✅ Ensures data consistency

### 3. Real-time Updates
- ✅ Stock decreases immediately on order
- ✅ Dashboard shows current stock status
- ✅ Low stock alerts

### 4. Admin Permissions
- ✅ Only admins can add/edit products
- ✅ Separate admin authentication
- ✅ Protected admin pages

## 🎨 Customization

### Change Stock Colors
Edit `assests/css/admin.css`:
```css
.stock-badge.in-stock {
    background: #d4edda;    /* Light green */
    color: #155724;         /* Dark green */
}

.stock-badge.low-stock {
    background: #fff3cd;    /* Light yellow */
    color: #856404;         /* Dark yellow */
}

.stock-badge.out-of-stock {
    background: #f8d7da;    /* Light red */
    color: #721c24;         /* Dark red */
}
```

### Change Low Stock Threshold Default
Edit `Admin/add_product.php` line with threshold input:
```php
value="<?php echo isset($_POST['low_stock_threshold']) ? htmlspecialchars($_POST['low_stock_threshold']) : '10'; ?>"
```
Change `'10'` to your preferred default.

### Add New Admin User
Run in phpMyAdmin:
```sql
INSERT INTO admins (username, email, password, full_name) 
VALUES ('yourusername', 'your@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Your Name');
```
Password will be: admin123 (change after login)

## 🐛 Troubleshooting

### Can't Upload Images
```bash
# Check folder exists and has permissions:
assests/images/products/

# In Windows (XAMPP):
# Folder should already be created
# If issues, check file upload settings in php.ini:
upload_max_filesize = 64M
post_max_size = 64M
```

### Stock Not Decreasing
```bash
# Check:
1. Database has orders and order_items tables
2. PHP errors in error log
3. Transaction is completing (check orders table)
```

### Admin Login Not Working
```bash
# Verify:
1. Database imported correctly
2. admins table exists and has data
3. config.php has correct database credentials
4. Session is starting (check config.php)
```

## 📞 Support

### Check Logs
- PHP errors: `C:\xampp\apache\logs\error.log`
- Database: phpMyAdmin → Check tables
- Browser console: F12 → Console tab

### Common Issues
1. **"Table doesn't exist"** → Import database.sql again
2. **"Can't login"** → Clear browser cache/cookies
3. **"Images not showing"** → Check image path in database
4. **"Stock not updating"** → Check transaction errors in PHP log

---

## 🎉 You're All Set!

Your complete admin system with stock management is ready to use!

**Next Steps:**
1. Import database
2. Login to admin panel
3. Add your products
4. Test stock functionality
5. Customize as needed

**Admin URL:** `http://localhost/Furnessence/Admin/Adminlogin.php`

Happy coding! 🚀
