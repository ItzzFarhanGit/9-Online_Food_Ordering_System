<?php
require_once 'db.php';
require_once 'whatsapp-helper.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

ensureWhatsAppSentColumn($pdo);

$lastSeenId = (int) ($_GET['last_seen_id'] ?? 0);

try {
    $stmt = $pdo->prepare("
        SELECT id, first_name, last_name, phone, total_price, created_at
        FROM orders
        WHERE status = 'Placed'
          AND whatsapp_sent = 0
          AND id > ?
        ORDER BY id ASC
    ");
    $stmt->execute([$lastSeenId]);
    $orders = $stmt->fetchAll();

    $payload = [];

    foreach ($orders as $order) {
        $items = fetchOrderItems($pdo, (int) $order['id']);
        $message = buildOrderConfirmationMessage($order, $items);

        $payload[] = [
            'id' => (int) $order['id'],
            'order_number' => getOrderNumber((int) $order['id']),
            'customer_name' => trim($order['first_name'] . ' ' . $order['last_name']),
            'total_price' => number_format((float) $order['total_price']),
            'whatsapp_url' => buildWhatsAppUrl($order['phone'], $message),
            'send_url' => 'admin-orders.php?action=send_whatsapp&id=' . (int) $order['id'],
        ];
    }

    echo json_encode([
        'count' => count($payload),
        'orders' => $payload,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to check new orders.']);
}
