<?php
require_once 'db.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$selected_category = $_GET['category'] ?? 'All';
$search_query = trim($_GET['search'] ?? '');

// Build query
$sql = "SELECT * FROM products WHERE 1=1";
$params = [];

if ($selected_category !== 'All') {
    $sql .= " AND category = ?";
    $params[] = $selected_category;
}

if (!empty($search_query)) {
    $sql .= " AND name LIKE ?";
    $params[] = "%" . $search_query . "%";
}

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    $products = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu | Delight Dinning</title>
    <link rel="stylesheet" href="menu.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        .menu-filter button {
            outline: none;
        }
    </style>
</head>
<body>

    <?php include 'header.php'; ?>

    <!-- ================= PAGE BANNER ================= -->
    <section class="page-banner">
        <div class="container">
            <h1>Our Delicious Menu</h1>
            <p>Freshly prepared meals made with love.</p>
        </div>
    </section>

    <!-- ================= SEARCH ================= -->
    <section class="search-area">
        <div class="container">
            <form action="menu.php" method="GET" class="search-box">
                <input type="hidden" name="category" value="<?php echo htmlspecialchars($selected_category); ?>">
                <input type="text" name="search" placeholder="Search your favorite food..." value="<?php echo htmlspecialchars($search_query); ?>">
                <button type="submit">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </form>
        </div>
    </section>

    <!-- ================= CATEGORY FILTER ================= -->
    <section class="menu-filter">
        <div class="container">
            <button class="<?php echo $selected_category == 'All' ? 'active' : ''; ?>" onclick="location.href='menu.php?category=All<?php echo !empty($search_query) ? '&search='.urlencode($search_query) : ''; ?>'">All</button>
            <button class="<?php echo $selected_category == 'Pizza' ? 'active' : ''; ?>" onclick="location.href='menu.php?category=Pizza<?php echo !empty($search_query) ? '&search='.urlencode($search_query) : ''; ?>'">Pizza</button>
            <button class="<?php echo $selected_category == 'Burger' ? 'active' : ''; ?>" onclick="location.href='menu.php?category=Burger<?php echo !empty($search_query) ? '&search='.urlencode($search_query) : ''; ?>'">Burger</button>
            <button class="<?php echo $selected_category == 'Chicken' ? 'active' : ''; ?>" onclick="location.href='menu.php?category=Chicken<?php echo !empty($search_query) ? '&search='.urlencode($search_query) : ''; ?>'">Chicken</button>
            <button class="<?php echo $selected_category == 'Pasta' ? 'active' : ''; ?>" onclick="location.href='menu.php?category=Pasta<?php echo !empty($search_query) ? '&search='.urlencode($search_query) : ''; ?>'">Pasta</button>
            <button class="<?php echo $selected_category == 'Drinks' ? 'active' : ''; ?>" onclick="location.href='menu.php?category=Drinks<?php echo !empty($search_query) ? '&search='.urlencode($search_query) : ''; ?>'">Drinks</button>
            <button class="<?php echo $selected_category == 'Desserts' ? 'active' : ''; ?>" onclick="location.href='menu.php?category=Desserts<?php echo !empty($search_query) ? '&search='.urlencode($search_query) : ''; ?>'">Desserts</button>
        </div>
    </section>

    <!-- ================= FOOD GRID ================= -->
    <section class="foods">
        <div class="container">
            <div class="food-grid">
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $product): ?>
                        <div class="food-card">
                            <?php if (!empty($product['discount'])): ?>
                                <span class="discount"><?php echo htmlspecialchars($product['discount']); ?></span>
                            <?php endif; ?>
                            <div class="favorite">
                                <i class="fa-solid fa-heart"></i>
                            </div>
                            <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <div class="food-info">
                                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                                <div class="rating" style="color: #ffc107;">
                                    ★★★★★
                                </div>
                                <p class="price">
                                    Rs. <?php echo number_format($product['price']); ?>
                                </p>
                                <div class="buttons">
                                    <a href="food-details.php?id=<?php echo $product['id']; ?>" class="details">
                                        Details
                                    </a>
                                    <a href="cart-action.php?action=add&id=<?php echo $product['id']; ?>" class="cart-btn">
                                        Add Cart
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="text-align: center; width: 100%; grid-column: 1 / -1; padding: 40px; font-size: 18px; color: #666;">
                        No dishes found matching your selection.
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?>

</body>
</html>
