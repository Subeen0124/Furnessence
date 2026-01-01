# Furnessence

A modern furniture e-commerce platform built with PHP and MySQL. Furnessence provides a seamless shopping experience for furniture enthusiasts with user authentication, product browsing, and secure checkout features.

## 🌟 Features

- **User Authentication**: Secure registration and login system
- **Product Catalog**: Browse through a curated collection of furniture items
- **Shopping Cart**: Add, update, and manage items in your cart
- **Checkout Process**: Streamlined checkout with order processing
- **Responsive Design**: Mobile-friendly interface with custom CSS styling
- **Database Integration**: MySQL backend for data persistence

## 🛠️ Tech Stack

- **Backend**: PHP
- **Database**: MySQL
- **Frontend**: HTML, CSS, JavaScript
- **Server**: Apache/Nginx (XAMPP, WAMP, or similar)

## 📋 Prerequisites

Before you begin, ensure you have the following installed:

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- XAMPP, WAMP, or MAMP (recommended for local development)

## 🚀 Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/Subeen0124/Furnessence.git
   cd Furnessence
   ```

2. **Set up the database**
   - Create a MySQL database named `furnessence`
   - Import the database schema (if provided)
   - Update database credentials in `config.php` if needed

3. **Configure the application**
   - Open `config.php`
   - Update the database configuration:
     ```php
     define('DB_SERVER', 'localhost');
     define('DB_USERNAME', 'root');
     define('DB_PASSWORD', '');
     define('DB_NAME', 'furnessence');
     ```

4. **Start your local server**
   - If using XAMPP: Place the project in `htdocs` folder and start Apache
   - If using WAMP: Place the project in `www` folder and start services
   - If using PHP built-in server:
     ```bash
     php -S localhost:8000
     ```

5. **Access the application**
   - Open your browser and navigate to `http://localhost/Furnessence` or `http://localhost:8000`

## 📁 Project Structure

```
Furnessence/
├── index.php           # Main landing page
├── login.php          # User login page
├── registration.php   # User registration page
├── checkout.php       # Checkout process
├── product-cart.php   # Shopping cart functionality
├── navbar.php         # Navigation component
├── config.php         # Database configuration
├── style.css          # Main stylesheet
└── README.md          # Project documentation
```

## 💻 Usage

### For Customers:
1. **Register**: Create a new account using the registration page
2. **Login**: Access your account with credentials
3. **Browse**: Explore furniture products on the main page
4. **Add to Cart**: Select items and add them to your shopping cart
5. **Checkout**: Complete your purchase through the checkout process

### For Developers:
- Modify `style.css` for design changes
- Update product listings in `index.php`
- Customize checkout flow in `checkout.php`
- Extend database schema as needed

## 🤝 Contributing

Contributions are welcome! Here's how you can help:

1. Fork the repository
2. Create a new branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

### Contribution Guidelines:
- Follow PHP coding standards (PSR-12)
- Write clear commit messages
- Test your changes before submitting
- Update documentation as needed

## 🔒 Security

- Passwords are securely hashed
- SQL injection prevention measures implemented
- Session management for user authentication
- Input validation and sanitization

**Note**: For production deployment, ensure you:
- Change default database credentials
- Enable HTTPS
- Implement additional security headers
- Regular security audits

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 👥 Team

This is a team collaboration project for development purposes.

## 📧 Contact

**Project Owner**: [Subeen0124](https://github.com/Subeen0124)

**Project Link**: [https://github.com/Subeen0124/Furnessence](https://github.com/Subeen0124/Furnessence)

## 🙏 Acknowledgments

- Thanks to all team members who contributed to this project
- Inspired by modern e-commerce platforms
- Built with love for furniture enthusiasts

## 🐛 Bug Reports

If you discover any bugs, please create an issue on GitHub with:
- Bug description
- Steps to reproduce
- Expected vs actual behavior
- Screenshots (if applicable)

## 🔄 Changelog

### Version 1.0.0 (2026-01-01)
- Initial release
- User authentication system
- Product catalog
- Shopping cart functionality
- Checkout process

---

**Made with ❤️ by the Furnessence Team**