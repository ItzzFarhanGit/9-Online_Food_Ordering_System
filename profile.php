<?php
require_once 'db.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['success_msg'] = 'Please login to view your profile.';
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$error = '';
$password_error = '';

// Fetch latest user details
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    $user = null;
}

if (!$user) {
    header("Location: logout.php");
    exit;
}

// Handle Profile Details Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if (empty($name) || empty($phone)) {
        $error = 'Name and phone number are required.';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ? WHERE id = ?");
            $stmt->execute([$name, $phone, $user_id]);

            $_SESSION['user_name'] = $name;
            $_SESSION['user_phone'] = $phone;
            $_SESSION['success_msg'] = 'Profile updated successfully!';
            header("Location: profile.php");
            exit;
        } catch (PDOException $e) {
            $error = 'Failed to update profile. Please try again later.';
        }
    }
    // refresh local copy on error so the form re-shows entered values
    $user['name'] = $name;
    $user['phone'] = $phone;
}

// Handle Password Change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_new_password'] ?? '';

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $password_error = 'Please fill in all password fields.';
    } elseif (!password_verify($current_password, $user['password'])) {
        $password_error = 'Your current password is incorrect.';
    } elseif (strlen($new_password) < 6) {
        $password_error = 'New password must be at least 6 characters long.';
    } elseif ($new_password !== $confirm_password) {
        $password_error = 'New passwords do not match.';
    } else {
        try {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed, $user_id]);

            $_SESSION['success_msg'] = 'Password changed successfully!';
            header("Location: profile.php");
            exit;
        } catch (PDOException $e) {
            $password_error = 'Failed to change password. Please try again later.';
        }
    }
}

// Order stats for a quick summary on the profile
$order_count = 0;
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM orders WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $order_count = (int) ($stmt->fetch()['cnt'] ?? 0);
} catch (PDOException $e) {
    $order_count = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | Delight Dinning</title>
    <link rel="stylesheet" href="register.css">
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        .profile-section {
            max-width: 900px;
            margin: 0 auto;
            padding: 60px 20px 80px;
        }
        .profile-header {
            text-align: center;
            margin-bottom: 40px;
        }
        .profile-header h1 {
            font-size: 32px;
            color: #1F2937;
        }
        .profile-header p {
            color: #6b7280;
            margin-top: 8px;
        }
        .profile-avatar {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: #FF6B00;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            font-weight: 700;
            margin: 0 auto 15px;
        }
        .profile-stat {
            display: inline-block;
            background: #fff8f2;
            border: 1px solid #ffe4cc;
            color: #FF6B00;
            font-weight: 600;
            font-size: 13px;
            padding: 6px 16px;
            border-radius: 20px;
            margin-top: 10px;
        }
        .profile-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        @media (max-width: 800px) {
            .profile-grid { grid-template-columns: 1fr; }
        }
        .profile-card {
            background: #fff;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            border: 1px solid rgba(255, 107, 0, 0.08);
        }
        .profile-card h2 {
            font-size: 18px;
            margin-bottom: 20px;
            color: #1F2937;
        }
        .error-msg {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 13px;
            text-align: center;
        }
        .readonly-box {
            background: #f9fafb;
            color: #888;
        }
    </style>
</head>
<body>

    <?php include 'header.php'; ?>

    <section class="profile-section">
        <div class="profile-header">
            <div class="profile-avatar"><?php echo strtoupper(substr($user['name'], 0, 1)); ?></div>
            <h1><?php echo htmlspecialchars($user['name']); ?></h1>
            <p>Member since <?php echo date('F Y', strtotime($user['created_at'])); ?></p>
            <span class="profile-stat"><i class="fa-solid fa-bag-shopping"></i> <?php echo $order_count; ?> Order<?php echo $order_count == 1 ? '' : 's'; ?> Placed</span>
        </div>

        <div class="profile-grid">
            <!-- EDIT DETAILS -->
            <div class="profile-card">
                <h2><i class="fa-solid fa-user-pen"></i> Profile Details</h2>

                <?php if (!empty($error)): ?>
                    <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form action="profile.php" method="POST">
                    <input type="hidden" name="update_profile" value="1">
                    <div class="input-box">
                        <label>Full Name</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                    </div>

                    <div class="input-box">
                        <label>Email Address</label>
                        <input type="email" class="readonly-box" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                    </div>

                    <div class="input-box">
                        <label>Phone Number</label>
                        <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                    </div>

                    <button type="submit" class="register-btn">Save Changes</button>
                </form>
            </div>

            <!-- CHANGE PASSWORD -->
            <div class="profile-card">
                <h2><i class="fa-solid fa-lock"></i> Change Password</h2>

                <?php if (!empty($password_error)): ?>
                    <div class="error-msg"><?php echo htmlspecialchars($password_error); ?></div>
                <?php endif; ?>

                <form action="profile.php" method="POST">
                    <input type="hidden" name="change_password" value="1">
                    <div class="input-box">
                        <label>Current Password</label>
                        <input type="password" name="current_password" placeholder="Enter current password" required>
                    </div>

                    <div class="input-box">
                        <label>New Password</label>
                        <input type="password" name="new_password" placeholder="Enter new password" required>
                    </div>

                    <div class="input-box">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_new_password" placeholder="Confirm new password" required>
                    </div>

                    <button type="submit" class="register-btn">Update Password</button>
                </form>
            </div>
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <a href="my-orders.php" style="color: #FF6B00; font-weight: 600; text-decoration: none; margin-right: 20px;">
                <i class="fa-solid fa-receipt"></i> View My Orders
            </a>
            <a href="logout.php" style="color: #888; font-weight: 600; text-decoration: none;">
                <i class="fa-solid fa-right-from-bracket"></i> Logout
            </a>
        </div>
    </section>

    <?php include 'footer.php'; ?>

</body>
</html>
