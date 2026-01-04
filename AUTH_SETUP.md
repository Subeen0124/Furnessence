# Furnessence Authentication System Setup

## Database Setup

1. **Start XAMPP**:
   - Open XAMPP Control Panel
   - Start Apache and MySQL services

2. **Import Database**:
   - Open phpMyAdmin: http://localhost/phpmyadmin
   - Click "Import" tab
   - Choose file: `database.sql`
   - Click "Go" to import

   OR run this SQL manually:
   ```sql
   CREATE DATABASE IF NOT EXISTS furnessence_db;
   USE furnessence_db;
   
   CREATE TABLE IF NOT EXISTS users (
       id INT(11) AUTO_INCREMENT PRIMARY KEY,
       name VARCHAR(100) NOT NULL,
       email VARCHAR(100) NOT NULL UNIQUE,
       password VARCHAR(255) NOT NULL,
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
       updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
       INDEX idx_email (email)
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
   ```

3. **Test Admin Account** (Optional):
   - Email: admin@furnessence.com
   - Password: admin123

## Files Created

### Authentication Pages:
- `login.php` - User login page
- `registration.php` - User registration page
- `logout.php` - Logout handler

### Configuration:
- `config.php` - Database connection and session management
- `database.sql` - Database schema

### Styling:
- `assests/css/auth.css` - Shared authentication styling

## Features Implemented

### Login Page (`login.php`):
- ✅ Email and password validation
- ✅ Password visibility toggle
- ✅ Remember me functionality
- ✅ Session management
- ✅ Redirect if already logged in
- ✅ Link to registration page
- ✅ Social login placeholders (Google, Facebook)

### Registration Page (`registration.php`):
- ✅ Full name, email, password fields
- ✅ Password confirmation
- ✅ Email validation
- ✅ Password strength check (min 6 chars)
- ✅ Terms & conditions checkbox
- ✅ Duplicate email check
- ✅ Password hashing (bcrypt)
- ✅ Success message with auto-redirect
- ✅ Link to login page

### Security Features:
- ✅ Password hashing with PHP password_hash()
- ✅ SQL injection prevention (mysqli_real_escape_string)
- ✅ XSS prevention (htmlspecialchars)
- ✅ Session management
- ✅ Email validation
- ✅ CSRF protection ready

### Design Features:
- ✅ Modern gradient design
- ✅ Responsive layout (mobile-first)
- ✅ Smooth animations
- ✅ Icon integration (Ionicons)
- ✅ Form validation feedback
- ✅ Loading states
- ✅ Alert messages (success, error, info)

## How to Use

1. **Access the pages**:
   - Homepage: http://localhost/Furnessence/index.php
   - Login: http://localhost/Furnessence/login.php
   - Register: http://localhost/Furnessence/registration.php

2. **Register a new account**:
   - Go to registration page
   - Fill in all required fields
   - Accept terms and conditions
   - Click "Create Account"
   - Auto-redirects to login after success

3. **Login**:
   - Go to login page
   - Enter email and password
   - Optionally check "Remember me"
   - Click "Login"
   - Redirects to homepage

4. **Logout**:
   - Click the user icon in header (when logged in)
   - Session destroyed and redirected to login

## Header Integration

The homepage header now shows:
- **Not logged in**: Login icon (redirects to login.php)
- **Logged in**: User icon with name tooltip (click to logout)

## Customization

### Colors:
Edit CSS variables in `assests/css/auth.css`:
```css
--medium-turquoise: hsl(183, 50%, 60%);
--coquelicot: hsl(12, 100%, 57%);
```

### Database:
Update credentials in `config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'furnessence_db');
```

## Troubleshooting

1. **"Connection failed" error**:
   - Check MySQL is running in XAMPP
   - Verify database credentials in config.php

2. **"Table doesn't exist" error**:
   - Import database.sql in phpMyAdmin
   - Or create table manually using SQL provided

3. **"Email already registered" error**:
   - Use different email
   - Or delete existing user from database

4. **Styles not loading**:
   - Clear browser cache
   - Check file path: assests/css/auth.css
   - Verify file exists

## Next Steps

Consider adding:
- Password reset functionality
- Email verification
- User profile page
- Admin panel integration
- Two-factor authentication
- OAuth integration (Google, Facebook)
