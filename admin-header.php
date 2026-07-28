<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Security Check: Redirect if not logged in as Admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin-login.php");
    exit;
}

$current_admin_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delight Dinning Console | Admin Portal</title>
    <!-- Stylesheets -->
    <link rel="stylesheet" href="admin-style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>
<body>

    <!-- ================= ADMIN NAVBAR ================= -->
    <header class="admin-navbar">
        <div class="admin-nav-container">
            <a href="admin-dashboard.php" class="admin-logo">
                <img src="logo.png" alt="Logo">
                <span>Delight Dinning</span>
                <span class="badge">Console</span>
            </a>
            
            <nav>
                <ul class="admin-menu">
                    <li><a href="admin-dashboard.php" class="<?php echo $current_admin_page == 'admin-dashboard.php' ? 'active' : ''; ?>">Dashboard</a></li>
                    <li><a href="admin-orders.php" class="<?php echo $current_admin_page == 'admin-orders.php' ? 'active' : ''; ?>">Manage Orders</a></li>
                    <li><a href="admin-foods.php" class="<?php echo $current_admin_page == 'admin-foods.php' ? 'active' : ''; ?>">Manage Foods</a></li>
                    <li><a href="index.php" target="_blank">View Site <i class="fa-solid fa-arrow-up-right-from-square" style="font-size: 10px;"></i></a></li>
                </ul>
            </nav>

            <div class="admin-nav-right">
                <span class="admin-user-welcome">Hi, <?php echo htmlspecialchars($_SESSION['admin_user']); ?></span>
                <a href="logout.php" class="admin-logout-btn">Logout</a>
            </div>
        </div>
    </header>
