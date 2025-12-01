<?php
// modules/products/index.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit;
}

require_once '../../config/database.php';

// معالجة إضافة منتج جديد
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $productData = [
        'name' => $_POST['name'],
        'description' => $_POST['description'] ?? '',
        'category' => $_POST['category'],
        'price' => $_POST['price'],
        'cost_price' => $_POST['cost_price'] ?? 0,
        'stock_quantity' => $_POST['stock_quantity'] ?? 0,
        'min_stock' => $_POST['min_stock'] ?? 5,
        'barcode' => $_POST['barcode'] ?: 'PROD-' . date('YmdHis') . rand(100, 999)
    ];
    
    if (addProduct($productData)) {
        $success_message = "✅ تم إضافة المنتج بنجاح!";
    } else {
        $error_message = "❌ فشل في إضافة المنتج!";
    }
}

// جلب البيانات للعرض
$category_filter = $_GET['category'] ?? '';
$search_term = $_GET['search'] ?? '';
$products = getAllProducts($category_filter, $search_term);

// إحصائيات حقيقية
$total_products = count($products);
$low_stock = 0;
$out_of_stock = 0;
$total_value = 0;

foreach ($products as $product) {
    if ($product['stock_quantity'] == 0) {
        $out_of_stock++;
    } elseif ($product['stock_quantity'] <= $product['min_stock']) {
        $low_stock++;
    }
    $total_value += $product['price'] * $product['stock_quantity'];
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المنتجات - نظام إدارة المخزون</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { 
            background: #f8f9fa; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
        }
        .main-content {
            margin-right: 280px;
            padding: 20px;
            min-height: 100vh;
        }
        .product-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .product-card:hover {
            transform: translateY(-5px);
        }
        .low-stock {
            border-left: 4px solid #ffc107;
        }
        .out-of-stock {
            border-left: 4px solid #dc3545;
        }
        .in-stock {
            border-left: 4px solid #28a745;
        }
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border: none;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include '../../includes/sidebar.php'; ?>
            
            <div class="main-content">
                <!-- رأس الصفحة -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="mb-1">إدارة المنتجات</h2>
                        <p class="text-muted mb-0">إدارة منتجات المتجر - العملة: دينار جزائري (د.ج)</p>
                    </div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                        <i class="fas fa-plus me-2"></i>إضافة منتج جديد
                    </button>
                </div>

                <!-- رسائل التنبيه -->
                <?php if ($success_message): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $success_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($error_message): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $error_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- إحصائيات سريعة -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stats-card">
                            <div class="stats-icon" style="background: rgba(40, 167, 69, 0.1); color: #28a745;">
                                <i class="fas fa-boxes"></i>
                            </div>
                            <div class="stats-number"><?php echo $total_products; ?></div>
                            <div class="stats-label">إجمالي المنتجات</div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stats-card">
                            <div class="stats-icon" style="background: rgba(255, 193, 7, 0.1); color: #ffc107;">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="stats-number"><?php echo $low_stock; ?></div>
                            <div class="stats-label">منخفضة المخزون</div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stats-card">
                            <div class="stats-icon" style="background: rgba(220, 53, 69, 0.1); color: #dc3545;">
                                <i class="fas fa-times-circle"></i>
                            </div>
                            <div class="stats-number"><?php echo $out_of_stock; ?></div>
                            <div class="stats-label">منتهية المخزون</div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stats-card">
                            <div class="stats-icon" style="background: rgba(0, 123, 255, 0.1); color: #007bff;">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <div class="stats-number">
                                <span style="font-family: Arial; font-weight: bold;"><?php echo formatCurrency($total_value); ?></span>
                            </div>
                            <div class="stats-label">قيمة المخزون</div>
                        </div>
                    </div>
                </div>

                <!-- شريط البحث والتصفية -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" action="">
                            <div class="row">
                                <div class="col-md-4 mb-2">
                                    <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search_term); ?>" placeholder="🔍 ابحث عن منتج...">
                                </div>
                                <div class="col-md-3 mb-2">
                                    <select class="form-control" name="category">
                                        <option value="">جميع التصنيفات</option>
                                        <option value="هواتف ذكية" <?php echo $category_filter == 'هواتف ذكية' ? 'selected' : ''; ?>>هواتف ذكية</option>
                                        <option value="أجهزة كمبيوتر" <?php echo $category_filter == 'أجهزة كمبيوتر' ? 'selected' : ''; ?>>أجهزة كمبيوتر</option>
                                        <option value="طابعات" <?php echo $category_filter == 'طابعات' ? 'selected' : ''; ?>>طابعات</option>
                                        <option value="ملحقات" <?php echo $category_filter == 'ملحقات' ? 'selected' : ''; ?>>ملحقات</option>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-filter me-2"></i>تصفية
                                    </button>
                                </div>
                                <div class="col-md-2 mb-2">
                                    <a href="index.php" class="btn btn-outline-secondary w-100">
                                        <i class="fas fa-redo me-2"></i>إعادة تعيين
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- جدول المنتجات -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-list me-2"></i>قائمة المنتجات
                            <small class="text-muted">(<?php echo $total_products; ?> منتج)</small>
                        </h5>
                        <div>
                            <button class="btn btn-outline-success btn-sm me-2">
                                <i class="fas fa-file-export me-2"></i>تصدير
                            </button>
                            <button class="btn btn-outline-info btn-sm">
                                <i class="fas fa-print me-2"></i>طباعة
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>الصورة</th>
                                        <th>اسم المنتج</th>
                                        <th>التصنيف</th>
                                        <th>سعر البيع</th>
                                        <th>سعر الشراء</th>
                                        <th>المخزون</th>
                                        <th>الحالة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($products)): ?>
                                        <tr>
                                            <td colspan="9" class="text-center text-muted py-4">
                                                <i class="fas fa-box-open fa-2x mb-2"></i><br>
                                                لا توجد منتجات
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($products as $index => $product): ?>
                                            <?php
                                            // تحديد حالة المخزون
                                            if ($product['stock_quantity'] == 0) {
                                                $status_class = 'out-of-stock';
                                                $status_badge = 'bg-danger';
                                                $status_text = 'منتهي';
                                                $status_icon = 'fas fa-times';
                                            } elseif ($product['stock_quantity'] <= $product['min_stock']) {
                                                $status_class = 'low-stock';
                                                $status_badge = 'bg-warning';
                                                $status_text = 'منخفض';
                                                $status_icon = 'fas fa-exclamation';
                                            } else {
                                                $status_class = 'in-stock';
                                                $status_badge = 'bg-success';
                                                $status_text = 'متوفر';
                                                $status_icon = 'fas fa-check';
                                            }
                                            
                                            // حساب نسبة المخزون
                                            $max_stock = max($product['stock_quantity'] * 2, 100);
                                            $stock_percentage = ($product['stock_quantity'] / $max_stock) * 100;
                                            ?>
                                            <tr class="<?php echo $status_class; ?>">
                                                <td><?php echo $index + 1; ?></td>
                                                <td>
                                                    <div style="width: 40px; height: 40px; background: #e9ecef; border-radius: 5px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="fas fa-<?php echo getProductIcon($product['category']); ?> text-primary"></i>
                                                    </div>
                                                </td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                                    <br><small class="text-muted"><?php echo $product['barcode']; ?></small>
                                                    <?php if (!empty($product['description'])): ?>
                                                        <br><small class="text-muted"><?php echo htmlspecialchars($product['description']); ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($product['category']); ?></td>
                                                <td>
                                                    <span class="fw-bold text-success"><?php echo formatCurrency($product['price']); ?></span>
                                                </td>
                                                <td>
                                                    <span class="fw-bold text-info"><?php echo formatCurrency($product['cost_price']); ?></span>
                                                </td>
                                                <td>
                                                    <div><?php echo $product['stock_quantity']; ?> قطعة</div>
                                                    <div class="progress" style="height: 5px;">
                                                        <div class="progress-bar <?php echo str_replace('bg-', 'bg-', $status_badge); ?>" 
                                                             style="width: <?php echo $stock_percentage; ?>%"></div>
                                                    </div>
                                                    <small class="text-muted">الحد الأدنى: <?php echo $product['min_stock']; ?></small>
                                                </td>
                                                <td>
                                                    <span class="badge <?php echo $status_badge; ?>">
                                                        <i class="<?php echo $status_icon; ?> me-1"></i><?php echo $status_text; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <button class="btn btn-outline-primary" onclick="editProduct(<?php echo $product['id']; ?>)">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-outline-info" onclick="viewProduct(<?php echo $product['id']; ?>)">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <button class="btn btn-outline-warning" onclick="manageStock(<?php echo $product['id']; ?>)">
                                                            <i class="fas fa-box"></i>
                                                        </button>
                                                        <button class="btn btn-outline-danger" onclick="deleteProduct(<?php echo $product['id']; ?>)">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- مودال إضافة منتج جديد -->
    <div class="modal fade" id="addProductModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">إضافة منتج جديد</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">اسم المنتج <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" placeholder="أدخل اسم المنتج" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">التصنيف <span class="text-danger">*</span></label>
                                <select class="form-control" name="category" required>
                                    <option value="">اختر التصنيف</option>
                                    <option value="هواتف ذكية">هواتف ذكية</option>
                                    <option value="أجهزة كمبيوتر">أجهزة كمبيوتر</option>
                                    <option value="طابعات">طابعات</option>
                                    <option value="ملحقات">ملحقات</option>
                                    <option value="شاشات">شاشات</option>
                                    <option value="كاميرات">كاميرات</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">سعر البيع (د.ج) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="price" placeholder="سعر البيع" required step="0.01">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">سعر الشراء (د.ج)</label>
                                <input type="number" class="form-control" name="cost_price" placeholder="سعر الشراء" step="0.01">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">الكمية في المخزون</label>
                                <input type="number" class="form-control" name="stock_quantity" placeholder="الكمية المتاحة" value="0">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">الحد الأدنى للمخزون</label>
                                <input type="number" class="form-control" name="min_stock" placeholder="الحد الأدنى للتنبيه" value="5">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">وصف المنتج</label>
                            <textarea class="form-control" name="description" rows="3" placeholder="وصف مختصر للمنتج"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">الباركود (اختياري)</label>
                            <input type="text" class="form-control" name="barcode" placeholder="سيتم إنشاء باركود تلقائياً">
                            <small class="text-muted">اتركه فارغاً لإنشاء باركود تلقائي</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" name="add_product" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>حفظ المنتج
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    // وظائف الأزرار
    function editProduct(id) {
        alert('تعديل المنتج رقم: ' + id);
    }

    function viewProduct(id) {
        alert('عرض المنتج رقم: ' + id);
    }

    function manageStock(id) {
        alert('إدارة مخزون المنتج رقم: ' + id);
    }

    function deleteProduct(id) {
        if (confirm('هل تريد حذف هذا المنتج؟')) {
            window.location.href = 'delete_product.php?id=' + id;
        }
    }
    </script>
    
</body>
</html>