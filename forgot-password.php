<?php
require_once 'db.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';
$step = isset($_SESSION['reset_user_id']) ? 2 : 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // STEP 1: Verify identity using email + registered phone number
    if (isset($_POST['verify'])) {
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        if (empty($email) || empty($phone)) {
            $error = 'Please enter both your email address and registered phone number.';
        } else {
            try {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND phone = ?");
                $stmt->execute([$email, $phone]);
                $user = $stmt->fetch();

                if ($user) {
                    $_SESSION['reset_user_id'] = $user['id'];
                    $step = 2;
                } else {
                    $error = 'We could not find an account matching that email and phone number.';
                }
            } catch (PDOException $e) {
                $error = 'Something went wrong. Please try again later.';
            }
        }
    }

    // STEP 2: Set a new password
    if (isset($_POST['reset'])) {
        if (!isset($_SESSION['reset_user_id'])) {
            $error = 'Your reset session has expired. Please verify your identity again.';
            $step = 1;
        } else {
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            if (empty($password) || empty($confirm_password)) {
                $error = 'Please fill in both password fields.';
                $step = 2;
            } elseif (strlen($password) < 6) {
                $error = 'Password must be at least 6 characters long.';
                $step = 2;
            } elseif ($password !== $confirm_password) {
                $error = 'Passwords do not match.';
                $step = 2;
            } else {
                try {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt->execute([$hashed_password, $_SESSION['reset_user_id']]);

                    unset($_SESSION['reset_user_id']);
                    $_SESSION['success_msg'] = 'Password reset successful! Please login with your new password.';
                    header("Location: login.php");
                    exit;
                } catch (PDOException $e) {
                    $error = 'Failed to reset password. Please try again later.';
                    $step = 2;
                }
            }
        }
    }
}

// Allow the user to cancel and start the verification over
if (isset($_GET['start_over'])) {
    unset($_SESSION['reset_user_id']);
    header("Location: forgot-password.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | Delight Dinning</title>
    <link rel="stylesheet" href="login.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        .error-msg {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 14px;
            text-align: center;
        }
        .info-note {
            background-color: #fff8e6;
            color: #7a5b00;
            border: 1px solid #ffe08a;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 18px;
            line-height: 1.5;
        }
        .step-indicator {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
            font-size: 12px;
            font-weight: 600;
            color: #aaa;
        }
        .step-indicator span.active { color: var(--primary); }
    </style>
</head>
<body>

<?php include 'success-toast.php'; ?>

<section class="login-section">
    <!-- LEFT SIDE -->
    <div class="login-image">
        <div class="overlay">
            <img src="logo.png" alt="Logo">
            <h1>Delight Dinning</h1>
            <p>Delicious meals delivered fresh to your doorstep.</p>
        </div>
    </div>

    <!-- RIGHT SIDE -->
    <div class="login-container">
        <div class="login-card">
            <h2>Reset Your Password 🔑</h2>
            <p>We'll help you get back into your account in two quick steps.</p>

            <div class="step-indicator">
                <span class="<?php echo $step == 1 ? 'active' : ''; ?>">1. Verify Identity</span>
                <span>&rarr;</span>
                <span class="<?php echo $step == 2 ? 'active' : ''; ?>">2. Set New Password</span>
            </div>

            <?php if (!empty($error)): ?>
                <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($step == 1): ?>
                <div class="info-note">
                    <i class="fa-solid fa-circle-info"></i>
                    Enter the email address and phone number you registered with. We'll use these to confirm it's really you.
                </div>

                <form action="forgot-password.php" method="POST">
                    <input type="hidden" name="verify" value="1">
                    <div class="input-box">
                        <label>Email Address</label>
                        <input type="email" name="email" placeholder="Enter your registered email" required>
                    </div>

                    <div class="input-box">
                        <label>Phone Number</label>
                        <input type="text" name="phone" placeholder="Enter your registered phone number" required>
                    </div>

                    <button type="submit" class="login-btn">Verify Identity</button>
                </form>
            <?php else: ?>
                <div class="info-note">
                    <i class="fa-solid fa-circle-check" style="color: #27ae60;"></i>
                    Identity verified! Please choose a new password below.
                </div>

                <form action="forgot-password.php" method="POST">
                    <input type="hidden" name="reset" value="1">
                    <div class="input-box">
                        <label>New Password</label>
                        <div class="password-box">
                            <input type="password" name="password" id="password" placeholder="Enter new password" required>
                            <i class="fa-solid fa-eye" id="togglePassword"></i>
                        </div>
                    </div>

                    <div class="input-box">
                        <label>Confirm New Password</label>
                        <div class="password-box">
                            <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm new password" required>
                            <i class="fa-solid fa-eye" id="toggleConfirmPassword"></i>
                        </div>
                    </div>

                    <button type="submit" class="login-btn">Reset Password</button>
                </form>

                <p class="register-link" style="margin-top: 10px;">
                    <a href="forgot-password.php?start_over=1">Not you? Start over</a>
                </p>
            <?php endif; ?>

            <p class="register-link">
                Remembered your password?
                <a href="login.php">Back to Login</a>
            </p>

            <a href="index.php" class="back-home">
                <i class="fa-solid fa-arrow-left"></i>
                Back to Home
            </a>
        </div>
    </div>
</section>

<script>
    function wirePasswordToggle(toggleId, inputId) {
        const toggle = document.querySelector(toggleId);
        const input = document.querySelector(inputId);
        if (!toggle || !input) return;
        toggle.addEventListener('click', function () {
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');
        });
    }
    wirePasswordToggle('#togglePassword', '#password');
    wirePasswordToggle('#toggleConfirmPassword', '#confirm_password');
</script>
</body>
</html>
