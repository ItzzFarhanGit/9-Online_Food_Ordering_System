<?php
require_once 'db.php';
require_once 'whatsapp-helper.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$order_id = (int)($_GET['id'] ?? 0);
$order = null;
$order_items = [];

if ($order_id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
        $stmt->execute([$order_id, $_SESSION['user_id']]);
        $order = $stmt->fetch();

        if ($order) {
            $stmt_items = $pdo->prepare("
                SELECT oi.*, p.name as product_name 
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = ?
            ");
            $stmt_items->execute([$order_id]);
            $order_items = $stmt_items->fetchAll();
        }
    } catch (PDOException $e) {
        // Handle db error
    }
}

if (!$order) {
    die("Invalid Order ID or you do not have permission to view this order.");
}

$customer_phone = htmlspecialchars($order['phone']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Successful | Delight Dinning</title>
    <link rel="stylesheet" href="order-success.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        .btn.primary {
            background-color: #FF6B00 !important;
            color: #fff !important;
            border: none;
        }
        .btn.track {
            background-color: #27ae60 !important;
            color: #fff !important;
            border: none;
            padding: 15px 30px;
            font-size: 16px;
            border-radius: 30px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-transform: uppercase;
            box-shadow: 0 5px 15px rgba(39, 174, 96, 0.3);
            transition: 0.3s;
        }
        .btn.track:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(39, 174, 96, 0.4);
        }
        .btn.whatsapp-info-box {
            background-color: #e8f8ef !important;
            color: #155724 !important;
            border: 1px solid #c3e6cb;
            padding: 18px 24px;
            font-size: 14px;
            border-radius: 16px;
            font-weight: 500;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            text-align: left;
            line-height: 1.6;
            width: 100%;
            box-sizing: border-box;
        }
        .btn.whatsapp-info-box i {
            color: #25D366;
            font-size: 24px;
            margin-top: 2px;
        }
        .success-card .buttons {
            display: flex;
            flex-direction: column;
            gap: 15px;
            align-items: center;
            margin-top: 30px;
            width: 100%;
        }
        .success-card .buttons-row {
            display: flex;
            gap: 15px;
            justify-content: center;
            width: 100%;
        }
        .success-card {
            max-width: 550px !important;
            width: 90% !important;
        }
        
        /* Auto-redirect Toast/Notification Styles */
        .toast-notification {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%) translateY(-100px);
            background-color: #e3fcef;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 15px 25px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            z-index: 10000;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: transform 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            font-weight: 500;
        }
        .toast-notification.show {
            transform: translateX(-50%) translateY(0);
        }
        .toast-notification i {
            color: #25D366;
            font-size: 20px;
        }
    </style>
</head>
<body>

<section class="success">
    <div class="success-card">
        <div class="check-icon">
            <i class="fa-solid fa-circle-check"></i>
        </div>

        <h1>Order Placed Successfully!</h1>
        <p>
            Thank you for choosing <strong>Delight Dinning</strong>.<br>
            Your delicious meal is now being prepared by our chefs.
        </p>

        <div class="order-details">
            <div class="detail">
                <span>Order Number</span>
                <strong>#DD<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></strong>
            </div>
            <div class="detail">
                <span>Estimated Delivery</span>
                <strong>30 - 45 Minutes</strong>
            </div>
            <div class="detail">
                <span>Payment Method</span>
                <strong><?php echo htmlspecialchars($order['payment_method']); ?></strong>
            </div>
            <div class="detail">
                <span>Total Amount</span>
                <strong>Rs. <?php echo number_format($order['total_price']); ?></strong>
            </div>
        </div>

        <div class="buttons">
            <div class="btn whatsapp-info-box">
                <i class="fa-brands fa-whatsapp"></i>
                <span>
                    Our team will send your <strong>order confirmation &amp; bill</strong> to
                    <strong><?php echo $customer_phone; ?></strong> via WhatsApp shortly.
                </span>
            </div>

            <a href="track-order.php?id=<?php echo $order['id']; ?>" class="btn track">
                <i class="fa-solid fa-map-location-dot"></i>
                Track Your Order 🚚
            </a>
            <div class="buttons-row">
                <a href="menu.php" class="btn primary">
                    <i class="fa-solid fa-utensils"></i>
                    Order More
                </a>
                <a href="index.php" class="btn secondary">
                    <i class="fa-solid fa-house"></i>
                    Back to Home
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Toast notification -->
<div id="whatsapp-toast" class="toast-notification">
    <i class="fa-brands fa-whatsapp"></i>
    <span>You will receive your order bill on WhatsApp from our team soon!</span>
</div>

<!-- WhatsApp Float -->
<a href="https://wa.me/94769788951?text=Hello%20Delight%20Dinning!%20I%20would%20like%20to%20place%20an%20order." class="whatsapp-float" target="_blank">
    <i class="fa-brands fa-whatsapp"></i>
</a>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Show the toast notification after 1 second
        setTimeout(function() {
            const toast = document.getElementById("whatsapp-toast");
            if (toast) {
                toast.classList.add("show");
                // Automatically hide after 6 seconds
                setTimeout(function() {
                    toast.classList.remove("show");
                }, 6000);
            }
        }, 1000);
    });
</script>

</body>
</html>
