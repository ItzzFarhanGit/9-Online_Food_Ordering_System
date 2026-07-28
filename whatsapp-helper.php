<?php

function formatWhatsAppPhone(string $phone): string
{
    $clean = preg_replace('/[^0-9]/', '', $phone);

    if (strpos($clean, '00') === 0) {
        $clean = substr($clean, 2);
    }

    if (strlen($clean) === 10 && $clean[0] === '0') {
        $clean = '94' . substr($clean, 1);
    } elseif (strlen($clean) === 9 && $clean[0] === '7') {
        $clean = '94' . $clean;
    }

    return $clean;
}

function getOrderNumber(int $orderId): string
{
    return '#DD' . str_pad((string) $orderId, 6, '0', STR_PAD_LEFT);
}

function getTrackOrderUrl(int $orderId): string
{
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $basePath = '/Food%20Ordering%20System';

    return $protocol . $host . $basePath . '/track-order.php?id=' . $orderId;
}

function buildOrderConfirmationMessage(array $order, array $items): string
{
    $orderNum = getOrderNumber((int) $order['id']);
    $customerName = trim($order['first_name'] . ' ' . $order['last_name']);
    $orderDate = $order['created_at'] ?? date('Y-m-d H:i:s');

    $msg = "*🧾 DELIGHT DINING - ORDER CONFIRMATION & BILL*\n";
    $msg .= "---------------------------------------------\n";
    $msg .= "Hello " . $order['first_name'] . ", your order has been *confirmed*!\n\n";
    $msg .= "*Order:* " . $orderNum . "\n";
    $msg .= "*Date:* " . $orderDate . "\n";
    $msg .= "*Customer:* " . $customerName . "\n";
    $msg .= "*Delivery Address:* " . $order['address'] . "\n";
    $msg .= "*Payment:* " . $order['payment_method'] . "\n";
    $msg .= "---------------------------------------------\n";
    $msg .= "*Items Ordered:*\n";

    foreach ($items as $item) {
        $lineTotal = $item['price'] * $item['quantity'];
        $productName = $item['product_name'] ?? $item['name'] ?? 'Item';
        $msg .= "• " . $productName . " x " . $item['quantity'] . " (Rs. " . number_format($lineTotal) . ")\n";
    }

    $msg .= "---------------------------------------------\n";
    $msg .= "*Subtotal:* Rs. " . number_format($order['subtotal']) . "\n";
    $msg .= "*Delivery:* Rs. " . number_format($order['delivery_fee']) . "\n";
    $msg .= "*Service Charge:* Rs. " . number_format($order['service_charge']) . "\n";

    if (($order['discount_amount'] ?? 0) > 0) {
        $msg .= "*Discount:* - Rs. " . number_format($order['discount_amount']) . "\n";
    }

    $msg .= "---------------------------------------------\n";
    $msg .= "*Total Amount:* *Rs. " . number_format($order['total_price']) . "*\n";
    $msg .= "---------------------------------------------\n";
    $msg .= "Estimated delivery: *30 - 45 minutes*.\n";
    $msg .= "Your delicious meal is now being prepared. 🍕🍔\n\n";
    $msg .= "Track your order here:\n" . getTrackOrderUrl((int) $order['id']) . "\n\n";
    $msg .= "Thank you for ordering with Delight Dining!";

    return $msg;
}

function buildStatusUpdateMessage(array $order, string $status): string
{
    $orderNum = getOrderNumber((int) $order['id']);

    return "Hello " . $order['first_name'] . ", this is Delight Dining. Your order *"
        . $orderNum . "* status has been updated to *_" . $status . "_*. Track live delivery here: "
        . getTrackOrderUrl((int) $order['id']);
}

function buildWhatsAppUrl(string $phone, string $message): string
{
    return 'https://wa.me/' . formatWhatsAppPhone($phone) . '?text=' . urlencode($message);
}

function fetchOrderItems(PDO $pdo, int $orderId): array
{
    $stmt = $pdo->prepare("
        SELECT oi.*, p.name AS product_name
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$orderId]);

    return $stmt->fetchAll();
}

function ensureWhatsAppSentColumn(PDO $pdo): void
{
    static $checked = false;

    if ($checked) {
        return;
    }

    $checked = true;

    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM orders LIKE 'whatsapp_sent'");
        if (!$stmt->fetch()) {
            $pdo->exec("ALTER TABLE orders ADD COLUMN whatsapp_sent TINYINT(1) NOT NULL DEFAULT 0 AFTER status");
        }
    } catch (PDOException $e) {
        // Ignore migration errors on read-only or restricted environments.
    }
}
