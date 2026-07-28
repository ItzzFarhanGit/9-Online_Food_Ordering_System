<?php
require_once 'db.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$id = (int)($_GET['id'] ?? 0);
$product = null;

if ($id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch();
    } catch (PDOException $e) {
        // Handle error or fallback
    }
}

if (!$product) {
    // If product not found, redirect to menu or show error
    $_SESSION['success_msg'] = 'Product not found.';
    header("Location: menu.php");
    exit;
}

// Calculate original price if discount is percentage
$original_price = null;
if (!empty($product['discount']) && strpos($product['discount'], '%') !== false) {
    $pct = (int)filter_var($product['discount'], FILTER_SANITIZE_NUMBER_INT);
    $pct = abs($pct);
    if ($pct > 0 && $pct < 100) {
        $original_price = $product['price'] / (1 - ($pct / 100));
    }
}

// Format category class or name
$category = htmlspecialchars($product['category']);
$rating = (float)($product['rating'] ?? 5.0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> | Delight Dinning</title>
    <link rel="stylesheet" href="food-details.css">
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>
<body>

    <?php include 'header.php'; ?>

    <main class="details-section">
        <div class="container">
            <!-- Breadcrumbs -->
            <div class="breadcrumbs">
                <a href="index.php">Home</a> <i class="fa-solid fa-angle-right"></i> 
                <a href="menu.php">Menu</a> <i class="fa-solid fa-angle-right"></i> 
                <a href="menu.php?category=<?php echo urlencode($product['category']); ?>"><?php echo $category; ?></a> <i class="fa-solid fa-angle-right"></i> 
                <span><?php echo htmlspecialchars($product['name']); ?></span>
            </div>

            <!-- Detail Card -->
            <div class="details-card">
                <!-- Left: Food Image -->
                <div class="details-image-wrapper">
                    <?php if (!empty($product['discount'])): ?>
                        <span class="detail-discount-badge"><?php echo htmlspecialchars($product['discount']); ?></span>
                    <?php endif; ?>
                    <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="details-image">
                </div>

                <!-- Right: Food Info -->
                <div class="details-info">
                    <span class="category-tag"><?php echo $category; ?></span>
                    <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                    
                    <!-- Rating -->
                    <div class="rating-box">
                        <div class="stars">
                            <?php
                            $full_stars = floor($rating);
                            $has_half = ($rating - $full_stars) >= 0.5;
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= $full_stars) {
                                    echo '<i class="fa-solid fa-star"></i>';
                                } elseif ($i == $full_stars + 1 && $has_half) {
                                    echo '<i class="fa-solid fa-star-half-stroke"></i>';
                                } else {
                                    echo '<i class="fa-regular fa-star"></i>';
                                }
                            }
                            ?>
                        </div>
                        <span class="rating-value"><?php echo number_format($rating, 1); ?> Rating</span>
                    </div>

                    <!-- Price Box -->
                    <div class="price-box">
                        <span class="current-price">Rs. <?php echo number_format($product['price']); ?></span>
                        <?php if ($original_price): ?>
                            <span class="original-price">Rs. <?php echo number_format($original_price); ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Description -->
                    <div class="description-box">
                        <h3>Description</h3>
                        <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                    </div>

                    <!-- Delivery features info bar -->
                    <div class="features-bar">
                        <div class="feature-item">
                            <i class="fa-solid fa-truck-fast"></i>
                            <span>Fast Delivery</span>
                        </div>
                        <div class="feature-item">
                            <i class="fa-solid fa-bowl-hot"></i>
                            <span>Served Hot</span>
                        </div>
                        <div class="feature-item">
                            <i class="fa-solid fa-shield-halved"></i>
                            <span>100% Fresh</span>
                        </div>
                    </div>

                    <!-- Action form -->
                    <form action="cart-action.php" method="GET" class="action-form">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                        
                        <div class="qty-label">Quantity:</div>
                        <div class="purchase-controls">
                            <div class="quantity-selector">
                                <button type="button" class="qty-btn minus" onclick="decreaseQty()">
                                    <i class="fa-solid fa-minus"></i>
                                </button>
                                <input type="number" name="qty" id="qty-input" value="1" min="1" readonly>
                                <button type="button" class="qty-btn plus" onclick="increaseQty()">
                                    <i class="fa-solid fa-plus"></i>
                                </button>
                            </div>

                            <button type="submit" class="add-to-cart-btn">
                                <i class="fa-solid fa-cart-shopping"></i> Add to Cart
                            </button>
                        </div>
                    </form>

                    <div class="back-link-wrapper">
                        <a href="menu.php" class="back-to-menu-link">
                            <i class="fa-solid fa-arrow-left"></i> Back to Menu
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'footer.php'; ?>

    <script>
        function decreaseQty() {
            const input = document.getElementById('qty-input');
            let val = parseInt(input.value);
            if (val > 1) {
                input.value = val - 1;
            }
        }
        function increaseQty() {
            const input = document.getElementById('qty-input');
            let val = parseInt(input.value);
            input.value = val + 1;
        }
    </script>
</body>
</html>
