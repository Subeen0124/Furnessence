# Furnessence 🛋️

A modern e-commerce platform for furniture shopping built with PHP and MySQL. Furnessence provides a seamless shopping experience with user authentication, shopping cart, wishlist functionality, and admin management.

## 🌟 Features

### User Features
- **User Authentication**: Secure registration, login, and logout system
- **Product Browsing**: Browse through a wide selection of furniture items
- **Shopping Cart**: Add, update, and remove items from cart
- **Wishlist**: Save favorite items for later
- **User Profile**: Manage personal information and view order history
- **Checkout System**: Secure checkout process for placing orders

### Admin Features
- **Admin Dashboard**: Comprehensive admin panel for managing the store
- **Product Management**: Add, edit, and delete products
- **Order Management**: View and process customer orders
- **User Management**: Manage customer accounts

## 🚀 Getting Started

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- XAMPP/WAMP/MAMP (recommended for local development)

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/Subeen0124/Furnessence.git
   cd Furnessence
   ```

2. **Set up the database**
   - Create a new MySQL database
   - Import the `database.sql` file into your database
   ```bash
   mysql -u your_username -p your_database_name < database.sql
   ```

3. **Configure database connection**
   - Open `config.php`
   - Update the database credentials:
   ```php
   $servername = "localhost";
   $username = "your_username";
   $password = "your_password";
   $dbname = "your_database_name";
   ```

4. **Set up authentication** (if using OAuth/Social Login)
   - Follow the instructions in `AUTH_SETUP.md` for detailed authentication setup

5. **Start the server**
   - If using XAMPP/WAMP, place the project in `htdocs` folder
   - Access the application at `http://localhost/Furnessence`

## 📁 Project Structure

```
Furnessence/
├── Admin/                      # Admin panel files
├── assests/                    # Static assets (CSS, JS, images)
├── AUTH_SETUP.md              # Authentication setup guide
├── cart.php                   # Shopping cart page
├── cart_wishlist_handler.php  # Cart and wishlist logic
├── checkout.php               # Checkout process
├── config.php                 # Database configuration
├── database.sql               # Database schema
├── index.php                  # Homepage
├── login.php                  # User login
├── logout.php                 # User logout
├── profile.php                # User profile page
├── registration.php           # User registration
└── wishlist.php               # Wishlist page
```

## 🔐 Security Features

- Password hashing for secure storage
- Session management for user authentication
- SQL injection prevention
- XSS protection

## 🛠️ Technologies Used

- **Backend**: PHP
- **Database**: MySQL
- **Frontend**: HTML5, CSS3, JavaScript
- **Server**: Apache

## 📖 Usage

### For Customers
1. Register for a new account or login
2. Browse products on the homepage
3. Add items to cart or wishlist
4. Proceed to checkout when ready
5. Manage your profile and view order history

### For Administrators
1. Login with admin credentials
2. Access the admin dashboard
3. Manage products, orders, and users
4. Monitor store activity

## 🤝 Contributing

This is a team collaboration project. To contribute:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 👥 Team

- **Project Lead**: [Subeen0124](https://github.com/Subeen0124)

## 📝 License

This project is available for educational and development purposes.

## 🐛 Issues

If you encounter any issues or have suggestions, please [open an issue](https://github.com/Subeen0124/Furnessence/issues).

## 📧 Contact

For any queries, reach out through [GitHub](https://github.com/Subeen0124).

---

**Made with ❤️ by the Furnessence Team**