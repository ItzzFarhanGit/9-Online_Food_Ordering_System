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
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($name) || empty($email) || empty($phone) || empty($password) || empty($confirm_password)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } else {
        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'This email address is already registered.';
            } else {
                // Insert user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)");
                $stmt->execute([$name, $email, $phone, $hashed_password]);
                
                $_SESSION['success_msg'] = 'Registration successful! Please login.';
                header("Location: login.php");
                exit;
            }
        } catch (PDOException $e) {
            $error = 'Registration failed. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Delight Dinning</title>
    <link rel="stylesheet" href="register.css">
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

<section class="register-section">
    <!-- LEFT -->
    <div class="register-image">
        <div class="overlay">
            <img src="logo.png" alt="Logo">
            <h1>Delight Dinning</h1>
            <p>Create your account and enjoy delicious meals anytime.</p>
        </div>
    </div>

    <!-- RIGHT -->
    <div class="register-container">
        <div class="register-card">
            <h2>Create Account ✨</h2>
            <p>Join us and start ordering your favorite meals.</p>

            <?php if (!empty($error)): ?>
                <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form action="register.php" method="POST">
                <div class="input-box">
                    <label>Full Name</label>
                    <input type="text" name="name" placeholder="John Doe" value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
                </div>

                <div class="input-box">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="john@example.com" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                </div>

                <div class="input-box">
                    <label>Phone Number</label>
                    <input type="text" name="phone" placeholder="+94 76 978 8951" value="<?php echo htmlspecialchars($phone ?? ''); ?>" required>
                </div>

                <div class="input-box">
                    <label>Password</label>
                    <div class="password-box">
                        <input type="password" name="password" id="password" placeholder="Enter password" required>
                        <i class="fa-solid fa-eye" id="togglePassword"></i>
                    </div>
                </div>

                <div class="input-box">
                    <label>Confirm Password</label>
                    <div class="password-box">
                        <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm password" required>
                        <i class="fa-solid fa-eye" id="toggleConfirmPassword"></i>
                    </div>
                </div>

                <button type="submit" class="register-btn">Register</button>
            </form>

            <div class="divider"><span>OR</span></div>

            <a href="#" class="google-btn">
                <i class="fab fa-google"></i>
                Continue with Google
            </a>

            <p class="login-link">
                Already have an account?
                <a href="login.php">Login</a>
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

    const toggleConfirmPassword = document.querySelector('#toggleConfirmPassword');
    const confirmPassword = document.querySelector('#confirm_password');
    toggleConfirmPassword.addEventListener('click', function (e) {
        const type = confirmPassword.getAttribute('type') === 'password' ? 'text' : 'password';
        confirmPassword.setAttribute('type', type);
        this.classList.toggle('fa-eye-slash');
    });
</script>
</body>
</html>
