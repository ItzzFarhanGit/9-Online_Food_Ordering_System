<?php
header('Content-Type: application/json');
require_once 'db.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check login (a regular customer or a logged-in admin may both poll status)
$is_admin = isset($_SESSION['admin_id']);
if (!isset($_SESSION['user_id']) && !$is_admin) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$order_id = (int)($_GET['id'] ?? 0);

if ($order_id <= 0) {
    echo json_encode(['error' => 'Invalid order ID']);
    exit;
}

try {
    if ($is_admin) {
        $stmt = $pdo->prepare("SELECT id, status FROM orders WHERE id = ?");
        $stmt->execute([$order_id]);
    } else {
        $stmt = $pdo->prepare("SELECT id, status FROM orders WHERE id = ? AND user_id = ?");
        $stmt->execute([$order_id, $_SESSION['user_id']]);
    }
    $order = $stmt->fetch();

    if ($order) {
        echo json_encode([
            'id' => $order['id'],
            'status' => $order['status']
        ]);
    } else {
        echo json_encode(['error' => 'Order not found']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error']);
}
exit;
?>
