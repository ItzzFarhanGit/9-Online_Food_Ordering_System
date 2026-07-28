<?php
$host = 'localhost';
$user = 'root';
$pass = '';

try {
    // 1. Connect to MySQL without database first
    $pdo = new PDO("mysql:host=$host", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // 2. Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS delight_dining CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE delight_dining");

    // 3. Create tables
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            phone VARCHAR(50) NOT NULL,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS admins (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    $pdo->exec("
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
    ");

    $pdo->exec("
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
            status VARCHAR(50) DEFAULT 'Placed',
            whatsapp_sent TINYINT(1) NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            product_id INT NOT NULL,
            quantity INT NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // 4. Seed products
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec("TRUNCATE TABLE products");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    $stmt = $pdo->prepare("
        INSERT INTO products (name, description, price, image, category, rating, discount) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $products = [
        ['Italian Pizza', 'Freshly baked pizza topped with rich mozzarella cheese, savory tomato sauce, pepperoni, bell peppers, and olives.', 2450.00, 'Italian-pizza.jpg', 'Pizza', 5.0, '-20%'],
        ['Classic Cheese Pizza', 'Simple yet delicious pizza topped with pure melted mozzarella, fresh tomato paste, and wild oregano.', 1850.00, 'Cheese-pizza.jpg', 'Pizza', 4.8, 'NEW'],
        ['Double Burger', 'Double flame-grilled beef patty, double melted cheddar cheese, lettuce, tomatoes, and our signature sauce.', 1550.00, 'Double-burger.jpg', 'Burger', 5.0, '-15%'],
        ['Crispy Chicken Burger', 'Golden-fried crispy chicken breast fillet, creamy mayonnaise, lettuce, toasted sesame buns.', 1250.00, 'Chicken Burger.png', 'Burger', 4.9, 'NEW'],
        ['Fried Chicken', 'Crispy, golden-fried chicken pieces seasoned with our signature blend of secret herbs and spices.', 2500.00, 'Fried-Chicken.jpg', 'Chicken', 5.0, '-10%'],
        ['Fried Chicken Bucket', 'A family bucket of crispy, juicy fried chicken, perfect for sharing. Comes with golden French fries.', 2500.00, 'Fried-chicken-bucket.jpg', 'Chicken', 5.0, '-25%'],
        ['Spicy Roasted Chicken', 'Slow-roasted chicken breast marinated in hot Asian spices, fresh ginger, garlic, and coriander.', 2200.00, 'Chicken.jpg', 'Chicken', 4.8, '-15%'],
        ['Seafood Pasta', 'Al dente pasta tossed with fresh shrimp, calamari, mussels, and a rich garlic-herb marinara sauce.', 2850.00, 'Sea food pasta.jpg', 'Pasta', 5.0, 'NEW'],
        ['Creamy Mushroom Pasta', 'White sauce pasta with sauteed wild mushrooms, fresh Parmesan cheese, parsley, and garlic butter.', 2150.00, 'Pasta.jpg', 'Pasta', 4.7, '-10%'],
        ['Ice Cold Fruit Drink', 'A refreshing blend of sweet citrus fruits, tropical pineapple, fresh mint leaves, served ice cold.', 450.00, 'Drink.jpg', 'Drinks', 4.6, 'NEW'],
        ['Caramel-Topped Ice Cream Dessert', 'Creamy vanilla ice cream topped with rich, warm caramel sauce and toasted pecans.', 550.00, 'Caramel-Topped Ice Cream Dessert.jpg', 'Desserts', 5.0, '-10%'],
        ['Chocolate Fudge Cake', 'Rich layered chocolate sponge cake smothered in thick warm fudge, served with chocolate shavings.', 750.00, 'Dessert .jpg', 'Desserts', 4.9, '-15%']
    ];

    foreach ($products as $prod) {
        $stmt->execute($prod);
    }

    // 5. Seed Admin
    $pdo->exec("DELETE FROM admins WHERE username = 'admin'");
    $hashed_pass = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt_admin = $pdo->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
    $stmt_admin->execute(['admin', $hashed_pass]);

    $success = true;

} catch (PDOException $e) {
    $success = false;
    $error_msg = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delight Dinning | Database Installer</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #FFF8F2;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }
        .installer-card {
            background: #fff;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            max-width: 500px;
            width: 90%;
            text-align: center;
            border: 1px solid rgba(255, 107, 0, 0.1);
        }
        .icon {
            font-size: 60px;
            margin-bottom: 20px;
        }
        .icon.success { color: #27ae60; }
        .icon.error { color: #e74c3c; }
        h1 {
            font-size: 24px;
            color: #1F2937;
            margin-top: 0;
        }
        p {
            color: #6b7280;
            font-size: 14px;
            line-height: 1.6;
        }
        .details-box {
            background: #f9fafb;
            padding: 15px;
            border-radius: 8px;
            text-align: left;
            margin-top: 20px;
            font-size: 13px;
            border: 1px solid #eee;
        }
        .details-box li {
            margin-bottom: 5px;
        }
        .btn {
            display: inline-block;
            background: #FF6B00;
            color: #fff;
            padding: 12px 30px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            margin-top: 25px;
            transition: 0.3s;
        }
        .btn:hover {
            background: #e05e00;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

    <div class="installer-card">
        <?php if ($success): ?>
            <div class="icon success">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <h1>Database Setup Successful!</h1>
            <p>The <strong>delight_dining</strong> database has been successfully created, structured, and fully seeded with food items and admin details.</p>
            
            <div class="details-box">
                <strong style="display: block; margin-bottom: 8px;">Setup details:</strong>
                <ul>
                    <li>12 Food items added across all categories.</li>
                    <li>Admin account configured: **admin** / **admin123**</li>
                    <li>Tables initialized: users, admins, products, orders, order_items.</li>
                </ul>
            </div>
            
            <a href="menu.php" class="btn">Go to Menu Page</a>
        <?php else: ?>
            <div class="icon error">
                <i class="fa-solid fa-circle-xmark"></i>
            </div>
            <h1>Database Setup Failed</h1>
            <p>We encountered an error while trying to configure your database connection.</p>
            
            <div class="details-box" style="color: #e74c3c; background: #fff5f5; border-color: #fcdede;">
                <strong>Error Details:</strong><br>
                <?php echo htmlspecialchars($error_msg); ?>
            </div>
            
            <p style="margin-top: 15px; font-size: 12px;">Make sure your MySQL server (XAMPP/WAMP) is running with default username <strong>root</strong> and blank password.</p>
            <a href="setup-db.php" class="btn" style="background: #777;">Try Again</a>
        <?php endif; ?>
    </div>

</body>
</html>
