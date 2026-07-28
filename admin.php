<?php
// This page was an unprotected duplicate of admin-orders.php (any logged-in
// customer could reach it and view/edit every customer's orders). It has been
// retired in favor of the properly admin-authenticated admin-orders.php page.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['admin_id'])) {
    header("Location: admin-orders.php");
} else {
    header("Location: admin-login.php");
}
exit;
