<?php
session_start();

// إذا كان المستخدم مسجل دخول، توجيه للوحة التحكم
if (isset($_SESSION['user_id'])) {
    header("Location: modules/dashboard/index.php");
    exit;
}

// إعدادات قاعدة البيانات
define('DB_HOST', 'localhost');
define('DB_NAME', 'gestion_stock');
define('DB_USER', 'root');
define('DB_PASS', '');

// الاتصال بقاعدة البيانات
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    // تجاهل خطأ قاعدة البيانات للصفحة الرئيسية
    $pdo = null;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if ($pdo) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND status = 'active'");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_role'] = $user['role'];
            
            // تحديث آخر دخول
            $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?")->execute([$user['id']]);
            
            header("Location: modules/dashboard/index.php");
            exit;
        } else {
            $error = "اسم المستخدم أو كلمة المرور غير صحيحة";
        }
    } else {
        $error = "خطأ في الاتصال بقاعدة البيانات";
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - نظام إدارة المخزون المتكامل</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- CSS المخصص -->
    <style>
        /* أنسخ محتوى ملف style.css هنا مؤقتاً */
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --dark: #1d3557;
            --light: #f8f9fa;
            --gradient: linear-gradient(135deg, #4361ee, #3a0ca3);
        }

        * {
            font-family: 'Tajawal', sans-serif;
        }

        body {
            background: var(--gradient);
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .login-container {
            position: relative;
            z-index: 2;
            max-width: 420px;
            margin: 0 auto;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: none;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .card-header {
            background: var(--gradient);
            border: none;
            padding: 2rem 1rem;
            text-align: center;
        }

        .logo {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            border: 3px solid rgba(255, 255, 255, 0.3);
        }

        .logo i {
            font-size: 2rem;
            color: white;
        }

        .btn-login {
            background: var(--gradient);
            border: none;
            border-radius: 12px;
            padding: 12px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="login-card shadow-lg">
                <div class="card-header text-white">
                    <div class="logo">
                        <i class="fas fa-warehouse"></i>
                    </div>
                    <h3 class="mb-1">نظام إدارة المخزون</h3>
                    <p class="mb-0 opacity-75">StockFlow Pro</p>
                </div>
                
                <div class="card-body p-4">
                    <?php if(isset($error)): ?>
                        <div class="alert alert-danger d-flex align-items-center" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?= $error ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="username" class="form-label fw-bold text-dark">
                                <i class="fas fa-user me-2 text-primary"></i>اسم المستخدم
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="username" 
                                   name="username" 
                                   placeholder="أدخل اسم المستخدم" 
                                   required
                                   value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
                            <div class="invalid-feedback">يرجى إدخال اسم المستخدم</div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="password" class="form-label fw-bold text-dark">
                                <i class="fas fa-lock me-2 text-primary"></i>كلمة المرور
                            </label>
                            <input type="password" 
                                   class="form-control" 
                                   id="password" 
                                   name="password" 
                                   placeholder="أدخل كلمة المرور" 
                                   required>
                            <div class="invalid-feedback">يرجى إدخال كلمة المرور</div>
                        </div>
                        
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-login text-white fw-bold py-2">
                                <i class="fas fa-sign-in-alt me-2"></i>تسجيل الدخول
                            </button>
                        </div>
                        
                        <div class="text-center">
                            <small class="text-muted">
                                المستخدم الافتراضي: <strong>admin</strong> / كلمة المرور: <strong>admin123</strong>
                            </small>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // تفعيل التحقق من النماذج
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        form.classList.add('was-validated')
                    }, false)
                })
        })()
    </script>
</body>
</html>