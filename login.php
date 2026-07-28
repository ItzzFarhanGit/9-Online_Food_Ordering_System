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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'All fields are required.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Successful login
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_phone'] = $user['phone'];
                $_SESSION['success_msg'] = 'Login successful!';

                // Redirect to checkout if cart is not empty, otherwise to index
                if (!empty($_SESSION['cart'])) {
                    header("Location: checkout.php");
                } else {
                    header("Location: index.php");
                }
                exit;
            } else {
                $error = 'Invalid email or password.';
            }
        } catch (PDOException $e) {
            $error = 'Login failed. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Delight Dinning</title>
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
            <h2>Welcome Back 👋</h2>
            <p>Sign in to continue your food ordering experience.</p>

            <?php if (!empty($error)): ?>
                <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="input-box">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="Enter your email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                </div>

                <div class="input-box">
                    <label>Password</label>
                    <div class="password-box">
                        <input type="password" name="password" id="password" placeholder="Enter your password" required>
                        <i class="fa-solid fa-eye" id="togglePassword"></i>
                    </div>
                </div>

                <div class="options">
                    <label>
                        <input type="checkbox" name="remember">
                        Remember Me
                    </label>
                    <a href="forgot-password.php">Forgot Password?</a>
                </div>

                <button type="submit" class="login-btn">Login</button>
            </form>

            <div class="divider">
                <span>OR</span>
            </div>

            <a href="#" class="google-btn">
                <i class="fab fa-google"></i>
                Continue with Google
            </a>

            <p class="register-link">
                Don't have an account?
                <a href="register.php">Create Account</a>
            </p>

            <p class="register-link" style="margin-top: 10px;">
                Are you an administrator?
                <a href="admin-login.php" style="color: #FF6B00; font-weight: 600;">Admin Login</a>
            </p>

            <a href="index.php" class="back-home">
                <i class="fa-solid fa-arrow-left"></i>
                Back to Home
            </a>
        </div>
    </div>
</section>

<script>
    // Toggle Password Visibility
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#password');
    togglePassword.addEventListener('click', function (e) {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        this.classList.toggle('fa-eye-slash');
    });
</script>
</body>
</html>
