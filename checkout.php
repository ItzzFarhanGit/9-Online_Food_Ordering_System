<?php
require_once 'db.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 1. Check if logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['success_msg'] = 'Please login to proceed with checkout.';
    header("Location: login.php");
    exit;
}

// 2. Check if cart is empty
if (empty($_SESSION['cart'])) {
    header("Location: menu.php");
    exit;
}

$cart_items = [];
$subtotal = $_SESSION['subtotal'] ?? 0;
$delivery_fee = $_SESSION['delivery_fee'] ?? 350;
$service_charge = $_SESSION['service_charge'] ?? 150;
$discount = $_SESSION['discount_amount'] ?? 0;
$total = $_SESSION['total_price'] ?? 0;

// Fetch Cart Products for side-panel display
$ids = array_keys($_SESSION['cart']);
if (!empty($ids)) {
    $placeholders = str_repeat('?,', count($ids) - 1) . '?';
    try {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
        $stmt->execute(array_values($ids));
        $products = $stmt->fetchAll();
        
        foreach ($products as $product) {
            $product['qty'] = $_SESSION['cart'][$product['id']];
            $product['item_total'] = $product['price'] * $product['qty'];
            $cart_items[] = $product;
        }
    } catch (PDOException $e) {
        // Handle db error
    }
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $payment_method = 'Cash on Delivery';

    if (empty($first_name) || empty($last_name) || empty($email) || empty($phone) || empty($address)) {
        $error = 'All billing fields are required.';
    } else {
        try {
            $pdo->beginTransaction();

            // Insert into orders table
            $stmt = $pdo->prepare("
                INSERT INTO orders 
                (user_id, first_name, last_name, email, phone, address, notes, payment_method, subtotal, delivery_fee, service_charge, discount_amount, total_price, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Placed')
            ");
            $stmt->execute([
                $_SESSION['user_id'],
                $first_name,
                $last_name,
                $email,
                $phone,
                $address,
                $notes,
                $payment_method,
                $subtotal,
                $delivery_fee,
                $service_charge,
                $discount,
                $total
            ]);
            $order_id = $pdo->lastInsertId();

            // Insert items into order_items table
            $stmt_item = $pdo->prepare("
                INSERT INTO order_items (order_id, product_id, quantity, price) 
                VALUES (?, ?, ?, ?)
            ");
            foreach ($cart_items as $item) {
                $stmt_item->execute([
                    $order_id,
                    $item['id'],
                    $item['qty'],
                    $item['price']
                ]);
            }

            $pdo->commit();

            // Clear session cart
            unset($_SESSION['cart']);
            unset($_SESSION['subtotal']);
            unset($_SESSION['delivery_fee']);
            unset($_SESSION['service_charge']);
            unset($_SESSION['discount_amount']);
            unset($_SESSION['total_price']);
            unset($_SESSION['promo_code']);
            unset($_SESSION['discount']);

            // Redirect to success
            header("Location: order-success.php?id=" . $order_id);
            exit;

        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'Failed to place order. Please try again. ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | Delight Dinning</title>
    <link rel="stylesheet" href="checkout.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        .error-msg {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 14px;
            text-align: center;
        }
    </style>
</head>
<body>

    <?php include 'header.php'; ?>

    <!--========== PAGE TITLE ==========-->
    <section class="page-title">
        <div class="container">
            <h1>Checkout</h1>
            <p>Complete your order in just a few steps.</p>
        </div>
    </section>

    <!--========== CHECKOUT ==========-->
    <section class="checkout">
        <div class="container checkout-grid">
            
            <!--========== LEFT ==========-->
            <div class="checkout-form">
                <h2>Billing Details</h2>
                
                <?php if (!empty($error)): ?>
                    <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form id="checkout-payment-form" action="checkout.php" method="POST">
                    <div class="row">
                        <div class="input-box">
                            <label>First Name</label>
                            <input type="text" name="first_name" placeholder="John" value="<?php echo htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]); ?>" required>
                        </div>
                        <div class="input-box">
                            <label>Last Name</label>
                            <input type="text" name="last_name" placeholder="Doe" value="<?php echo htmlspecialchars(explode(' ', $_SESSION['user_name'])[1] ?? 'Customer'); ?>" required>
                        </div>
                    </div>

                    <div class="input-box">
                        <label>Email Address</label>
                        <input type="email" name="email" placeholder="john@example.com" value="<?php echo htmlspecialchars($_SESSION['user_email']); ?>" required>
                    </div>

                    <div class="input-box">
                        <label>Phone Number</label>
                        <input type="text" name="phone" placeholder="+94 76 978 8951" value="<?php echo htmlspecialchars($_SESSION['user_phone']); ?>" required>
                    </div>

                    <div class="input-box">
                        <label>Delivery Address</label>
                        <textarea name="address" rows="5" placeholder="House No, Street, City" required></textarea>
                    </div>

                    <div class="input-box">
                        <label>Order Notes</label>
                        <textarea name="notes" rows="4" placeholder="Extra cheese, less spicy..."></textarea>
                    </div>

                    <h2>Payment Method</h2>
                    <div class="payment-options">
                        <label>
                            <input type="radio" name="payment" value="Cash on Delivery" checked>
                            Cash on Delivery
                        </label>
                    </div>
                </form>
            </div>

            <!--========== RIGHT ==========-->
            <div class="order-summary">
                <h2>Your Order</h2>
                
                <?php foreach ($cart_items as $item): ?>
                    <div class="summary-item">
                        <span><?php echo htmlspecialchars($item['name']); ?> ×<?php echo $item['qty']; ?></span>
                        <span>Rs. <?php echo number_format($item['item_total']); ?></span>
                    </div>
                <?php endforeach; ?>

                <hr>

                <div class="summary-item">
                    <span>Subtotal</span>
                    <span>Rs. <?php echo number_format($subtotal); ?></span>
                </div>

                <div class="summary-item">
                    <span>Delivery</span>
                    <span>Rs. <?php echo number_format($delivery_fee); ?></span>
                </div>

                <div class="summary-item">
                    <span>Service Charge</span>
                    <span>Rs. <?php echo number_format($service_charge); ?></span>
                </div>

                <?php if ($discount > 0): ?>
                    <div class="summary-item" style="color: green;">
                        <span>Discount</span>
                        <span>- Rs. <?php echo number_format($discount); ?></span>
                    </div>
                <?php endif; ?>

                <div class="summary-item total">
                    <strong>Total</strong>
                    <strong>Rs. <?php echo number_format($total); ?></strong>
                </div>

                <button type="submit" form="checkout-payment-form" class="place-order" style="border: none; width: 100%; font-family: inherit; font-size: 16px; cursor: pointer;">
                    Place Order
                </button>
            </div>
        </div>
    </section>

    <!--================ DELIVERY INFO ================-->
    <section class="delivery-info">
        <div class="container">
            <div class="info-card">
                <i class="fa-solid fa-truck-fast"></i>
                <h3>Fast Delivery</h3>
                <p>Estimated delivery time: <strong>30 - 45 Minutes</strong></p>
            </div>
            <div class="info-card">
                <i class="fa-solid fa-money-bill-wave"></i>
                <h3>Cash on Delivery</h3>
                <p>Pay with cash when your order is delivered to your door.</p>
            </div>
            <div class="info-card">
                <i class="fa-solid fa-utensils"></i>
                <h3>Freshly Prepared</h3>
                <p>Every meal is cooked fresh after your order is confirmed.</p>
            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?>

</body>
</html>
