-- Furnessence Database Setup
-- Run this SQL file in phpMyAdmin or MySQL command line

-- Create database
CREATE DATABASE IF NOT EXISTS furnessence;
USE furnessence;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create products table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock_quantity INT DEFAULT 0,
    image VARCHAR(255),
    category_id INT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Create orders table
CREATE TABLE IF NOT EXISTS orders (
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
);

-- Create order_items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Insert admin user (password: admin123)
INSERT IGNORE INTO users (username, email, password, status) VALUES
('admin', 'admin@furnessence.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active');

-- Insert sample categories
INSERT IGNORE INTO categories (name, description) VALUES
('Living Room', 'Furniture for living rooms'),
('Bedroom', 'Bedroom furniture and decor'),
('Kitchen', 'Kitchen furniture and accessories'),
('Office', 'Office furniture and supplies');

-- Insert sample products
INSERT IGNORE INTO products (name, price, image, category_id) VALUES
('Animi Dolor Pariatur', 10.00, './assets/images/product-1.jpg', 1),
('Art Deco Home', 30.00, './assets/images/product-2.jpg', 1),
('Artificial potted plant', 40.00, './assets/images/product-3.jpg', 2),
('Dark Green Jug', 17.10, './assets/images/product-4.jpg', 3),
('Drinking Glasses', 21.00, './assets/images/product-5.jpg', 3),
('Helen Chair', 69.50, './assets/images/product-6.jpg', 1),
('High Quality Glass Bottle', 30.10, './assets/images/product-7.jpg', 3),
('Living Room & Bedroom Lights', 45.00, './assets/images/product-8.jpg', 2),
('Nancy Chair', 90.00, './assets/images/product-9.jpg', 1),
('Simple Chair', 40.00, './assets/images/product-10.jpg', 1),
('Smooth Disk', 46.00, './assets/images/product-11.jpg', 4),
('Table Black', 67.00, './assets/images/product-12.jpg', 4),
('Table Wood Pine', 50.00, './assets/images/product-13.jpg', 4),
('Teapot with black tea', 25.00, './assets/images/product-14.jpg', 3),
('Unique Decoration', 15.00, './assets/images/product-15.jpg', 2),
('Vase Of Flowers', 77.00, './assets/images/product-16.jpg', 2),
('Wood Eggs', 19.00, './assets/images/product-17.jpg', 2),
('Wooden Box', 27.00, './assets/images/product-18.jpg', 4),
('Wooden Cups', 29.00, './assets/images/product-19.jpg', 3);
