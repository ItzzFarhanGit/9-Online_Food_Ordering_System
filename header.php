<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- ================= NAVBAR ================= -->
<style>
    .nav-buttons {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    .user-welcome {
        font-size: 14px;
        font-weight: 500;
        color: #333;
    }
    .admin-badge {
        background-color: #e74c3c !important;
        color: #fff !important;
    }
    header nav ul {
        display: flex;
        list-style: none;
        gap: 20px;
        margin: 0;
        padding: 0;
    }
</style>
<header>
    <div class="container navbar">
        <a href="index.php" class="logo">
            <img src="logo.png" alt="Logo">
            <span>Delight Dinning</span>
        </a>
        <nav>
            <ul>
                <li><a href="index.php" class="<?php echo ($current_page == 'index.php' || $current_page == '') ? 'active' : ''; ?>">Home</a></li>
                <li><a href="menu.php" class="<?php echo $current_page == 'menu.php' ? 'active' : ''; ?>">Menu</a></li>
                <li><a href="about.php" class="<?php echo $current_page == 'about.php' ? 'active' : ''; ?>">About</a></li>
                <li><a href="contact.php" class="<?php echo $current_page == 'contact.php' ? 'active' : ''; ?>">Contact</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="my-orders.php" class="<?php echo $current_page == 'my-orders.php' ? 'active' : ''; ?>">My Orders</a></li>
                    <li><a href="profile.php" class="<?php echo $current_page == 'profile.php' ? 'active' : ''; ?>">My Profile</a></li>
                <?php endif; ?>
                <?php if (isset($_SESSION['admin_id'])): ?>
                    <li><a href="admin-dashboard.php" class="<?php echo $current_page == 'admin-dashboard.php' ? 'active' : ''; ?>" style="color: #FF6B00; font-weight: 600;">Admin Console</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <div class="nav-buttons">
            <a href="cart.php" class="cart">
                <i class="fa-solid fa-cart-shopping"></i>
                <span><?php echo isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0; ?></span>
            </a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="profile.php" class="user-welcome" style="text-decoration: none;">Hi, <?php echo htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]); ?></a>
                <a href="logout.php" class="login-btn">Logout</a>
            <?php else: ?>
                <a href="login.php" class="login-btn">Login</a>
            <?php endif; ?>
        </div>
    </div>
</header>
<?php include 'success-toast.php'; ?>
