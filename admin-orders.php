<?php
require_once 'db.php';
require_once 'whatsapp-helper.php';
include 'admin-header.php';

// Handle Send WhatsApp Confirmation to Customer
if (isset($_GET['action']) && $_GET['action'] === 'send_whatsapp') {
    $order_id = (int)($_GET['id'] ?? 0);

    if ($order_id > 0) {
        try {
            $stmt_order = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
            $stmt_order->execute([$order_id]);
            $o_data = $stmt_order->fetch();

            if ($o_data) {
                $items = fetchOrderItems($pdo, $order_id);
                $msg_text = buildOrderConfirmationMessage($o_data, $items);
                $_SESSION['admin_wa_url'] = buildWhatsAppUrl($o_data['phone'], $msg_text);

                $stmt_mark = $pdo->prepare("UPDATE orders SET whatsapp_sent = 1 WHERE id = ?");
                $stmt_mark->execute([$order_id]);

                $_SESSION['admin_msg'] = "Order bill ready for " . getOrderNumber($order_id) . ". WhatsApp will open — click Send to deliver the bill to the customer.";
            }
        } catch (PDOException $e) {
            $_SESSION['admin_err'] = "Failed to prepare WhatsApp confirmation.";
        }
    }

    header("Location: admin-orders.php");
    exit;
}

// Handle Order Status Update Action
if (isset($_GET['action']) && $_GET['action'] === 'update_status') {
    $order_id = (int)($_GET['id'] ?? 0);
    $new_status = $_GET['status'] ?? '';
    
    $valid_statuses = ['Placed', 'Preparing', 'Out for Delivery', 'Delivered', 'Cancelled'];
    
    if ($order_id > 0 && in_array($new_status, $valid_statuses)) {
        try {
            $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $order_id]);
            
            $stmt_order = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
            $stmt_order->execute([$order_id]);
            $o_data = $stmt_order->fetch();
            if ($o_data) {
                $msg_text = buildStatusUpdateMessage($o_data, $new_status);
                $_SESSION['admin_wa_url'] = buildWhatsAppUrl($o_data['phone'], $msg_text);
            }
            
            $_SESSION['admin_msg'] = "Order " . getOrderNumber($order_id) . " status updated to '$new_status'!";
        } catch (PDOException $e) {
            $_SESSION['admin_err'] = "Failed to update order status.";
        }
    }
    
    header("Location: admin-orders.php");
    exit;
}

// Fetch all orders
$orders = [];
try {
    $stmt = $pdo->query("SELECT * FROM orders ORDER BY id DESC");
    $all_orders = $stmt->fetchAll();
    
    foreach ($all_orders as $order) {
        $stmt_items = $pdo->prepare("
            SELECT oi.*, p.name as product_name 
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = ?
        ");
        $stmt_items->execute([$order['id']]);
        $order['items'] = $stmt_items->fetchAll();
        $orders[] = $order;
    }
} catch (PDOException $e) {
    // Handle error
}

$latest_order_id = 0;
if (!empty($orders)) {
    $latest_order_id = (int) $orders[0]['id'];
}
$auto_open_wa = $auto_open_wa ?? null;
?>

<div class="admin-dashboard-section">
    <div class="admin-panel-container">
        
        <div class="admin-page-header">
            <h1>Manage Orders</h1>
            <p>View client orders, send bill via WhatsApp, and update delivery status.</p>
        </div>

        <?php 
        if (isset($_SESSION['admin_msg'])) {
            echo '<div class="admin-alert success"><i class="fa-solid fa-circle-check"></i> ' . htmlspecialchars($_SESSION['admin_msg']);
            if (isset($_SESSION['admin_wa_url'])) {
                echo ' <a href="' . htmlspecialchars($_SESSION['admin_wa_url']) . '" class="btn-action admin-wa-open-link" style="background: #25d366; color: white; margin-left: 15px; padding: 6px 15px; border-radius: 20px; text-decoration: none; font-size: 13px;" target="_blank" data-wa-url="' . htmlspecialchars($_SESSION['admin_wa_url']) . '"><i class="fa-brands fa-whatsapp"></i> Open WhatsApp & Send</a>';
                $auto_open_wa = $_SESSION['admin_wa_url'];
                unset($_SESSION['admin_wa_url']);
            }
            echo '</div>';
            unset($_SESSION['admin_msg']);
        }
        if (isset($_SESSION['admin_err'])) {
            echo '<div class="admin-alert error"><i class="fa-solid fa-circle-exclamation"></i> ' . htmlspecialchars($_SESSION['admin_err']) . '</div>';
            unset($_SESSION['admin_err']);
        }

        $pending_whatsapp = array_filter($orders, function ($order) {
            return $order['status'] === 'Placed' && empty($order['whatsapp_sent']);
        });
        ?>

        <?php if (!empty($pending_whatsapp)): ?>
            <div class="admin-alert" style="background: #fff8e6; border: 1px solid #ffe08a; color: #7a5b00;">
                <i class="fa-brands fa-whatsapp" style="color: #25d366;"></i>
                <strong><?php echo count($pending_whatsapp); ?> new order(s)</strong> waiting for order confirmation & bill via WhatsApp.
            </div>
        <?php endif; ?>

        <!-- Orders Layout -->
        <div style="display: flex; flex-direction: column; gap: 30px;">
            <?php if (!empty($orders)): ?>
                <?php foreach ($orders as $order): ?>
                    <div class="admin-card" style="border: 1px solid var(--gray-200);">
                        <h2 style="margin-bottom: 15px; border-bottom: 1px solid var(--gray-100); padding-bottom: 15px; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <span style="font-weight: 700; color: var(--primary);">#DD<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></span>
                                <span style="font-size: 13px; color: var(--gray-500); font-weight: normal; margin-left: 10px;">
                                    <?php echo date('F j, Y, g:i a', strtotime($order['created_at'])); ?>
                                </span>
                            </div>
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
                        </h2>

                        <div class="admin-form-grid">
                            <!-- CUSTOMER DETAILS -->
                            <div>
                                <h3 style="font-size: 14px; font-weight: 600; color: var(--gray-700); text-transform: uppercase; margin-bottom: 10px;">Customer Info</h3>
                                <div style="font-size: 13px; margin-bottom: 5px; color: #555;">
                                    <span style="color: #888; display: inline-block; width: 100px;">Name:</span>
                                    <strong><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></strong>
                                </div>
                                <div style="font-size: 13px; margin-bottom: 5px; color: #555;">
                                    <span style="color: #888; display: inline-block; width: 100px;">Email:</span>
                                    <strong><?php echo htmlspecialchars($order['email']); ?></strong>
                                </div>
                                <div style="font-size: 13px; margin-bottom: 5px; color: #555;">
                                    <span style="color: #888; display: inline-block; width: 100px;">Phone:</span>
                                    <strong><?php echo htmlspecialchars($order['phone']); ?></strong>
                                </div>
                                <div style="font-size: 13px; margin-bottom: 5px; color: #555;">
                                    <span style="color: #888; display: inline-block; width: 100px;">Address:</span>
                                    <strong><?php echo htmlspecialchars($order['address']); ?></strong>
                                </div>
                                <?php if (!empty($order['notes'])): ?>
                                    <div style="font-size: 13px; margin-top: 10px; padding: 10px; background: #fff8f5; border-radius: 8px; color: #555;">
                                        <strong>Notes:</strong> "<?php echo htmlspecialchars($order['notes']); ?>"
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- ORDERED ITEMS -->
                            <div>
                                <h3 style="font-size: 14px; font-weight: 600; color: var(--gray-700); text-transform: uppercase; margin-bottom: 10px;">Items</h3>
                                <?php foreach ($order['items'] as $item): ?>
                                    <div style="display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 5px; color: #555;">
                                        <span><?php echo htmlspecialchars($item['product_name']); ?> × <?php echo $item['quantity']; ?></span>
                                        <strong>Rs. <?php echo number_format($item['price'] * $item['quantity']); ?></strong>
                                    </div>
                                <?php endforeach; ?>
                                <hr style="border: 0; border-top: 1px solid var(--gray-100); margin: 10px 0;">
                                <div style="display: flex; justify-content: space-between; font-size: 14px;">
                                    <span>Total Price:</span>
                                    <strong style="color: var(--primary); font-size: 15px;">Rs. <?php echo number_format($order['total_price']); ?></strong>
                                </div>
                                <div style="font-size: 13px; color: #888; margin-top: 5px;">
                                    <span>Payment Method:</span>
                                    <strong><?php echo htmlspecialchars($order['payment_method']); ?></strong>
                                </div>
                            </div>
                        </div>

                        <!-- ACTIONS ROW -->
                        <div style="margin-top: 25px; border-top: 1px solid var(--gray-100); padding-top: 15px; display: flex; gap: 10px; flex-wrap: wrap;">
                            <?php if ($order['status'] === 'Placed' && empty($order['whatsapp_sent'])): ?>
                                <a href="admin-orders.php?action=send_whatsapp&id=<?php echo $order['id']; ?>" class="btn-primary" style="background: #25d366; padding: 8px 16px; font-size: 13px; text-decoration: none;">
                                    <i class="fa-brands fa-whatsapp" style="margin-right: 5px;"></i> Send Bill via WhatsApp
                                </a>
                            <?php elseif (!empty($order['whatsapp_sent'])): ?>
                                <span style="display: inline-flex; align-items: center; gap: 6px; font-size: 12px; color: #27ae60; background: #e8f8ef; padding: 8px 12px; border-radius: 20px;">
                                    <i class="fa-solid fa-circle-check"></i> Bill Sent via WhatsApp
                                </span>
                                <a href="admin-orders.php?action=send_whatsapp&id=<?php echo $order['id']; ?>" class="btn-primary" style="background: #128C7E; padding: 8px 16px; font-size: 13px; text-decoration: none;">
                                    <i class="fa-brands fa-whatsapp" style="margin-right: 5px;"></i> Resend Bill
                                </a>
                            <?php endif; ?>

                            <?php if ($order['status'] !== 'Cancelled' && $order['status'] !== 'Delivered'): ?>
                                
                                <?php if ($order['status'] === 'Placed'): ?>
                                    <a href="admin-orders.php?action=update_status&id=<?php echo $order['id']; ?>&status=Preparing" class="btn-primary" style="padding: 8px 16px; font-size: 13px; text-decoration: none;">
                                        <i class="fa-solid fa-fire-burner" style="margin-right: 5px;"></i> Start Preparing
                                    </a>
                                <?php endif; ?>

                                <?php if ($order['status'] === 'Preparing'): ?>
                                    <a href="admin-orders.php?action=update_status&id=<?php echo $order['id']; ?>&status=Out+for+Delivery" class="btn-primary" style="background: #34d399; padding: 8px 16px; font-size: 13px; text-decoration: none;">
                                        <i class="fa-solid fa-motorcycle" style="margin-right: 5px;"></i> Dispatch Driver
                                    </a>
                                <?php endif; ?>

                                <?php if ($order['status'] === 'Out for Delivery'): ?>
                                    <a href="admin-orders.php?action=update_status&id=<?php echo $order['id']; ?>&status=Delivered" class="btn-primary" style="background: #27ae60; padding: 8px 16px; font-size: 13px; text-decoration: none;">
                                        <i class="fa-solid fa-check-double" style="margin-right: 5px;"></i> Complete Delivery
                                    </a>
                                <?php endif; ?>

                                <a href="admin-orders.php?action=update_status&id=<?php echo $order['id']; ?>&status=Cancelled" class="btn-primary" style="background: #e74c3c; padding: 8px 16px; font-size: 13px; text-decoration: none;">
                                    <i class="fa-solid fa-ban" style="margin-right: 5px;"></i> Cancel Order
                                </a>
                            <?php else: ?>
                                <a href="admin-orders.php?action=update_status&id=<?php echo $order['id']; ?>&status=Placed" class="btn-primary" style="background: #3498db; padding: 8px 16px; font-size: 13px; text-decoration: none;">
                                    <i class="fa-solid fa-arrows-rotate" style="margin-right: 5px;"></i> Reset Order
                                </a>
                            <?php endif; ?>

                            <?php
                            $wa_text = buildStatusUpdateMessage($order, $order['status']);
                            $wa_card_url = buildWhatsAppUrl($order['phone'], $wa_text);
                            ?>
                            <a href="<?php echo $wa_card_url; ?>" class="btn-primary" style="background: #25d366; padding: 8px 16px; font-size: 13px; text-decoration: none;" target="_blank">
                                <i class="fa-brands fa-whatsapp" style="margin-right: 5px;"></i> Send WhatsApp Update
                            </a>

                            <a href="track-order.php?id=<?php echo $order['id']; ?>" class="btn-primary" style="background: #2c3e50; padding: 8px 16px; font-size: 13px; text-decoration: none; margin-left: auto;" target="_blank">
                                <i class="fa-solid fa-map-location-dot" style="margin-right: 5px;"></i> View Customer Tracking Map
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="admin-card" style="text-align: center; color: #888;">
                    <i class="fa-solid fa-receipt" style="font-size: 40px; margin-bottom: 10px; display: block; color: #ccc;"></i>
                    No orders in database. Place a test order on the store frontend.
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php include 'admin-footer.php'; ?>
