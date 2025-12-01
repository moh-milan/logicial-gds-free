<?php
// config/database.php
if (session_status() == PHP_SESSION_NONE) {
    @session_start();
}

define('CURRENCY', 'د.ج');
define('COMPANY_NAME', 'StockFlow الجزائر');

// اتصال قاعدة البيانات
function getDBConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $pdo = new PDO("mysql:host=localhost;dbname=gestion_stock;charset=utf8mb4", "root", "", [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
        } catch(PDOException $e) {
            // تجاهل الخطأ للسماح للصفحة بالعمل بدون قاعدة بيانات
            $pdo = null;
        }
    }
    
    return $pdo;
}

// ==============================================
// وظائف إدارة المنتجات
// ==============================================

function setupProductsTable() {
    $pdo = getDBConnection();
    if (!$pdo) return false;
    
    try {
        // إنشاء الجدول
        $sql = "CREATE TABLE IF NOT EXISTS products (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            category VARCHAR(100) DEFAULT 'عام',
            price DECIMAL(10,2) DEFAULT 0,
            cost_price DECIMAL(10,2) DEFAULT 0,
            stock_quantity INT DEFAULT 0,
            min_stock INT DEFAULT 5,
            barcode VARCHAR(100),
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $pdo->exec($sql);
        
        // التحقق مما إذا كان الجدول فارغاً وإضافة بيانات تجريبية
        $count = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
        if ($count == 0) {
            $sample_data = [
                ['سامسونج جالاكسي S24', 'هاتف ذكي بشاشة 6.1 بوصة', 'هواتف ذكية', 45000, 38000, 48, 5, 'PROD-001'],
                ['لابتوب ديل XPS 13', 'لابتوب بشاشة 13 بوصة', 'أجهزة كمبيوتر', 120000, 95000, 5, 3, 'PROD-002'],
                ['طابعة ليزر HP', 'طابعة ليزر ملونة', 'طابعات', 35000, 28000, 0, 2, 'PROD-003']
            ];
            
            $stmt = $pdo->prepare("INSERT INTO products (name, description, category, price, cost_price, stock_quantity, min_stock, barcode) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            
            foreach ($sample_data as $data) {
                $stmt->execute($data);
            }
        }
        
        return true;
    } catch(PDOException $e) {
        return false;
    }
}

function addProduct($productData) {
    $pdo = getDBConnection();
    if (!$pdo) return false;
    
    try {
        $sql = "INSERT INTO products (name, description, category, price, cost_price, stock_quantity, min_stock, barcode) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            $productData['name'],
            $productData['description'],
            $productData['category'],
            $productData['price'],
            $productData['cost_price'],
            $productData['stock_quantity'],
            $productData['min_stock'],
            $productData['barcode']
        ]);
    } catch(PDOException $e) {
        return false;
    }
}

function getAllProducts($category = '', $search = '') {
    $pdo = getDBConnection();
    if (!$pdo) return [];
    
    try {
        $sql = "SELECT * FROM products WHERE status = 'active'";
        $params = [];
        
        if (!empty($category)) {
            $sql .= " AND category = ?";
            $params[] = $category;
        }
        
        if (!empty($search)) {
            $sql .= " AND (name LIKE ? OR description LIKE ? OR barcode LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        return [];
    }
}

// ==============================================
// وظائف إدارة العملاء
// ==============================================

function setupClientsTable() {
    $pdo = getDBConnection();
    if (!$pdo) return false;
    
    try {
        $sql = "CREATE TABLE IF NOT EXISTS clients (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255),
            phone VARCHAR(20),
            address TEXT,
            company VARCHAR(255),
            type ENUM('individual', 'company') DEFAULT 'individual',
            notes TEXT,
            status ENUM('active', 'inactive') DEFAULT 'active',
            total_orders INT DEFAULT 0,
            total_spent DECIMAL(15,2) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $pdo->exec($sql);
        
        // إضافة بيانات تجريبية إذا كان الجدول فارغاً
        $count = $pdo->query("SELECT COUNT(*) FROM clients")->fetchColumn();
        if ($count == 0) {
            $sample_data = [
                ['أحمد بوعزة', 'ahmed.bouazza@dz.com', '0551-234-567', 'الجزائر العاصمة', 'شركة الإلكترونيات المتطورة', 'company'],
                ['فاطمة الزهراء', 'fatima.zohra@dz.com', '0552-345-678', 'وهران', '', 'individual'],
                ['خالد معمري', 'khaled.maamri@dz.com', '0553-456-789', 'قسنطينة', 'مؤسسة معمري للتجارة', 'company'],
                ['سميرة قاسم', 'samira.kacem@dz.com', '0554-567-890', 'عنابة', '', 'individual']
            ];
            
            $stmt = $pdo->prepare("INSERT INTO clients (name, email, phone, address, company, type) VALUES (?, ?, ?, ?, ?, ?)");
            
            foreach ($sample_data as $data) {
                $stmt->execute($data);
            }
        }
        
        return true;
    } catch(PDOException $e) {
        return false;
    }
}

function addClient($clientData) {
    $pdo = getDBConnection();
    if (!$pdo) return false;
    
    try {
        $sql = "INSERT INTO clients (name, email, phone, address, company, notes) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            $clientData['name'],
            $clientData['email'],
            $clientData['phone'],
            $clientData['address'],
            $clientData['company'],
            $clientData['notes']
        ]);
    } catch(PDOException $e) {
        return false;
    }
}

function getAllClients() {
    $pdo = getDBConnection();
    if (!$pdo) return [];
    
    try {
        $sql = "SELECT * FROM clients ORDER BY created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        return [];
    }
}

function getTotalClientOrders() {
    $pdo = getDBConnection();
    if (!$pdo) return 0;
    
    try {
        $result = $pdo->query("SELECT SUM(total_orders) as total FROM clients")->fetch();
        return $result['total'] ?? 0;
    } catch(PDOException $e) {
        return 0;
    }
}

function getTotalClientRevenue() {
    $pdo = getDBConnection();
    if (!$pdo) return 0;
    
    try {
        $result = $pdo->query("SELECT SUM(total_spent) as total FROM clients")->fetch();
        return $result['total'] ?? 0;
    } catch(PDOException $e) {
        return 0;
    }
}

// ==============================================
// وظائف مساعدة عامة
// ==============================================

function formatCurrency($amount) {
    return number_format($amount, 0, '.', ',') . ' ' . CURRENCY;
}

function getProductIcon($category) {
    $icons = [
        'هواتف ذكية' => 'mobile-alt',
        'أجهزة كمبيوتر' => 'laptop',
        'طابعات' => 'print',
        'ملحقات' => 'headphones',
        'شاشات' => 'tv',
        'كاميرات' => 'camera',
        'عام' => 'box'
    ];
    
    return $icons[$category] ?? 'box';
}

function getAvatarColor($name) {
    $colors = ['#4361ee', '#3a0ca3', '#7209b7', '#f72585', '#4cc9f0', '#4895ef', '#560bad'];
    $index = crc32($name) % count($colors);
    return $colors[$index];
}

function getInitials($name) {
    $words = explode(' ', $name);
    $initials = '';
    foreach ($words as $word) {
        $initials .= strtoupper(substr($word, 0, 1));
    }
    return substr($initials, 0, 2);
}

// ==============================================
// وظائف إدارة الموردين
// ==============================================

function setupSuppliersTable() {
    $pdo = getDBConnection();
    if (!$pdo) return false;
    
    try {
        $sql = "CREATE TABLE IF NOT EXISTS suppliers (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            contact_person VARCHAR(255),
            email VARCHAR(255),
            phone VARCHAR(20),
            address TEXT,
            city VARCHAR(100),
            website VARCHAR(255),
            notes TEXT,
            rating DECIMAL(2,1) DEFAULT 5.0,
            status ENUM('active', 'inactive') DEFAULT 'active',
            total_orders INT DEFAULT 0,
            total_spent DECIMAL(15,2) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $pdo->exec($sql);
        
        // إضافة بيانات تجريبية إذا كان الجدول فارغاً
        $count = $pdo->query("SELECT COUNT(*) FROM suppliers")->fetchColumn();
        if ($count == 0) {
            $sample_data = [
                ['شركة الإمدادات التقنية', 'محمد العلي', 'tech.supplies@dz.com', '0551-234-567', 'الجزائر العاصمة', 'الجزائر العاصمة', 'https://techsupplies.dz'],
                ['مؤسسة الجزائر للإلكترونيات', 'أحمد بوعزة', 'ahmed.bouazza@dz.com', '0552-345-678', 'وهران', 'وهران', 'https://algeria-electronics.dz'],
                ['شركة قسنطينة للكمبيوتر', 'فاطمة الزهراء', 'f.zohra@constantine.dz', '0553-456-789', 'قسنطينة', 'قسنطينة', 'https://constantine-pc.dz'],
                ['مورد هواتف عنابة', 'خالد معمري', 'k.maamri@annaba.dz', '0554-567-890', 'عنابة', 'عنابة', 'https://annaba-phones.dz']
            ];
            
            $stmt = $pdo->prepare("INSERT INTO suppliers (name, contact_person, email, phone, address, city, website) VALUES (?, ?, ?, ?, ?, ?, ?)");
            
            foreach ($sample_data as $data) {
                $stmt->execute($data);
            }
        }
        
        return true;
    } catch(PDOException $e) {
        return false;
    }
}

function addSupplier($supplierData) {
    $pdo = getDBConnection();
    if (!$pdo) return false;
    
    try {
        $sql = "INSERT INTO suppliers (name, contact_person, email, phone, address, city, website, notes) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            $supplierData['name'],
            $supplierData['contact_person'],
            $supplierData['email'],
            $supplierData['phone'],
            $supplierData['address'],
            $supplierData['city'],
            $supplierData['website'],
            $supplierData['notes']
        ]);
    } catch(PDOException $e) {
        return false;
    }
}

function getAllSuppliers() {
    $pdo = getDBConnection();
    if (!$pdo) return [];
    
    try {
        $sql = "SELECT * FROM suppliers ORDER BY created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        return [];
    }
}

function getTotalSupplierOrders() {
    $pdo = getDBConnection();
    if (!$pdo) return 0;
    
    try {
        $result = $pdo->query("SELECT SUM(total_orders) as total FROM suppliers")->fetch();
        return $result['total'] ?? 0;
    } catch(PDOException $e) {
        return 0;
    }
}

function getTotalSupplierSpent() {
    $pdo = getDBConnection();
    if (!$pdo) return 0;
    
    try {
        $result = $pdo->query("SELECT SUM(total_spent) as total FROM suppliers")->fetch();
        return $result['total'] ?? 0;
    } catch(PDOException $e) {
        return 0;
    }
}

// ==============================================
// الدوال الخاصة بالمبيعات
// ==============================================

function setupSalesTable() {
    $pdo = getDBConnection();
    if (!$pdo) return false;
    
    try {
        $sql = "CREATE TABLE IF NOT EXISTS sales (
            id INT PRIMARY KEY AUTO_INCREMENT,
            customer_name VARCHAR(255) NOT NULL,
            customer_phone VARCHAR(20),
            customer_email VARCHAR(255),
            product_id INT NOT NULL,
            product_name VARCHAR(255) NOT NULL,
            product_code VARCHAR(100) NOT NULL,
            quantity INT NOT NULL,
            unit_price DECIMAL(15,2) NOT NULL,
            total_amount DECIMAL(15,2) NOT NULL,
            payment_method ENUM('cash', 'card', 'transfer') DEFAULT 'cash',
            sale_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            status ENUM('completed', 'pending', 'cancelled') DEFAULT 'completed',
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        )";
        $pdo->exec($sql);
        
        return true;
    } catch(PDOException $e) {
        error_log("Error creating sales table: " . $e->getMessage());
        return false;
    }
}

function addSale($saleData) {
    $pdo = getDBConnection();
    if (!$pdo) return false;
    
    try {
        $pdo->beginTransaction();
        
        // الحصول على معلومات المنتج
        $product = getProductById($saleData['product_id']);
        if (!$product) {
            throw new Exception("المنتج غير موجود");
        }
        
        // التحقق من توفر الكمية في المخزن
        if ($product['current_stock'] < $saleData['quantity']) {
            throw new Exception("الكمية غير متوفرة في المخزن. المتاح: " . $product['current_stock']);
        }
        
        // تحديث المخزن
        $newStock = $product['current_stock'] - $saleData['quantity'];
        if (!updateProductStock($saleData['product_id'], $newStock)) {
            throw new Exception("فشل في تحديث المخزن");
        }
        
        // إضافة عملية البيع
        $sql = "INSERT INTO sales (customer_name, customer_phone, customer_email, product_id, product_name, product_code, 
                                  quantity, unit_price, total_amount, payment_method, sale_date, notes) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            $saleData['customer_name'],
            $saleData['customer_phone'],
            $saleData['customer_email'],
            $saleData['product_id'],
            $product['name'],
            $product['code'],
            $saleData['quantity'],
            $saleData['unit_price'],
            $saleData['total_amount'],
            $saleData['payment_method'],
            $saleData['sale_date'],
            $saleData['notes']
        ]);
        
        $pdo->commit();
        return $result;
        
    } catch(Exception $e) {
        $pdo->rollBack();
        error_log("Error in addSale: " . $e->getMessage());
        return false;
    }
}

function getAllSales() {
    $pdo = getDBConnection();
    if (!$pdo) return [];
    
    try {
        $sql = "SELECT s.*, p.name as product_name, p.code as product_code 
                FROM sales s 
                LEFT JOIN products p ON s.product_id = p.id 
                ORDER BY s.sale_date DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error in getAllSales: " . $e->getMessage());
        return [];
    }
}

function getTodaySales() {
    $pdo = getDBConnection();
    if (!$pdo) return ['count' => 0, 'revenue' => 0];
    
    try {
        $sql = "SELECT COUNT(*) as count, COALESCE(SUM(total_amount), 0) as revenue 
                FROM sales 
                WHERE DATE(sale_date) = CURDATE() AND status = 'completed'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: ['count' => 0, 'revenue' => 0];
    } catch(PDOException $e) {
        error_log("Error in getTodaySales: " . $e->getMessage());
        return ['count' => 0, 'revenue' => 0];
    }
}

function getMonthlyRevenue() {
    $pdo = getDBConnection();
    if (!$pdo) return 0;
    
    try {
        $sql = "SELECT COALESCE(SUM(total_amount), 0) as revenue 
                FROM sales 
                WHERE MONTH(sale_date) = MONTH(CURDATE()) 
                AND YEAR(sale_date) = YEAR(CURDATE()) 
                AND status = 'completed'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['revenue'] ?? 0;
    } catch(PDOException $e) {
        error_log("Error in getMonthlyRevenue: " . $e->getMessage());
        return 0;
    }
}

function getAvailableProducts() {
    $pdo = getDBConnection();
    if (!$pdo) return [];
    
    try {
        $sql = "SELECT id, name, code, selling_price, current_stock, category 
                FROM products 
                WHERE current_stock > 0 AND status = 'active' 
                ORDER BY name";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error in getAvailableProducts: " . $e->getMessage());
        return [];
    }
}

function getProductById($productId) {
    $pdo = getDBConnection();
    if (!$pdo) return null;
    
    try {
        $sql = "SELECT * FROM products WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$productId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error in getProductById: " . $e->getMessage());
        return null;
    }
}

function updateProductStock($productId, $newStock) {
    $pdo = getDBConnection();
    if (!$pdo) return false;
    
    try {
        $sql = "UPDATE products SET current_stock = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$newStock, $productId]);
    } catch(PDOException $e) {
        error_log("Error in updateProductStock: " . $e->getMessage());
        return false;
    }
}

function getStatusText($status) {
    $statuses = [
        'completed' => 'مكتمل',
        'pending' => 'قيد الانتظار',
        'cancelled' => 'ملغي'
    ];
    return $statuses[$status] ?? $status;
}

function getStatusIcon($status) {
    $icons = [
        'completed' => 'fa-check',
        'pending' => 'fa-clock',
        'cancelled' => 'fa-times'
    ];
    return $icons[$status] ?? 'fa-question';
}

function getPaymentMethodText($method) {
    $methods = [
        'cash' => 'نقدي',
        'card' => 'بطاقة',
        'transfer' => 'تحويل'
    ];
    return $methods[$method] ?? $method;
}

function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $string = [
        'y' => 'سنة',
        'm' => 'شهر',
        'd' => 'يوم',
        'h' => 'ساعة',
        'i' => 'دقيقة',
        's' => 'ثانية'
    ];
    
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v;
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? 'منذ ' . implode(', ', $string) : 'الآن';
}
// ==============================================
// وظائف إدارة المشتريات
// ==============================================

function setupPurchasesTable() {
    $pdo = getDBConnection();
    if (!$pdo) return false;
    
    try {
        $sql = "CREATE TABLE IF NOT EXISTS purchases (
            id INT PRIMARY KEY AUTO_INCREMENT,
            supplier_id INT NOT NULL,
            supplier_name VARCHAR(255) NOT NULL,
            product_id INT NOT NULL,
            product_name VARCHAR(255) NOT NULL,
            product_code VARCHAR(100) NOT NULL,
            quantity INT NOT NULL,
            unit_cost DECIMAL(15,2) NOT NULL,
            total_cost DECIMAL(15,2) NOT NULL,
            purchase_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            status ENUM('completed', 'pending', 'cancelled') DEFAULT 'completed',
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        )";
        $pdo->exec($sql);
        
        return true;
    } catch(PDOException $e) {
        error_log("Error creating purchases table: " . $e->getMessage());
        return false;
    }
}

function addPurchase($purchaseData) {
    $pdo = getDBConnection();
    if (!$pdo) return false;
    
    try {
        $pdo->beginTransaction();
        
        // الحصول على معلومات المنتج
        $product = getProductById($purchaseData['product_id']);
        if (!$product) {
            throw new Exception("المنتج غير موجود");
        }
        
        // الحصول على معلومات المورد
        $supplier = getSupplierById($purchaseData['supplier_id']);
        if (!$supplier) {
            throw new Exception("المورد غير موجود");
        }
        
        // تحديث المخزن
        $newStock = $product['current_stock'] + $purchaseData['quantity'];
        if (!updateProductStock($purchaseData['product_id'], $newStock)) {
            throw new Exception("فشل في تحديث المخزن");
        }
        
        // تحديث إحصائيات المورد
        updateSupplierStats($purchaseData['supplier_id'], $purchaseData['total_cost']);
        
        // إضافة عملية الشراء
        $sql = "INSERT INTO purchases (supplier_id, supplier_name, product_id, product_name, product_code, 
                                      quantity, unit_cost, total_cost, purchase_date, notes) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            $purchaseData['supplier_id'],
            $supplier['name'],
            $purchaseData['product_id'],
            $product['name'],
            $product['code'],
            $purchaseData['quantity'],
            $purchaseData['unit_cost'],
            $purchaseData['total_cost'],
            $purchaseData['purchase_date'],
            $purchaseData['notes']
        ]);
        
        $pdo->commit();
        return $result;
        
    } catch(Exception $e) {
        $pdo->rollBack();
        error_log("Error in addPurchase: " . $e->getMessage());
        return false;
    }
}

function getAllPurchases() {
    $pdo = getDBConnection();
    if (!$pdo) return [];
    
    try {
        $sql = "SELECT p.*, s.name as supplier_name, pr.name as product_name, pr.code as product_code 
                FROM purchases p 
                LEFT JOIN suppliers s ON p.supplier_id = s.id 
                LEFT JOIN products pr ON p.product_id = pr.id 
                ORDER BY p.purchase_date DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error in getAllPurchases: " . $e->getMessage());
        return [];
    }
}

function getTodayPurchases() {
    $pdo = getDBConnection();
    if (!$pdo) return ['count' => 0, 'cost' => 0];
    
    try {
        $sql = "SELECT COUNT(*) as count, COALESCE(SUM(total_cost), 0) as cost 
                FROM purchases 
                WHERE DATE(purchase_date) = CURDATE() AND status = 'completed'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: ['count' => 0, 'cost' => 0];
    } catch(PDOException $e) {
        error_log("Error in getTodayPurchases: " . $e->getMessage());
        return ['count' => 0, 'cost' => 0];
    }
}

function getMonthlyPurchases() {
    $pdo = getDBConnection();
    if (!$pdo) return 0;
    
    try {
        $sql = "SELECT COALESCE(SUM(total_cost), 0) as cost 
                FROM purchases 
                WHERE MONTH(purchase_date) = MONTH(CURDATE()) 
                AND YEAR(purchase_date) = YEAR(CURDATE()) 
                AND status = 'completed'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['cost'] ?? 0;
    } catch(PDOException $e) {
        error_log("Error in getMonthlyPurchases: " . $e->getMessage());
        return 0;
    }
}

function getSupplierById($supplierId) {
    $pdo = getDBConnection();
    if (!$pdo) return null;
    
    try {
        $sql = "SELECT * FROM suppliers WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$supplierId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error in getSupplierById: " . $e->getMessage());
        return null;
    }
}

function updateSupplierStats($supplierId, $amount) {
    $pdo = getDBConnection();
    if (!$pdo) return false;
    
    try {
        $sql = "UPDATE suppliers SET total_orders = total_orders + 1, total_spent = total_spent + ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$amount, $supplierId]);
    } catch(PDOException $e) {
        error_log("Error in updateSupplierStats: " . $e->getMessage());
        return false;
    }
}
// ==============================================
// وظائف إدارة التصنيفات
// ==============================================

function setupCategoriesTable() {
    $pdo = getDBConnection();
    if (!$pdo) return false;
    
    try {
        $sql = "CREATE TABLE IF NOT EXISTS categories (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL UNIQUE,
            description TEXT,
            color VARCHAR(7) DEFAULT '#007bff',
            icon VARCHAR(50) DEFAULT 'fas fa-folder',
            product_count INT DEFAULT 0,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $pdo->exec($sql);
        
        // إضافة تصنيفات افتراضية إذا كان الجدول فارغاً
        $count = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
        if ($count == 0) {
            $default_categories = [
                ['هواتف ذكية', 'الهواتف الذكية والأجهزة اللوحية', '#28a745', 'mobile-alt'],
                ['أجهزة كمبيوتر', 'أجهزة الكمبيوتر المحمولة والمكتبية', '#007bff', 'laptop'],
                ['طابعات', 'الطابعات وملحقاتها', '#dc3545', 'print'],
                ['شاشات', 'شاشات الكمبيور والتلفزيونات', '#ffc107', 'tv'],
                ['كاميرات', 'الكاميرات الرقمية وملحقاتها', '#6f42c1', 'camera'],
                ['ملحقات', 'ملحقات الأجهزة الإلكترونية', '#fd7e14', 'headphones'],
                ['أجهزة شبكات', 'أجهزة الراوتر والشبكات', '#20c997', 'network-wired'],
                ['برامج', 'البرامج والتطبيقات', '#6610f2', 'code']
            ];
            
            $stmt = $pdo->prepare("INSERT INTO categories (name, description, color, icon) VALUES (?, ?, ?, ?)");
            
            foreach ($default_categories as $category) {
                $stmt->execute($category);
            }
        }
        
        return true;
    } catch(PDOException $e) {
        error_log("Error creating categories table: " . $e->getMessage());
        return false;
    }
}

function addCategory($categoryData) {
    $pdo = getDBConnection();
    if (!$pdo) return false;
    
    try {
        $sql = "INSERT INTO categories (name, description, color, icon) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            $categoryData['name'],
            $categoryData['description'],
            $categoryData['color'],
            $categoryData['icon']
        ]);
    } catch(PDOException $e) {
        error_log("Error in addCategory: " . $e->getMessage());
        return false;
    }
}

function getAllCategories() {
    $pdo = getDBConnection();
    if (!$pdo) return [];
    
    try {
        $sql = "SELECT c.*, 
                (SELECT COUNT(*) FROM products WHERE category = c.name AND status = 'active') as product_count
                FROM categories c 
                WHERE c.status = 'active'
                ORDER BY c.created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error in getAllCategories: " . $e->getMessage());
        return [];
    }
}

function getCategoryById($categoryId) {
    $pdo = getDBConnection();
    if (!$pdo) return null;
    
    try {
        $sql = "SELECT c.*, 
                (SELECT COUNT(*) FROM products WHERE category = c.name AND status = 'active') as product_count
                FROM categories c 
                WHERE c.id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$categoryId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error in getCategoryById: " . $e->getMessage());
        return null;
    }
}

function updateCategory($categoryId, $categoryData) {
    $pdo = getDBConnection();
    if (!$pdo) return false;
    
    try {
        $sql = "UPDATE categories SET name = ?, description = ?, color = ?, icon = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            $categoryData['name'],
            $categoryData['description'],
            $categoryData['color'],
            $categoryData['icon'],
            $categoryId
        ]);
    } catch(PDOException $e) {
        error_log("Error in updateCategory: " . $e->getMessage());
        return false;
    }
}

function deleteCategory($categoryId) {
    $pdo = getDBConnection();
    if (!$pdo) return false;
    
    try {
        // التحقق من عدم وجود منتجات مرتبطة بالتصنيف
        $category = getCategoryById($categoryId);
        if ($category && $category['product_count'] > 0) {
            throw new Exception("لا يمكن حذف التصنيف لأنه يحتوي على منتجات");
        }
        
        $sql = "DELETE FROM categories WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$categoryId]);
    } catch(Exception $e) {
        error_log("Error in deleteCategory: " . $e->getMessage());
        return false;
    }
}

function getCategoryStats() {
    $pdo = getDBConnection();
    if (!$pdo) return ['total_categories' => 0, 'total_products' => 0];
    
    try {
        $sql = "SELECT 
                COUNT(*) as total_categories,
                SUM(product_count) as total_products
                FROM categories 
                WHERE status = 'active'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error in getCategoryStats: " . $e->getMessage());
        return ['total_categories' => 0, 'total_products' => 0];
    }
}

function getProductsByCategory($categoryName) {
    $pdo = getDBConnection();
    if (!$pdo) return [];
    
    try {
        $sql = "SELECT * FROM products WHERE category = ? AND status = 'active' ORDER BY created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$categoryName]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error in getProductsByCategory: " . $e->getMessage());
        return [];
    }
}
?>