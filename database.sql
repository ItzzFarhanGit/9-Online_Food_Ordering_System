-- Database creation script for Delight Dining Food Ordering System

CREATE DATABASE IF NOT EXISTS delight_dining;
USE delight_dining;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Admins table
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    rating DECIMAL(2,1) DEFAULT 5.0,
    discount VARCHAR(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    address TEXT NOT NULL,
    notes TEXT,
    payment_method VARCHAR(50) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    delivery_fee DECIMAL(10,2) NOT NULL,
    service_charge DECIMAL(10,2) NOT NULL,
    discount_amount DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    status VARCHAR(50) DEFAULT 'Placed', -- 'Placed', 'Preparing', 'Out for Delivery', 'Delivered', 'Cancelled'
    whatsapp_sent TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Order items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 0;
DELETE FROM products;
ALTER TABLE products AUTO_INCREMENT = 1;
SET FOREIGN_KEY_CHECKS = 1;

INSERT INTO products (name, description, price, image, category, rating, discount) VALUES
('Italian Pizza', 'Freshly baked pizza topped with rich mozzarella cheese, savory tomato sauce, pepperoni, bell peppers, and olives.', 2450.00, 'Italian-pizza.jpg', 'Pizza', 5.0, '-20%'),
('Classic Cheese Pizza', 'Simple yet delicious pizza topped with pure melted mozzarella, fresh tomato paste, and wild oregano.', 1850.00, 'Cheese-pizza.jpg', 'Pizza', 4.8, 'NEW'),
('Double Burger', 'Double flame-grilled beef patty, double melted cheddar cheese, lettuce, tomatoes, and our signature sauce.', 1550.00, 'Double-burger.jpg', 'Burger', 5.0, '-15%'),
('Crispy Chicken Burger', 'Golden-fried crispy chicken breast fillet, creamy mayonnaise, lettuce, toasted sesame buns.', 1250.00, 'Chicken Burger.png', 'Burger', 4.9, 'NEW'),
('Fried Chicken', 'Crispy, golden-fried chicken pieces seasoned with our signature blend of secret herbs and spices.', 2500.00, 'Fried-Chicken.jpg', 'Chicken', 5.0, '-10%'),
('Fried Chicken Bucket', 'A family bucket of crispy, juicy fried chicken, perfect for sharing. Comes with golden French fries.', 2500.00, 'Fried-chicken-bucket.jpg', 'Chicken', 5.0, '-25%'),
('Spicy Roasted Chicken', 'Slow-roasted chicken breast marinated in hot Asian spices, fresh ginger, garlic, and coriander.', 2200.00, 'Chicken.jpg', 'Chicken', 4.8, '-15%'),
('Seafood Pasta', 'Al dente pasta tossed with fresh shrimp, calamari, mussels, and a rich garlic-herb marinara sauce.', 2850.00, 'Sea food pasta.jpg', 'Pasta', 5.0, 'NEW'),
('Creamy Mushroom Pasta', 'White sauce pasta with sauteed wild mushrooms, fresh Parmesan cheese, parsley, and garlic butter.', 2150.00, 'Pasta.jpg', 'Pasta', 4.7, '-10%'),
('Ice Cold Fruit Drink', 'A refreshing blend of sweet citrus fruits, tropical pineapple, fresh mint leaves, served ice cold.', 450.00, 'Drink.jpg', 'Drinks', 4.6, 'NEW'),
('Caramel-Topped Ice Cream Dessert', 'Creamy vanilla ice cream topped with rich, warm caramel sauce and toasted pecans.', 550.00, 'Caramel-Topped Ice Cream Dessert.jpg', 'Desserts', 5.0, '-10%'),
('Chocolate Fudge Cake', 'Rich layered chocolate sponge cake smothered in thick warm fudge, served with chocolate shavings.', 750.00, 'Dessert .jpg', 'Desserts', 4.9, '-15%');

-- Seed default admin
-- Default password: admin123
DELETE FROM admins WHERE username = 'admin';
INSERT INTO admins (username, password) VALUES ('admin', '$2y$10$LGUMntdWmL077M5k84HxweBo7CEEZRYxlJwr0qj5ppzb9P0OmXJm2');
