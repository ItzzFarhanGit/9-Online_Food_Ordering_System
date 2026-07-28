<?php
require_once 'db.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$cart_items = [];
$subtotal = 0;
$delivery_fee = 350;
$service_charge = 150;
$discount = 0;
$promo_applied = false;
$promo_code = '';

// Handle Promo Code
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['promo_code'])) {
    $promo_code = strtoupper(trim($_POST['promo_code']));
    if ($promo_code === 'WELCOME10') {
        $_SESSION['discount'] = 500;
        $_SESSION['promo_code'] = $promo_code;
    } else {
        $_SESSION['discount_error'] = 'Invalid Promo Code!';
        unset($_SESSION['discount']);
        unset($_SESSION['promo_code']);
    }
}

if (isset($_SESSION['discount'])) {
    $discount = $_SESSION['discount'];
    $promo_applied = true;
    $promo_code = $_SESSION['promo_code'];
}

// Fetch Cart Products
if (!empty($_SESSION['cart'])) {
    $ids = array_keys($_SESSION['cart']);
    // Filter out invalid IDs just in case
    $ids = array_filter($ids, function($id) { return $id > 0; });
    
    if (!empty($ids)) {
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        try {
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
            $stmt->execute(array_values($ids));
            $products = $stmt->fetchAll();
            
            foreach ($products as $product) {
                $qty = $_SESSION['cart'][$product['id']];
                $product['qty'] = $qty;
                $product['item_total'] = $product['price'] * $qty;
                $subtotal += $product['item_total'];
                $cart_items[] = $product;
            }
        } catch (PDOException $e) {
            // Silence or handle db error
        }
    }
}

// Calculate Total
$total = $subtotal > 0 ? ($subtotal + $delivery_fee + $service_charge - $discount) : 0;
if ($total < 0) $total = 0;

// Save calculations to session for checkout
$_SESSION['subtotal'] = $subtotal;
$_SESSION['delivery_fee'] = $delivery_fee;
$_SESSION['service_charge'] = $service_charge;
$_SESSION['discount_amount'] = $discount;
$_SESSION['total_price'] = $total;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart | Delight Dinning</title>
    <link rel="stylesheet" href="cart.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        .qty-controls {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 15px 0;
        }
        .qty-btn {
            background-color: #f3f4f6;
            border: 1px solid #e5e7eb;
            color: #333;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.2s;
        }
        .qty-btn:hover {
            background-color: #FF6B00;
            color: white;
            border-color: #FF6B00;
        }
        .qty-input {
            width: 40px;
            text-align: center;
            border: 1px solid #e5e7eb;
            border-radius: 5px;
            padding: 5px;
            font-size: 14px;
        }
        .empty-cart-msg {
            text-align: center;
            padding: 50px 20px;
            background: white;
            border-radius: 20px;
            box-shadow: var(--shadow);
            grid-column: 1 / -1;
        }
        .empty-cart-msg h2 {
            font-size: 24px;
            margin-bottom: 15px;
            color: #555;
        }
        .empty-cart-msg .btn {
            display: inline-block;
            background: #FF6B00;
            color: white;
            padding: 12px 30px;
            border-radius: 30px;
            margin-top: 15px;
            font-weight: 600;
        }
        .discount-error {
            color: red;
            font-size: 13px;
            margin-top: 5px;
            display: block;
        }
        .discount-success {
            color: green;
            font-size: 13px;
            margin-top: 5px;
            display: block;
        }
    </style>
</head>
<body>

    <?php include 'header.php'; ?>

    <!--================ PAGE TITLE ================-->
    <section class="page-title">
        <div class="container">
            <h1>🛒 Shopping Cart</h1>
            <p>Review your delicious meals before checkout.</p>
        </div>
    </section>

    <!--================ CART ================-->
    <section class="cart-section">
        <div class="container cart-grid">
            
            <?php if (!empty($cart_items)): ?>
                <!--================ LEFT ================-->
                <div class="cart-items">
                    <?php foreach ($cart_items as $item): ?>
                        <!-- ITEM -->
                        <div class="cart-card">
                            <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                            <div class="cart-info">
                                <h2><?php echo htmlspecialchars($item['name']); ?></h2>
                                <p>⭐⭐⭐⭐⭐ (5.0)</p>
                                <h3>Rs. <?php echo number_format($item['price']); ?></h3>
                                
                                <div class="qty-controls">
                                    <button type="button" class="qty-btn" onclick="location.href='cart-action.php?action=update&id=<?php echo $item['id']; ?>&qty=<?php echo $item['qty'] - 1; ?>'">-</button>
                                    <input type="text" class="qty-input" value="<?php echo $item['qty']; ?>" readonly>
                                    <button type="button" class="qty-btn" onclick="location.href='cart-action.php?action=update&id=<?php echo $item['id']; ?>&qty=<?php echo $item['qty'] + 1; ?>'">+</button>
                                </div>
                                
                                <a href="cart-action.php?action=remove&id=<?php echo $item['id']; ?>" class="remove">
                                    <i class="fa-solid fa-trash"></i>
                                    Remove Item
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!--================ RIGHT ================-->
                <div class="summary">
                    <h2>Order Summary</h2>
                    <div class="summary-row">
                        <span>Items (<?php echo array_sum($_SESSION['cart']); ?>)</span>
                        <span>Rs. <?php echo number_format($subtotal); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Delivery Fee</span>
                        <span>Rs. <?php echo number_format($delivery_fee); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Service Charge</span>
                        <span>Rs. <?php echo number_format($service_charge); ?></span>
                    </div>
                    <?php if ($discount > 0): ?>
                        <div class="summary-row" style="color: green; font-weight: 500;">
                            <span>Discount (Promo)</span>
                            <span>- Rs. <?php echo number_format($discount); ?></span>
                        </div>
                    <?php endif; ?>
                    <hr>
                    <div class="summary-row total">
                        <span>Total</span>
                        <span>Rs. <?php echo number_format($total); ?></span>
                    </div>

                    <form action="cart.php" method="POST" style="margin-top: 15px;">
                        <input type="text" name="promo_code" placeholder="Promo Code (e.g. WELCOME10)" value="<?php echo htmlspecialchars($promo_code); ?>">
                        <button type="submit" class="apply">Apply Coupon</button>
                    </form>
                    
                    <?php 
                    if (isset($_SESSION['discount_error'])) {
                        echo '<span class="discount-error">' . htmlspecialchars($_SESSION['discount_error']) . '</span>';
                        unset($_SESSION['discount_error']);
                    }
                    if ($promo_applied) {
                        echo '<span class="discount-success">Promo code WELCOME10 applied! (Rs. 500 off)</span>';
                    }
                    ?>

                    <a href="checkout.php" class="checkout-btn" style="text-align: center; display: block; line-height: 50px; margin-top: 20px;">
                        Proceed to Checkout
                    </a>

                    <div class="delivery" style="margin-top: 20px;">
                        <h3>🚚 Estimated Delivery</h3>
                        <p>30 - 45 Minutes</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="empty-cart-msg">
                    <h2>Your Shopping Cart is Empty! 🛒</h2>
                    <p>It looks like you haven't added any meals to your cart yet.</p>
                    <a href="menu.php" class="btn">View Our Menu</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!--================ PAYMENT METHODS ================-->
    <section class="payment-section">
        <div class="container">
            <h2>Accepted Payment Methods</h2>
            <div class="payment-grid">
                <div class="payment-card">
                    <i class="fa-solid fa-money-bill-wave"></i>
                    <h3>Cash on Delivery</h3>
                    <p>Pay when your order arrives.</p>
                </div>
            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?>

</body>
</html>
