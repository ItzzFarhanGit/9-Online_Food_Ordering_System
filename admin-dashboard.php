<?php
require_once 'db.php';
require_once 'whatsapp-helper.php';
include 'admin-header.php';

// Fetch stats
$total_sales = 0;
$active_orders = 0;
$total_foods = 0;
$total_users = 0;
$pending_whatsapp_count = 0;
$pending_whatsapp_orders = [];

try {
    // 1. Total Sales (Delivered orders)
    $stmt = $pdo->query("SELECT SUM(total_price) as sales FROM orders WHERE status = 'Delivered'");
    $sales_data = $stmt->fetch();
    $total_sales = $sales_data['sales'] ?? 0;

    // 2. Active Orders
    $stmt = $pdo->query("SELECT COUNT(*) as active FROM orders WHERE status IN ('Placed', 'Preparing', 'Out for Delivery')");
    $orders_data = $stmt->fetch();
    $active_orders = $orders_data['active'] ?? 0;

    // 3. Total Foods
    $stmt = $pdo->query("SELECT COUNT(*) as foods FROM products");
    $foods_data = $stmt->fetch();
    $total_foods = $foods_data['foods'] ?? 0;

    // 4. Total Users
    $stmt = $pdo->query("SELECT COUNT(*) as users FROM users");
    $users_data = $stmt->fetch();
    $total_users = $users_data['users'] ?? 0;

    // 5. Recent Orders (last 5)
    $stmt = $pdo->query("SELECT * FROM orders ORDER BY id DESC LIMIT 5");
    $recent_orders = $stmt->fetchAll();

    // 6. Orders waiting for WhatsApp confirmation
    $stmt = $pdo->query("
        SELECT id, first_name, last_name, phone, total_price, created_at
        FROM orders
        WHERE status = 'Placed' AND whatsapp_sent = 0
        ORDER BY id ASC
    ");
    $pending_whatsapp_orders = $stmt->fetchAll();
    $pending_whatsapp_count = count($pending_whatsapp_orders);

    $stmt = $pdo->query("SELECT COALESCE(MAX(id), 0) AS latest_id FROM orders");
    $latest_row = $stmt->fetch();
    $latest_order_id = (int) ($latest_row['latest_id'] ?? 0);
} catch (PDOException $e) {
    $recent_orders = [];
    $pending_whatsapp_orders = [];
    $latest_order_id = 0;
}

$auto_open_wa = $auto_open_wa ?? null;
?>

<div class="admin-dashboard-section">
    <div class="admin-panel-container">
        
        <div class="admin-page-header">
            <h1>Dashboard Overview</h1>
            <p>Welcome to Delight Dinning Management Console.</p>
        </div>

        <?php if ($pending_whatsapp_count > 0): ?>
            <div class="admin-alert" style="background: #fff8e6; border: 1px solid #ffe08a; color: #7a5b00; margin-bottom: 25px;">
                <i class="fa-brands fa-whatsapp" style="color: #25d366;"></i>
                <strong><?php echo $pending_whatsapp_count; ?> new order(s)</strong> need order confirmation &amp; bill sent via WhatsApp.
                <div style="margin-top: 12px; display: flex; flex-wrap: wrap; gap: 8px;">
                    <?php foreach ($pending_whatsapp_orders as $pending): ?>
                        <a href="admin-orders.php?action=send_whatsapp&id=<?php echo (int) $pending['id']; ?>" style="display: inline-flex; align-items: center; gap: 6px; background: #25d366; color: #fff; text-decoration: none; padding: 7px 14px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                            <i class="fa-brands fa-whatsapp"></i>
                            Send Bill — <?php echo getOrderNumber((int) $pending['id']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Stats Grid -->
        <div class="admin-stats-grid">
            <!-- Stat 1 -->
            <div class="stat-card">
                <div class="stat-icon sales">
                    <i class="fa-solid fa-sack-dollar"></i>
                </div>
                <div class="stat-details">
                    <h3>Rs. <?php echo number_format($total_sales); ?></h3>
                    <p>Total Sales</p>
                </div>
            </div>

            <!-- Stat 2 -->
            <div class="stat-card">
                <div class="stat-icon orders">
                    <i class="fa-solid fa-truck-ramp-box"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $active_orders; ?></h3>
                    <p>Active Orders</p>
                </div>
            </div>

            <!-- Stat 3 -->
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fa-solid fa-utensils"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $total_foods; ?></h3>
                    <p>Total Foods</p>
                </div>
            </div>

            <!-- Stat 4 -->
            <div class="stat-card">
                <div class="stat-icon users">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $total_users; ?></h3>
                    <p>Total Users</p>
                </div>
            </div>
        </div>

        <!-- Recent Activity Section -->
        <div class="admin-row">
            <div class="admin-card">
                <h2>
                    <span><i class="fa-solid fa-clock-rotate-left"></i> Recent Orders</span>
                    <a href="admin-orders.php" style="font-size: 13px; color: var(--primary); text-decoration: none; font-weight: 600;">View All Orders &rarr;</a>
                </h2>
                
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Address</th>
                                <th>Method</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recent_orders)): ?>
                                <?php foreach ($recent_orders as $order): ?>
                                    <tr>
                                        <td><strong>#DD<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></strong></td>
                                        <td><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($order['address']); ?></td>
                                        <td><?php echo htmlspecialchars($order['payment_method']); ?></td>
                                        <td><strong>Rs. <?php echo number_format($order['total_price']); ?></strong></td>
                                        <td>
                                            <?php
                                            $badge_class = '';
                                            if ($order['status'] === 'Placed') $badge_class = 'placed';
                                            elseif ($order['status'] === 'Preparing') $badge_class = 'preparing';
                                            elseif ($order['status'] === 'Out for Delivery') $badge_class = 'out';
                                            elseif ($order['status'] === 'Delivered') $badge_class = 'delivered';
                                            elseif ($order['status'] === 'Cancelled') $badge_class = 'cancelled';
                                            ?>
                                            <span class="badge-status <?php echo $badge_class; ?>">
                                                <?php echo htmlspecialchars($order['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="admin-orders.php" class="btn-action view">
                                                <i class="fa-solid fa-sliders"></i> Manage
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; color: #888;">No recent orders placed.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<?php include 'admin-footer.php'; ?>
