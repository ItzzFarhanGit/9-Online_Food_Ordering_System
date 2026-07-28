<?php
require_once 'db.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirect to dashboard if already logged in as admin
if (isset($_SESSION['admin_id'])) {
    header("Location: admin-dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Both username and password are required.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password'])) {
                // Login Success
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_user'] = $admin['username'];
                
                header("Location: admin-dashboard.php");
                exit;
            } else {
                $error = 'Invalid username or password.';
            }
        } catch (PDOException $e) {
            $error = 'Database Error: ' . $e->getMessage() . '. Please visit <a href="setup-db.php" style="color: #FF6B00; text-decoration: underline; font-weight: 600;">setup-db.php</a> first to create and seed the database tables.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Delight Dinning Console</title>
    <link rel="stylesheet" href="admin-style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>
<body class="admin-login-body">

    <div class="admin-login-card">
        <div class="logo">
            <img src="logo.png" alt="Logo">
            <span>Delight Dinning</span>
        </div>
        <h2>Admin Console</h2>
        <p>Log in with your administrator credentials.</p>

        <?php if (!empty($error)): ?>
            <div class="admin-alert error" style="margin-bottom: 20px; padding: 10px 15px; font-size: 13px;">
                <i class="fa-solid fa-triangle-exclamation"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form action="admin-login.php" method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="e.g. admin" required autofocus>
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn-primary" style="width: 100%; margin-top: 10px;">
                Log In to Console
            </button>
        </form>

        <p style="margin-top: 30px; font-size: 12px; color: var(--gray-500);">
            <a href="index.php" style="color: var(--primary); text-decoration: none;">
                <i class="fa-solid fa-arrow-left"></i> Back to Main Site
            </a>
        </p>
    </div>

</body>
</html>
