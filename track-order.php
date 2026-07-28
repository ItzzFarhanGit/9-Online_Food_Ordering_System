<?php
require_once 'db.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check login (a regular customer or a logged-in admin may both track an order)
$is_admin = isset($_SESSION['admin_id']);
if (!isset($_SESSION['user_id']) && !$is_admin) {
    $_SESSION['success_msg'] = 'Please login to track orders.';
    header("Location: login.php");
    exit;
}

$order_id = (int)($_GET['id'] ?? 0);
$order = null;
$order_items = [];

if ($order_id > 0) {
    try {
        // Fetch order details. Admins can view any order; customers only their own.
        if ($is_admin) {
            $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
            $stmt->execute([$order_id]);
        } else {
            $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
            $stmt->execute([$order_id, $_SESSION['user_id']]);
        }
        $order = $stmt->fetch();

        if ($order) {
            // Fetch items
            $stmt_items = $pdo->prepare("
                SELECT oi.*, p.name as product_name, p.image 
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = ?
            ");
            $stmt_items->execute([$order_id]);
            $order_items = $stmt_items->fetchAll();
        }
    } catch (PDOException $e) {
        // Silence or handle db error
    }
}

// Redirect or show error if order not found
if (!$order) {
    die("Invalid Order ID or you do not have permission to track this order. <a href='index.php'>Go Home</a>");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Order #DD<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?> | Delight Dinning</title>
    <link rel="stylesheet" href="track-order.css">
    <!-- Main styles -->
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>
<body>

    <?php include 'header.php'; ?>

    <section class="tracker-section">
        <div class="container">
            <h1>Order Live Tracking</h1>
           

            <div class="tracker-content">
                <div class="tracker-card">
                    <h2>
                        <span>Order Status</span>
                        <span class="order-badge">#DD<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></span>
                    </h2>

                    <!-- Timeline Progress -->
                    <?php
                    $status = $order['status'];
                    $timeline_class = 'placed-active';
                    if ($status === 'Preparing') $timeline_class = 'preparing-active';
                    elseif ($status === 'Out for Delivery') $timeline_class = 'out-active';
                    elseif ($status === 'Delivered') $timeline_class = 'delivered-active';
                    ?>
                    <div class="progress-timeline <?php echo $timeline_class; ?>" id="timeline-container">
                        <!-- Step 1 -->
                        <div class="timeline-step <?php echo in_array($status, ['Placed', 'Preparing', 'Out for Delivery', 'Delivered']) ? ($status === 'Placed' ? 'active' : 'completed') : ''; ?>" id="step-placed">
                            <div class="step-icon">
                                <i class="fa-solid fa-receipt"></i>
                            </div>
                            <div class="step-content">
                                <h3>Order Placed</h3>
                                <p>We have received your order and are verifying it.</p>
                            </div>
                        </div>

                        <!-- Step 2 -->
                        <div class="timeline-step <?php echo in_array($status, ['Preparing', 'Out for Delivery', 'Delivered']) ? ($status === 'Preparing' ? 'active' : 'completed') : ''; ?>" id="step-preparing">
                            <div class="step-icon">
                                <i class="fa-solid fa-fire-burner"></i>
                            </div>
                            <div class="step-content">
                                <h3>Preparing Meal</h3>
                                <p>Our kitchen chefs are cooking your fresh and hot meal.</p>
                            </div>
                        </div>

                        <!-- Step 3 -->
                        <div class="timeline-step <?php echo in_array($status, ['Out for Delivery', 'Delivered']) ? ($status === 'Out for Delivery' ? 'active' : 'completed') : ''; ?>" id="step-out">
                            <div class="step-icon">
                                <i class="fa-solid fa-motorcycle"></i>
                            </div>
                            <div class="step-content">
                                <h3>Out for Delivery</h3>
                                <p>Our delivery boy is speeding towards your location.</p>
                            </div>
                        </div>

                        <!-- Step 4 -->
                        <div class="timeline-step <?php echo ($status === 'Delivered') ? 'completed active' : ''; ?>" id="step-delivered">
                            <div class="step-icon">
                                <i class="fa-solid fa-house-chimney-user"></i>
                            </div>
                            <div class="step-content">
                                <h3>Delivered</h3>
                                <p>Order arrived at your doorstep. Bon appetit!</p>
                            </div>
                        </div>
                    </div>

                    <h2 style="margin-top: 40px;">Delivery Info</h2>
                    <div class="info-row">
                        <span>Customer Name</span>
                        <strong><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></strong>
                    </div>
                    <div class="info-row">
                        <span>Phone Number</span>
                        <strong><?php echo htmlspecialchars($order['phone']); ?></strong>
                    </div>
                    <div class="info-row">
                        <span>Delivery Address</span>
                        <strong><?php echo htmlspecialchars($order['address']); ?></strong>
                    </div>
                    <div class="info-row">
                        <span>Payment Mode</span>
                        <strong><?php echo htmlspecialchars($order['payment_method']); ?></strong>
                    </div>
                    <div class="info-row">
                        <span>Amount Paid</span>
                        <strong>Rs. <?php echo number_format($order['total_price']); ?></strong>
                    </div>

                    <h2 style="margin-top: 30px;">Order Summary</h2>
                    <div class="items-list">
                        <?php foreach ($order_items as $item): ?>
                            <div class="info-row">
                                <span><?php echo htmlspecialchars($item['product_name']); ?> ×<?php echo $item['quantity']; ?></span>
                                <strong>Rs. <?php echo number_format($item['price'] * $item['quantity']); ?></strong>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?>

    <script>
        const orderId = <?php echo $order['id']; ?>;
        let currentStatus = "<?php echo $order['status']; ?>";

        setInterval(() => {
            fetch(`get-order-status.php?id=${orderId}`)
                .then(response => response.json())
                .then(data => {
                    if (data && data.status && data.status !== currentStatus) {
                        currentStatus = data.status;

                        const timeline = document.getElementById('timeline-container');
                        timeline.className = 'progress-timeline ';
                        if (currentStatus === 'Preparing') timeline.classList.add('preparing-active');
                        else if (currentStatus === 'Out for Delivery') timeline.classList.add('out-active');
                        else if (currentStatus === 'Delivered') timeline.classList.add('delivered-active');
                        else timeline.classList.add('placed-active');

                        updateTimelineSteps(currentStatus);
                    }
                })
                .catch(err => console.error("Error polling order status:", err));
        }, 3000);

        function updateTimelineSteps(status) {
            const stepPlaced = document.getElementById('step-placed');
            const stepPreparing = document.getElementById('step-preparing');
            const stepOut = document.getElementById('step-out');
            const stepDelivered = document.getElementById('step-delivered');

            [stepPlaced, stepPreparing, stepOut, stepDelivered].forEach(step => {
                step.className = 'timeline-step';
            });

            if (status === 'Placed') {
                stepPlaced.classList.add('active');
            } else if (status === 'Preparing') {
                stepPlaced.classList.add('completed');
                stepPreparing.classList.add('active');
            } else if (status === 'Out for Delivery') {
                stepPlaced.classList.add('completed');
                stepPreparing.classList.add('completed');
                stepOut.classList.add('active');
            } else if (status === 'Delivered') {
                stepPlaced.classList.add('completed');
                stepPreparing.classList.add('completed');
                stepOut.classList.add('completed');
                stepDelivered.classList.add('completed', 'active');
            }
        }
    </script>
</body>
</html>
