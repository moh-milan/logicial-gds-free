-- بيانات تجريبية لنظام إدارة المخزون

-- إدراج مستخدمين
INSERT INTO `users` (`username`, `password`, `full_name`, `email`, `phone`, `role`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'مدير النظام', 'admin@system.com', '0551234567', 'admin'),
('manager', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'مدير المبيعات', 'manager@system.com', '0557654321', 'manager'),
('user1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'موظف مبيعات', 'user1@system.com', '0551112233', 'user');

-- إدراج عملاء
INSERT INTO `clients` (`name`, `email`, `phone`, `company`, `address`, `created_by`) VALUES
('أحمد محمد', 'ahmed@company.com', '0501234567', 'شركة التقنية المتطورة', 'الرياض - حي العليا', 1),
('فاطمة عبدالله', 'fatima@business.com', '0557654321', 'مؤسسة النجاح التجارية', 'جدة - حي الصفا', 1),
('خالد السعيد', 'khaled@enterprise.com', '0541122334', 'شركة الإبداع للتقنية', 'الدمام - حي الثقبة', 2),
('نورة الرشيد', 'nora@corporation.com', '0534455667', 'مجموعة الرشيد الدولية', 'الرياض - حي النخيل', 1);

-- إدراج موردين
INSERT INTO `suppliers` (`name`, `contact_person`, `email`, `phone`, `address`) VALUES
('شركة الإمدادات التقنية', 'محمد العلي', 'tech@supplies.com', '0112345678', 'الرياض - الصناعية الأولى'),
('مؤسسة المواد الأولية', 'سعيد الحربي', 'raw@materials.com', '0123456789', 'جدة - الصناعية القديمة'),
('شركة الأدوات المكتبية', 'عبدالله القحطاني', 'office@tools.com', '0134567890', 'الدمام - الصناعية الجديدة');

-- إدراج تصنيفات
INSERT INTO `categories` (`name`, `description`) VALUES
('أجهزة كمبيوتر', 'أجهزة الكمبيوتر المحمولة والمكتبية'),
('هواتف ذكية', 'الهواتف الذكية والأجهزة اللوحية'),
('طابعات', 'الطابعات وملحقاتها'),
('شبكات', 'معدات الشبكات والاتصالات'),
('برمجيات', 'البرامج والتطبيقات');

-- إدراج منتجات
INSERT INTO `products` (`name`, `description`, `price`, `quantity`, `sku`, `category`) VALUES
('لابتوب ديل XPS 13', 'لابتوب ديل XPS 13 بشاشة 13 بوصة، معالج i7، ذاكرة 16GB', 4500.00, 15, 'DL-XPS13-001', 'أجهزة كمبيوتر'),
('ماك بوك برو M2', 'لابتوب أبل ماك بوك برو بشاشة 14 بوصة، معالج M2، ذاكرة 16GB', 8500.00, 8, 'AP-MBP14-001', 'أجهزة كمبيوتر'),
('آيفون 15 برو', 'هاتف آيفون 15 برو، شاشة 6.1 بوصة، تخزين 256GB', 5200.00, 25, 'AP-IP15P-001', 'هواتف ذكية'),
('سامسونج جالاكسي S24', 'هاتف سامسونج جالاكسي S24، شاشة 6.2 بوصة، تخزين 256GB', 3800.00, 30, 'SM-GS24-001', 'هواتف ذكية'),
('طابعة ليزر HP', 'طابعة ليزر HP لاسلكية، طباعة ملونة، سكانر', 1200.00, 12, 'HP-LZR-001', 'طابعات'),
('راوتر سيسكو', 'راوتر سيسكو للأعمال، دعم Wi-Fi 6، 8 منافذ LAN', 1800.00, 10, 'CS-RTR-001', 'شبكات'),
('مايكروسوفت أوفيس', 'حزمة مايكروسوفت أوفيس 2023، ترخيص دائم', 650.00, 50, 'MS-OFC-001', 'برمجيات');

-- إدراج مبيعات
INSERT INTO `sales` (`client_id`, `product_id`, `quantity`, `total_price`, `sale_date`, `status`, `created_by`) VALUES
(1, 1, 2, 9000.00, '2024-01-15', 'completed', 2),
(2, 3, 1, 5200.00, '2024-01-16', 'completed', 2),
(3, 5, 3, 3600.00, '2024-01-17', 'completed', 3),
(1, 7, 5, 3250.00, '2024-01-18', 'pending', 2),
(4, 2, 1, 8500.00, '2024-01-19', 'completed', 2);

-- إدراج مشتريات
INSERT INTO `purchases` (`supplier_id`, `product_id`, `quantity`, `unit_price`, `total_price`, `purchase_date`, `status`, `created_by`) VALUES
(1, 1, 10, 3800.00, 38000.00, '2024-01-10', 'received', 1),
(1, 3, 20, 4500.00, 90000.00, '2024-01-11', 'received', 1),
(2, 5, 15, 900.00, 13500.00, '2024-01-12', 'received', 1),
(3, 7, 50, 500.00, 25000.00, '2024-01-13', 'pending', 1);-- هيكل قاعدة البيانات الكامل
SET FOREIGN_KEY_CHECKS=0;

-- جدول المستخدمين
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('admin','manager','user') DEFAULT 'user',
  `status` enum('active','inactive') DEFAULT 'active',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- جدول التصنيفات
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- جدول المنتجات
CREATE TABLE IF NOT EXISTS `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `description` text,
  `price` decimal(10,2) DEFAULT '0.00',
  `quantity` int(11) DEFAULT '0',
  `sku` varchar(100) UNIQUE,
  `category_id` int(11) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- جدول العملاء
CREATE TABLE IF NOT EXISTS `clients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `company` varchar(200) DEFAULT NULL,
  `address` text,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- جدول الموردين
CREATE TABLE IF NOT EXISTS `suppliers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- جدول المبيعات
CREATE TABLE IF NOT EXISTS `sales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT '1',
  `total_price` decimal(10,2) DEFAULT '0.00',
  `sale_date` date DEFAULT NULL,
  `status` enum('pending','completed','cancelled') DEFAULT 'pending',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- جدول المشتريات
CREATE TABLE IF NOT EXISTS `purchases` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplier_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT '1',
  `unit_price` decimal(10,2) DEFAULT '0.00',
  `total_price` decimal(10,2) DEFAULT '0.00',
  `purchase_date` date DEFAULT NULL,
  `status` enum('pending','received','cancelled') DEFAULT 'pending',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`supplier_id`) REFERENCES `suppliers`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS=1;

-- إدراج بيانات أولية
INSERT INTO `users` (`username`, `password`, `full_name`, `email`, `role`) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'مدير النظام', 'admin@system.com', 'admin');

INSERT INTO `categories` (`name`, `description`) VALUES 
('أجهزة كمبيوتر', 'أجهزة الكمبيوتر المحمولة والمكتبية'),
('هواتف ذكية', 'الهواتف الذكية والأجهزة اللوحية'),
('طابعات', 'الطابعات وملحقاتها');