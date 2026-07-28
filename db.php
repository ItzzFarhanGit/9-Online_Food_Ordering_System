<?php
$host = 'localhost';
$db   = 'delight_dining';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);

     require_once __DIR__ . '/whatsapp-helper.php';
     ensureWhatsAppSentColumn($pdo);
} catch (\PDOException $e) {
     die("Database connection failed. Please make sure MySQL (XAMPP/WAMP) is running. If you haven't set up the database yet, run <a href='setup-db.php' style='color: #FF6B00; font-weight: 600;'>setup-db.php</a> to automatically build and seed the database.");
}
?>
