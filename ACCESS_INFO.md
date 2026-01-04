# 🔐 Access Information - Furnessence

## 🌐 URLs

### Installation & Setup
```
http://localhost/Furnessence/install_check.php
```
Use this first to verify your installation is complete.

### Admin Panel
```
http://localhost/Furnessence/Admin/Adminlogin.php
```

### Main Website
```
http://localhost/Furnessence/index.php
```

### Other Pages
```
Cart:      http://localhost/Furnessence/cart.php
Wishlist:  http://localhost/Furnessence/wishlist.php
Checkout:  http://localhost/Furnessence/checkout.php
Profile:   http://localhost/Furnessence/profile.php
Login:     http://localhost/Furnessence/login.php
Register:  http://localhost/Furnessence/registration.php
```

## 🔑 Default Credentials

### Admin Account
```
URL:      http://localhost/Furnessence/Admin/Adminlogin.php
Username: admin
Password: admin123
```

**⚠️ SECURITY WARNING:**
Change this password immediately after first login!

### Test User Account (Optional)
Create via registration page or use SQL:
```sql
INSERT INTO users (name, email, password) 
VALUES ('Test User', 'test@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
-- Password: admin123
```

## 📊 Admin Panel Features

### Dashboard
- View statistics (products, users, orders, revenue)
- Low stock alerts
- Recent orders
- Quick navigation

### Product Management
- **Add Product:** Admin/add_product.php
- **Manage Products:** Admin/manage_products.php
- **Edit Product:** Admin/edit_product.php?id={product_id}
- **Stock Management:** Built into edit product

### Category Management
- **Manage Categories:** Admin/manage_categories.php
- Add/delete categories
- View products per category

### Order Management
- **View Orders:** Admin/manage_orders.php
- **View Order Details:** Admin/view_order.php?id={order_id}
- Update order status

### User Management
- **View Users:** Admin/manage_users.php
- See user details and order count

## 🗄️ Database Information

### Database Name
```
furnessence_db
```

### Connection Settings (config.php)
```php
DB_HOST: localhost
DB_USER: root
DB_PASS: (empty)
DB_NAME: furnessence_db
```

### Database Tables
```
admins          - Admin user accounts
users           - Customer accounts
products        - Product catalog with stock
categories      - Product categories
cart            - Shopping cart items
wishlist        - User wishlists
orders          - Order records
order_items     - Order line items
```

### Sample Data Included
✅ 1 Admin user (admin/admin123)
✅ 4 Categories (Living Room, Bedroom, Office, Dining)
✅ 8 Sample products with stock

## 📁 Important Files

### Configuration
```
config.php              - Database configuration
```

### Admin Files
```
Admin/
├── Adminlogin.php      - Admin login
├── admin_config.php    - Auth helper
├── dashboard.php       - Main dashboard
├── manage_products.php - Product list
├── add_product.php     - Add product
├── edit_product.php    - Edit product
├── manage_categories.php - Categories
├── manage_orders.php   - Orders
├── view_order.php      - Order details
├── manage_users.php    - Users
└── logout.php          - Logout
```

### User-Facing Files
```
index.php               - Homepage
cart.php                - Shopping cart
wishlist.php            - Wishlist
checkout.php            - Checkout (stock decreases here)
profile.php             - User profile
login.php               - User login
registration.php        - User registration
cart_wishlist_handler.php - AJAX handler (stock validation here)
```

### CSS Files
```
assests/css/
├── admin.css          - Admin panel styles
├── style.css          - Main website styles
├── auth.css           - Login/register styles
├── cart.css           - Cart styles
├── wishlist.css       - Wishlist styles
├── profile.css        - Profile styles
└── checkout.css       - Checkout styles
```

### Documentation
```
ADMIN_QUICK_START.md   - Quick start guide
Admin/README.md        - Detailed admin guide
PROJECT_COMPLETE.md    - Complete feature list
ACCESS_INFO.md         - This file
```

## 🛠️ Quick Setup Checklist

- [ ] XAMPP installed and running
- [ ] Apache and MySQL started
- [ ] Database created: `furnessence_db`
- [ ] `database.sql` imported via phpMyAdmin
- [ ] Visited: `install_check.php` (all checks pass)
- [ ] Logged into admin panel
- [ ] Changed default admin password
- [ ] Added first product
- [ ] Tested adding to cart
- [ ] Tested placing order
- [ ] Verified stock decreased

## 🔄 Common Admin Tasks

### Add a Product
```
1. Login to admin panel
2. Click "Add Product" or go to Admin/add_product.php
3. Fill form:
   - Name, category, price
   - Stock quantity
   - Low stock threshold
   - Upload image
4. Click "Add Product"
```

### Update Stock
```
1. Go to Admin/manage_products.php
2. Click edit icon on product
3. Update "Stock Quantity" field
4. Save changes
```

### Process an Order
```
1. Go to Admin/manage_orders.php
2. Find order
3. Change status dropdown:
   - Pending → Processing → Completed
4. Automatically saves on change
```

### View Low Stock Items
```
1. Go to Admin/dashboard.php
2. Scroll to "Low Stock Alert" table
3. See products ≤ threshold
4. Click edit icon to update stock
```

## 🎯 Testing Stock Logic

### Test 1: Normal Purchase
```
1. Go to website
2. Add product to cart (stock = 10)
3. Complete checkout
4. Check admin → Stock now = 9 ✅
```

### Test 2: Low Stock Warning
```
1. Admin: Set product stock to 5
2. Admin: Set threshold to 10
3. Website: Add to cart
4. See: "Only 5 left!" ✅
```

### Test 3: Out of Stock
```
1. Admin: Set product stock to 0
2. Website: Try adding to cart
3. See: "Product is out of stock" ✅
```

### Test 4: Insufficient Stock
```
1. Product has stock = 3
2. Add 2 to cart
3. Try adding 2 more
4. See: "Only 3 items in stock" ✅
```

## 🔒 Security Notes

### Password Hashing
All passwords use PHP's `password_hash()` with bcrypt algorithm.

### SQL Injection Protection
All queries use `mysqli_real_escape_string()` or prepared statements.

### Session Security
- Sessions started in config.php
- Admin routes protected with `requireAdminLogin()`
- Session data validated on each request

### File Upload Security
- File type validation (JPG, PNG, GIF, WEBP only)
- Unique filename generation
- Upload to non-executable directory

## 📞 Troubleshooting

### Can't Login to Admin
```
1. Check database imported: SELECT * FROM admins;
2. Clear browser cookies
3. Try username: admin, password: admin123
4. Check config.php database settings
```

### Images Not Uploading
```
1. Check folder exists: assests/images/products/
2. Check folder permissions (XAMPP: usually OK)
3. Check PHP settings:
   - upload_max_filesize = 64M
   - post_max_size = 64M
```

### Stock Not Decreasing
```
1. Check order created in orders table
2. Check PHP error log: C:\xampp\apache\logs\error.log
3. Verify transaction completing
4. Check browser console for JS errors
```

### "Table doesn't exist" Error
```
1. Import database.sql again
2. Check database name in config.php
3. Verify all 8 tables created
```

## 📧 Support

### Check Logs
- PHP errors: `C:\xampp\apache\logs\error.log`
- MySQL errors: phpMyAdmin → Check queries
- Browser: F12 → Console tab

### Database Issues
- Use phpMyAdmin: `http://localhost/phpmyadmin`
- Check table structure matches database.sql
- Verify foreign keys created

### File Permissions
- Windows (XAMPP): Usually automatic
- If issues: Check folder properties → Security

## 🎉 You're All Set!

Everything you need is documented above. Start by:
1. Visiting `install_check.php`
2. Logging into admin panel
3. Adding your first product
4. Testing the stock system

Happy coding! 🚀
