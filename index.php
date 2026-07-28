<?php
require_once 'db.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

try {
    $stmt = $pdo->query("SELECT * FROM products LIMIT 3");
    $popular_dishes = $stmt->fetchAll();
} catch (PDOException $e) {
    $popular_dishes = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delight Dinning | Delicious Food Delivered Fast</title>
    <!-- CSS -->
    <link rel="stylesheet" href="style.css">
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>
<body>

    <?php include 'header.php'; ?>

    <!-- ================= HERO ================= -->
    <section class="hero">
        <div class="hero-content">
            <h4>Fresh • Hot • Delicious</h4>
            <h1>
                Taste Happiness<br>
                In Every Bite
            </h1>
            <p>
                Enjoy freshly prepared meals delivered quickly to your doorstep.
            </p>
            <div class="hero-buttons">
                <a href="menu.php" class="btn">Order Now</a>
                <a href="menu.php" class="btn-outline">View Menu</a>
            </div>
        </div>
        <div class="hero-image">
            <img src="food.png" alt="Food">
        </div>
    </section>

    <!-- ================= Categories ================= -->
    <section class="categories">
        <div class="container">
            <h2>Browse Food Categories</h2>
            <div class="category-buttons">
                <a href="menu.php?category=Pizza" class="category-btn">
                    <img src="Cheese-pizza.jpg" alt="Pizza">
                    <h3>Pizza</h3>
                </a>
                <a href="menu.php?category=Burger" class="category-btn">
                    <img src="Chicken Burger.png" alt="Burger">
                    <h3>Burger</h3>
                </a>
                <a href="menu.php?category=Chicken" class="category-btn">
                    <img src="Chicken.jpg" alt="Chicken">
                    <h3>Chicken</h3>
                </a>
                <a href="menu.php?category=Pasta" class="category-btn">
                    <img src="Pasta.jpg" alt="Pasta">
                    <h3>Pasta</h3>
                </a>
                <a href="menu.php?category=Drinks" class="category-btn">
                    <img src="Drink.jpg" alt="Drinks">
                    <h3>Drinks</h3>
                </a>
                <a href="menu.php?category=Desserts" class="category-btn">
                    <img src="Dessert .jpg" alt="Desserts">
                    <h3>Desserts</h3>
                </a>
            </div>
        </div>
    </section>

    <!-- ================= POPULAR FOOD ================= -->
    <section class="popular">
        <div class="container">
            <h2>Popular Dishes</h2>
            <div class="food-grid">
                <?php if (!empty($popular_dishes)): ?>
                    <?php foreach ($popular_dishes as $dish): ?>
                        <div class="food-card">
                            <img src="<?php echo htmlspecialchars($dish['image']); ?>" alt="<?php echo htmlspecialchars($dish['name']); ?>">
                            <h3><?php echo htmlspecialchars($dish['name']); ?></h3>
                            <p>Rs. <?php echo number_format($dish['price']); ?></p>
                            <div class="rating" style="color: #ffc107; font-size: 16px; margin: 5px 0;">
                                ★★★★★
                            </div>
                            <br><br>
                            <a href="menu.php" class="small-btn">Order Now</a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="text-align: center; width: 100%;">No dishes available at the moment.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- ================= FEATURES ================= -->
    <section class="features">
        <div class="container">
            <div class="feature">
                <i class="fa-solid fa-truck-fast"></i>
                <h3>Fast Delivery</h3>
                <p>
                    Hot and fresh meals delivered to your doorstep in the shortest possible time.
                </p>
            </div>
            <div class="feature">
                <i class="fa-solid fa-bowl-food"></i>
                <h3>Fresh Ingredients</h3>
                <p>
                    Every dish is prepared using premium-quality ingredients for exceptional taste.
                </p>
            </div>
            <div class="feature">
                <i class="fa-solid fa-money-bill-wave"></i>
                <h3>Cash on Delivery</h3>
                <p>
                    Pay conveniently with cash when your order arrives at your doorstep.
                </p>
            </div>
        </div>
    </section>

    <!-- ================= TESTIMONIALS ================= -->

    <!-- ================= NEWSLETTER ================= -->
    <?php include 'footer.php'; ?>

</body>
</html>
