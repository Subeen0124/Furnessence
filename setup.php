<?php
// Database setup script for Furnessence

require_once 'config.php';

$conn = getDBConnection();

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS furnessence";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully or already exists.<br>";
} else {
    echo "Error creating database: " . $conn->error . "<br>";
}

// Select the database
$conn->select_db('furnessence');

// Create users table
$users_table = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($users_table) === TRUE) {
    echo "Users table created successfully.<br>";
} else {
    echo "Error creating users table: " . $conn->error . "<br>";
}

// Create categories table
$categories_table = "CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($categories_table) === TRUE) {
    echo "Categories table created successfully.<br>";
} else {
    echo "Error creating categories table: " . $conn->error . "<br>";
}

// Drop products table if exists (to ensure clean recreation)
$conn->query("DROP TABLE IF EXISTS products");

// Create products table
$products_table = "CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),
    category_id INT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
)";

if ($conn->query($products_table) === TRUE) {
    echo "Products table created successfully.<br>";
} else {
    echo "Error creating products table: " . $conn->error . "<br>";
}

// Create orders table
$orders_table = "CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    shipping_name VARCHAR(100) NOT NULL,
    shipping_email VARCHAR(100) NOT NULL,
    shipping_address TEXT NOT NULL,
    shipping_city VARCHAR(100) NOT NULL,
    shipping_zip VARCHAR(20) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
)";

if ($conn->query($orders_table) === TRUE) {
    echo "Orders table created successfully.<br>";
} else {
    echo "Error creating orders table: " . $conn->error . "<br>";
}

// Create order_items table
$order_items_table = "CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
)";

if ($conn->query($order_items_table) === TRUE) {
    echo "Order items table created successfully.<br>";
} else {
    echo "Error creating order items table: " . $conn->error . "<br>";
}

// Create admin user (for testing)
$admin_password = password_hash('admin123', PASSWORD_DEFAULT);
$admin_sql = "INSERT IGNORE INTO users (username, email, password, status) VALUES ('admin', 'admin@furnessence.com', '$admin_password', 'active')";

if ($conn->query($admin_sql) === TRUE) {
    echo "Admin user created successfully.<br>";
} else {
    echo "Error creating admin user: " . $conn->error . "<br>";
}

// Insert sample categories
$sample_categories = [
    ['name' => 'Living Room', 'description' => 'Furniture for living rooms'],
    ['name' => 'Bedroom', 'description' => 'Bedroom furniture and decor'],
    ['name' => 'Kitchen', 'description' => 'Kitchen furniture and accessories'],
    ['name' => 'Office', 'description' => 'Office furniture and supplies']
];

foreach ($sample_categories as $category) {
    $cat_sql = "INSERT IGNORE INTO categories (name, description) VALUES (?, ?)";
    $stmt = $conn->prepare($cat_sql);
    $stmt->bind_param("ss", $category['name'], $category['description']);
    if ($stmt->execute()) {
        echo "Category '{$category['name']}' inserted.<br>";
    }
    $stmt->close();
}

// Insert sample products
$sample_products = [
    ['name' => 'Animi Dolor Pariatur', 'price' => 10.00, 'image' => './assets/images/product-1.jpg', 'category_id' => 1],
    ['name' => 'Art Deco Home', 'price' => 30.00, 'image' => './assets/images/product-2.jpg', 'category_id' => 1],
    ['name' => 'Artificial potted plant', 'price' => 40.00, 'image' => './assets/images/product-3.jpg', 'category_id' => 2],
    ['name' => 'Dark Green Jug', 'price' => 17.10, 'image' => './assets/images/product-4.jpg', 'category_id' => 3],
    ['name' => 'Drinking Glasses', 'price' => 21.00, 'image' => './assets/images/product-5.jpg', 'category_id' => 3],
    ['name' => 'Helen Chair', 'price' => 69.50, 'image' => './assets/images/product-6.jpg', 'category_id' => 1],
    ['name' => 'High Quality Glass Bottle', 'price' => 30.10, 'image' => './assets/images/product-7.jpg', 'category_id' => 3],
    ['name' => 'Living Room & Bedroom Lights', 'price' => 45.00, 'image' => './assets/images/product-8.jpg', 'category_id' => 2],
    ['name' => 'Nancy Chair', 'price' => 90.00, 'image' => './assets/images/product-9.jpg', 'category_id' => 1],
    ['name' => 'Simple Chair', 'price' => 40.00, 'image' => './assets/images/product-10.jpg', 'category_id' => 1],
    ['name' => 'Smooth Disk', 'price' => 46.00, 'image' => './assets/images/product-11.jpg', 'category_id' => 4],
    ['name' => 'Table Black', 'price' => 67.00, 'image' => './assets/images/product-12.jpg', 'category_id' => 4],
    ['name' => 'Table Wood Pine', 'price' => 50.00, 'image' => './assets/images/product-13.jpg', 'category_id' => 4],
    ['name' => 'Teapot with black tea', 'price' => 25.00, 'image' => './assets/images/product-14.jpg', 'category_id' => 3],
    ['name' => 'Unique Decoration', 'price' => 15.00, 'image' => './assets/images/product-15.jpg', 'category_id' => 2],
    ['name' => 'Vase Of Flowers', 'price' => 77.00, 'image' => './assets/images/product-16.jpg', 'category_id' => 2],
    ['name' => 'Wood Eggs', 'price' => 19.00, 'image' => './assets/images/product-17.jpg', 'category_id' => 2],
    ['name' => 'Wooden Box', 'price' => 27.00, 'image' => './assets/images/product-18.jpg', 'category_id' => 4],
    ['name' => 'Wooden Cups', 'price' => 29.00, 'image' => './assets/images/product-19.jpg', 'category_id' => 3]
];

foreach ($sample_products as $product) {
    $prod_sql = "INSERT IGNORE INTO products (name, price, image, category_id) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($prod_sql);
    $stmt->bind_param("sdsi", $product['name'], $product['price'], $product['image'], $product['category_id']);
    if ($stmt->execute()) {
        echo "Product '{$product['name']}' inserted.<br>";
    }
    $stmt->close();
}

$conn->close();

echo "<br>Database setup completed successfully!";
?>
