<?php
require_once 'db.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['success_msg'] = 'Please log in to manage your orders.';
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle order cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel') {
    $order_id = (int)($_POST['order_id'] ?? 0);
    if ($order_id > 0) {
        try {
            // Verify order belongs to customer and is in 'Placed' state
            $stmt = $pdo->prepare("SELECT id, status FROM orders WHERE id = ? AND user_id = ?");
            $stmt->execute([$order_id, $user_id]);
            $order = $stmt->fetch();

            if ($order) {
                if ($order['status'] === 'Placed') {
                    $update_stmt = $pdo->prepare("UPDATE orders SET status = 'Cancelled' WHERE id = ?");
                    $update_stmt->execute([$order_id]);
                    $_SESSION['success_msg'] = 'Your order #DD' . str_pad($order_id, 6, '0', STR_PAD_LEFT) . ' has been cancelled successfully.';
                } else {
                    $_SESSION['error_msg'] = 'Only orders in the "Placed" state can be cancelled. Your order is already being processed.';
                }
            } else {
                $_SESSION['error_msg'] = 'Order not found or access denied.';
            }
        } catch (PDOException $e) {
            $_SESSION['error_msg'] = 'An error occurred. Please try again later.';
        }
    }
    header("Location: my-orders.php");
    exit;
}

// Fetch all orders for current user
$orders = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC");
    $stmt->execute([$user_id]);
    $user_orders = $stmt->fetchAll();

    foreach ($user_orders as $order) {
        // Fetch items for each order
        $stmt_items = $pdo->prepare("
            SELECT oi.*, p.name as product_name, p.image 
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = ?
        ");
        $stmt_items->execute([$order['id']]);
        $order['items'] = $stmt_items->fetchAll();
        $orders[] = $order;
    }
} catch (PDOException $e) {
    // Handle error or display empty list
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders | Delight Dinning</title>
    <link rel="stylesheet" href="my-orders.css">
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>
<body>

    <?php include 'header.php'; ?>

    <main class="orders-section">
        <div class="container">
            <div class="orders-header">
                <h1>Manage Your Orders</h1>
                <p>Track live statuses, view receipt breakdowns, or cancel new orders.</p>
            </div>

            <!-- Toast alert inside the page if any errors -->
            <?php if (isset($_SESSION['error_msg'])): ?>
                <div class="alert-message error-alert">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span><?php echo htmlspecialchars($_SESSION['error_msg']); unset($_SESSION['error_msg']); ?></span>
                </div>
            <?php endif; ?>

            <div class="orders-container">
                <?php if (!empty($orders)): ?>
                    <?php foreach ($orders as $order): ?>
                        <div class="order-card">
                            <!-- Card Header -->
                            <div class="order-card-header">
                                <div class="order-meta">
                                    <span class="order-number">Order #DD<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></span>
                                    <span class="order-date"><i class="fa-regular fa-calendar"></i> <?php echo date('F j, Y, g:i a', strtotime($order['created_at'])); ?></span>
                                </div>
                                <?php
                                $status = $order['status'];
                                $badge_class = '';
                                if ($status === 'Placed') $badge_class = 'status-placed';
                                elseif ($status === 'Preparing') $badge_class = 'status-preparing';
                                elseif ($status === 'Out for Delivery') $badge_class = 'status-out';
                                elseif ($status === 'Delivered') $badge_class = 'status-delivered';
                                elseif ($status === 'Cancelled') $badge_class = 'status-cancelled';
                                ?>
                                <span class="status-badge <?php echo $badge_class; ?>">
                                    <?php echo htmlspecialchars($status); ?>
                                </span>
                            </div>

                            <!-- Card Body: Grid -->
                            <div class="order-card-body">
                                <!-- Left Column: Item details list -->
                                <div class="order-items-list">
                                    <h3>Order Items</h3>
                                    <?php foreach ($order['items'] as $item): ?>
                                        <div class="order-item-row">
                                            <div class="item-img-container">
                                                <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                                            </div>
                                            <div class="item-name-qty">
                                                <span class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></span>
                                                <span class="item-qty">Qty: <?php echo $item['quantity']; ?> × Rs. <?php echo number_format($item['price']); ?></span>
                                            </div>
                                            <span class="item-subtotal">Rs. <?php echo number_format($item['price'] * $item['quantity']); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <!-- Right Column: Payment & Shipping Summary -->
                                <div class="order-summary-box">
                                    <h3>Receipt Summary</h3>
                                    <div class="summary-row">
                                        <span>Subtotal</span>
                                        <span>Rs. <?php echo number_format($order['subtotal']); ?></span>
                                    </div>
                                    <div class="summary-row">
                                        <span>Delivery Fee</span>
                                        <span>Rs. <?php echo number_format($order['delivery_fee']); ?></span>
                                    </div>
                                    <div class="summary-row">
                                        <span>Service Charge</span>
                                        <span>Rs. <?php echo number_format($order['service_charge']); ?></span>
                                    </div>
                                    <?php if ($order['discount_amount'] > 0): ?>
                                        <div class="summary-row discount-row">
                                            <span>Discount Applied</span>
                                            <span>- Rs. <?php echo number_format($order['discount_amount']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <div class="summary-row total-row">
                                        <span>Total Amount</span>
                                        <span>Rs. <?php echo number_format($order['total_price']); ?></span>
                                    </div>
                                    
                                    <div class="payment-info">
                                        <span>Payment Mode:</span>
                                        <strong><?php echo htmlspecialchars($order['payment_method']); ?></strong>
                                    </div>
                                </div>
                            </div>

                            <!-- Card Footer: Actions -->
                            <div class="order-card-footer">
                                <div class="footer-actions-left">
                                    <?php if ($status !== 'Cancelled'): ?>
                                        <a href="track-order.php?id=<?php echo $order['id']; ?>" class="action-btn track-btn">
                                            <i class="fa-solid fa-map-location-dot"></i> Live Tracking
                                        </a>
                                    <?php endif; ?>
                                </div>

                                <div class="footer-actions-right">
                                    <?php if ($status === 'Placed'): ?>
                                        <form action="my-orders.php" method="POST" onsubmit="return confirmCancellation(<?php echo $order['id']; ?>)">
                                            <input type="hidden" name="action" value="cancel">
                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                            <button type="submit" class="action-btn cancel-btn">
                                                <i class="fa-solid fa-rectangle-xmark"></i> Cancel Order
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Empty Orders Page -->
                    <div class="empty-orders-card">
                        <div class="empty-icon-wrapper">
                            <i class="fa-solid fa-basket-shopping"></i>
                        </div>
                        <h2>No Orders Found</h2>
                        <p>It looks like you haven't placed any food orders yet.</p>
                        <a href="menu.php" class="browse-menu-btn">Browse Delicious Menu</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include 'footer.php'; ?>

    <script>
        function confirmCancellation(orderId) {
            return confirm("Are you sure you want to cancel Order #DD" + String(orderId).padStart(6, '0') + "? This action cannot be undone.");
        }
    </script>
</body>
</html>
