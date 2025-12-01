<?php
// includes/config.php

// بداية الجلسة مرة واحدة فقط
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// إعدادات قاعدة البيانات
define('DB_HOST', 'localhost');
define('DB_NAME', 'gestion_stock');
define('DB_USER', 'root');
define('DB_PASS', '');
define('SITE_NAME', 'نظام إدارة المخزون');

// الاتصال بقاعدة البيانات
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage());
}

// دوال مساعدة
function redirect($url) {
    header("Location: $url");
    exit;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function hasRole($role) {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] == $role;
}

// إنشاء الجداول الأساسية
$pdo->exec("CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    email VARCHAR(100),
    role ENUM('admin', 'manager', 'user') DEFAULT 'user',
    status ENUM('active', 'inactive') DEFAULT 'active',
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// إنشاء المستخدم الافتراضي إذا لم يكن موجوداً
$userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
if ($userCount == 0) {
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    $pdo->prepare("INSERT INTO users (username, password, full_name, email, role) VALUES (?, ?, ?, ?, ?)")
        ->execute(['admin', $password, 'مدير النظام', 'admin@system.com', 'admin']);
}
?>