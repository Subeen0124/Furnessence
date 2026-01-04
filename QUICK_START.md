# 🚀 QUICK START - Furnessence E-Commerce

## ⚡ 1-Minute Overview

Your Furnessence project is a **complete furniture e-commerce website** with:
- ✅ Customer shopping interface
- ✅ Admin management panel
- ✅ Database with sample data
- ✅ All files organized and ready

---

## 🎯 Start in 3 Steps

### Step 1: Database Setup (2 minutes)
```
1. Open XAMPP Control Panel
2. Start Apache + MySQL
3. Go to: http://localhost/phpmyadmin
4. Create database: 'furnessence'
5. Import: database_setup.sql
```

### Step 2: Access Website
```
Homepage: http://localhost/Furnessence/index.php
```

### Step 3: Login to Admin
```
URL: http://localhost/Furnessence/Admin/Adminlogin.php
Username: admin
Password: admin123
```

**Done! 🎉**

---

## 📁 Your Project Files

```
Furnessence/
├── 🌐 Customer Pages
│   ├── index.php          → Homepage
│   ├── login.php          → User login
│   ├── registration.php   → Sign up
│   ├── product-cart.php   → Shopping cart
│   └── checkout.php       → Checkout
│
├── 🔧 Admin Panel
│   └── Admin/
│       ├── Adminlogin.php     → Admin login
│       ├── Admindashboard.php → Dashboard
│       ├── manage-products.php → Products
│       ├── manage-orders.php  → Orders
│       └── manage-users.php   → Users
│
├── 📚 Documentation
│   ├── SETUP_GUIDE.md     → Full setup guide
│   ├── PROJECT_SUMMARY.md → Complete overview
│   └── README.md          → Project info
│
├── ⚙️ Configuration
│   ├── config.php         → Database settings
│   └── database_setup.sql → Database schema
│
└── 🎨 Assets
    └── assets/
        ├── css/style.css
        └── images/        → Put product images here
```

---

## 🔑 Key URLs

| What | URL |
|------|-----|
| **Homepage** | http://localhost/Furnessence/ |
| **Admin Login** | http://localhost/Furnessence/Admin/Adminlogin.php |
| **phpMyAdmin** | http://localhost/phpmyadmin |
| **User Login** | http://localhost/Furnessence/login.php |

---

## 👤 Login Credentials

### Admin:
- Username: `admin`
- Password: `admin123`

### Test User:
- Create your own via registration page

---

## 📊 What's Included

### Database:
- ✅ 19 Sample products
- ✅ 4 Categories
- ✅ 1 Admin user
- ✅ Complete schema

### Features:
- ✅ Product browsing
- ✅ Shopping cart
- ✅ User registration/login
- ✅ Admin dashboard
- ✅ Order management
- ✅ Product management

---

## 🎨 Add Product Images

1. Go to: `c:\xampp\htdocs\Furnessence\assets\images\`
2. Add images named: `product-1.jpg`, `product-2.jpg`, etc.
3. Or update paths in admin panel

**Note**: Site works without images, they'll just show as broken links

---

## ⚙️ Database Configuration

**File**: `config.php`

```php
DB_SERVER: localhost
DB_USERNAME: root
DB_PASSWORD: (empty)
DB_NAME: furnessence
```

Change only if your setup differs!

---

## 🐛 Quick Fixes

**Problem**: Page not loading
- ✅ Check Apache is running in XAMPP
- ✅ Verify URL is correct

**Problem**: Can't login to admin
- ✅ Check database was imported
- ✅ Try: http://localhost/Furnessence/test_admin.php

**Problem**: Database error
- ✅ Verify MySQL is running
- ✅ Check config.php credentials

**Problem**: Images not showing
- ✅ Add images to `assets/images/` folder
- ✅ Or ignore - site works without them

---

## 📖 Need More Help?

Read these files in order:
1. **README.md** - Project overview
2. **SETUP_GUIDE.md** - Detailed setup
3. **PROJECT_SUMMARY.md** - Complete documentation

---

## ✅ Checklist

Before you start:
- [ ] XAMPP installed
- [ ] Apache running (green in XAMPP)
- [ ] MySQL running (green in XAMPP)
- [ ] Database 'furnessence' created
- [ ] database_setup.sql imported
- [ ] Visited homepage successfully
- [ ] Admin login works

---

## 🎯 What To Do Next

1. **Explore** admin panel features
2. **Add** product images
3. **Customize** design (edit CSS)
4. **Test** shopping cart
5. **Add** your own products
6. **Make it yours!**

---

## 💡 Pro Tips

- **Bookmark** admin panel URL
- **Change** admin password first!
- **Backup** database regularly
- **Test** before customizing
- **Read** code comments for guidance

---

## 🚀 You're Ready!

Everything is set up and working. Your complete e-commerce site is ready to use, customize, and deploy!

**Need detailed info?** → Read SETUP_GUIDE.md
**Want to learn more?** → Read PROJECT_SUMMARY.md

---

**Happy Coding! 💻🛋️✨**
