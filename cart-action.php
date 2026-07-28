<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php';

$action = $_GET['action'] ?? '';
$id = (int)($_GET['id'] ?? 0);
$qty = (int)($_GET['qty'] ?? 1);

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($id > 0) {
    // Verify product exists
    try {
        $stmt = $pdo->prepare("SELECT id FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch();
        
        if ($product) {
            switch ($action) {
                case 'add':
                    $_SESSION['cart'][$id] = ($_SESSION['cart'][$id] ?? 0) + $qty;
                    break;
                case 'update':
                    if ($qty > 0) {
                        $_SESSION['cart'][$id] = $qty;
                    } else {
                        unset($_SESSION['cart'][$id]);
                    }
                    break;
                case 'remove':
                    unset($_SESSION['cart'][$id]);
                    break;
            }
        }
    } catch (PDOException $e) {
        // Log or handle error silently
    }
}

if ($action === 'clear') {
    $_SESSION['cart'] = [];
}

// Redirect back to the page the user came from
$referer = $_SERVER['HTTP_REFERER'] ?? '';
if (!empty($referer) && strpos($referer, 'cart-action.php') === false) {
    header("Location: " . $referer);
} else {
    if ($action === 'add') {
        header("Location: menu.php");
    } else {
        header("Location: cart.php");
    }
}
exit;
?>
