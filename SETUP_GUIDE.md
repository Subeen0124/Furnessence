# Furnessence E-Commerce Setup Guide

## 📋 Prerequisites

Before you begin, make sure you have:
- **XAMPP** installed (includes Apache, MySQL, and PHP)
- A web browser (Chrome, Firefox, Edge, etc.)
- Basic knowledge of PHP and MySQL

## 🚀 Installation Steps

### Step 1: Place Files in XAMPP
1. Copy the `Furnessence` folder to `c:\xampp\htdocs\`
2. Your project path should be: `c:\xampp\htdocs\Furnessence\`

### Step 2: Start XAMPP Services
1. Open **XAMPP Control Panel**
2. Click **Start** for **Apache** (Web Server)
3. Click **Start** for **MySQL** (Database)
4. Wait for both to show green "Running" status

### Step 3: Create Database
1. Open your browser and go to: `http://localhost/phpmyadmin`
2. Click on **"New"** in the left sidebar
3. Database name: `furnessence`
4. Collation: `utf8mb4_general_ci`
5. Click **"Create"**

### Step 4: Import Database
1. Click on the `furnessence` database you just created
2. Click the **"Import"** tab at the top
3. Click **"Choose File"** button
4. Navigate to `c:\xampp\htdocs\Furnessence\database_setup.sql`
5. Click **"Go"** at the bottom
6. Wait for success message

### Step 5: Verify Database Configuration
Open `c:\xampp\htdocs\Furnessence\config.php` and verify:
```php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');  // Leave empty for default XAMPP
define('DB_NAME', 'furnessence');
```

### Step 6: Access the Application

#### Customer/User Side:
- Homepage: `http://localhost/Furnessence/index.php`
- Login: `http://localhost/Furnessence/login.php`
- Register: `http://localhost/Furnessence/registration.php`
- Cart: `http://localhost/Furnessence/product-cart.php`
- Checkout: `http://localhost/Furnessence/checkout.php`

#### Admin Panel:
- Admin Login: `http://localhost/Furnessence/Admin/Adminlogin.php`
- **Username**: `admin`
- **Password**: `admin123`

## 👥 Default Admin Credentials

```
Username: admin
Password: admin123
Email: admin@furnessence.com
```

**Important**: Change these credentials after first login!

## 📁 Project Structure

```
Furnessence/
├── index.php              # Homepage with product catalog
├── login.php              # User login
├── registration.php       # User registration
├── product-cart.php       # Shopping cart
├── checkout.php           # Checkout process
├── navbar.php             # Navigation bar
├── config.php             # Database configuration
├── database_setup.sql     # Database schema and sample data
├── setup.php              # Initial setup helper
├── test_admin.php         # Admin test utility
├── README.md              # Project documentation
├── SETUP_GUIDE.md         # This file
│
├── Admin/                 # Admin panel
│   ├── Adminlogin.php     # Admin login
│   ├── Admindashboard.php # Admin dashboard
│   ├── Adminlogout.php    # Admin logout
│   ├── manage-products.php # Product management
│   ├── manage-orders.php  # Order management
│   ├── manage-users.php   # User management
│   ├── manage-categories.php # Category management
│   ├── add-product.php    # Add new product
│   ├── edit-product.php   # Edit product
│   ├── edit-category.php  # Edit category
│   └── reports.php        # Sales reports
│
└── assets/                # Static resources
    ├── css/
    │   └── style.css      # Main stylesheet
    ├── images/            # Product and UI images
    │   ├── product-1.jpg to product-19.jpg
    │   └── [hero images]
    └── js/                # JavaScript files
```

## 🎨 Adding Product Images

The project includes 19 sample products. To add product images:

1. Place your product images in: `c:\xampp\htdocs\Furnessence\assets\images\`
2. Name them: `product-1.jpg`, `product-2.jpg`, etc.
3. Or update the image paths in the database

**Image Requirements:**
- Format: JPG, PNG, or WebP
- Recommended size: 800x800px
- Max file size: 2MB

## 🔧 Testing the Installation

### Test 1: Check Homepage
Visit: `http://localhost/Furnessence/index.php`
- Should see the Furnessence homepage
- Products should be displayed (with or without images)

### Test 2: Register New User
1. Go to: `http://localhost/Furnessence/registration.php`
2. Create a test account
3. Login with your credentials

### Test 3: Admin Login
1. Go to: `http://localhost/Furnessence/Admin/Adminlogin.php`
2. Login with admin/admin123
3. Should see admin dashboard with statistics

### Test 4: Add Product to Cart
1. On homepage, click "Add to Cart" on any product
2. Cart count should increase
3. Go to cart page to verify

## 🐛 Troubleshooting

### Apache Won't Start
- **Problem**: Port 80 or 443 is already in use
- **Solution**: 
  - Close Skype or other apps using these ports
  - Or change Apache port in XAMPP config

### MySQL Won't Start
- **Problem**: Port 3306 is already in use
- **Solution**: 
  - Close other MySQL instances
  - Or change MySQL port in XAMPP config

### Page Shows Error
- Check Apache and MySQL are running in XAMPP
- Verify database name is correct in config.php
- Check database was imported successfully

### Images Not Showing
- Create the folder: `assets/images/`
- Add at least one test image
- Or ignore - the site works without images

### Can't Login to Admin
- Database was imported correctly?
- Try running: `http://localhost/Furnessence/test_admin.php`
- This will show the admin user details

### Blank White Page
- **Check PHP errors**:
  - Open: `c:\xampp\php\php.ini`
  - Find: `display_errors = Off`
  - Change to: `display_errors = On`
  - Restart Apache

## 🔒 Security Notes

For production deployment:
1. Change all default passwords
2. Update `DB_PASSWORD` in config.php
3. Enable HTTPS
4. Add input validation
5. Enable CSRF protection
6. Set proper file permissions
7. Remove test files (test_admin.php, setup.php)

## 📊 Sample Data

The database includes:
- 1 Admin user
- 4 Categories (Living Room, Bedroom, Kitchen, Office)
- 19 Sample products
- No orders or customers (you create those)

## 🎯 Common Tasks

### Add a New Product
1. Login to admin panel
2. Go to "Manage Products"
3. Click "Add New Product"
4. Fill in details and upload image
5. Click "Save"

### View Orders
1. Login to admin panel
2. Go to "Manage Orders"
3. View order details, update status

### Create Categories
1. Login to admin panel
2. Go to "Categories"
3. Add/Edit/Delete categories

## 📞 Need Help?

If you encounter issues:
1. Check this guide again carefully
2. Verify all steps were followed
3. Check XAMPP error logs
4. Review PHP error messages

## ✅ Quick Start Checklist

- [ ] XAMPP installed
- [ ] Apache and MySQL running
- [ ] Database `furnessence` created
- [ ] database_setup.sql imported
- [ ] config.php configured correctly
- [ ] Homepage loads: http://localhost/Furnessence/
- [ ] Admin login works: http://localhost/Furnessence/Admin/Adminlogin.php
- [ ] Can register new user
- [ ] Can add products to cart
- [ ] Can view admin dashboard

## 🎉 You're Done!

Your Furnessence e-commerce site is now ready to use!

Start by:
1. Logging into the admin panel
2. Exploring the dashboard
3. Adding some products
4. Testing the shopping cart
5. Customizing the design

Happy selling! 🛋️✨
