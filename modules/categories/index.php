<?php
// modules/categories/index.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit;
}

require_once '../../config/database.php';

// تأكد من وجود جدول التصنيفات
setupCategoriesTable();

// معالجة إضافة تصنيف جديد
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_category'])) {
        $categoryData = [
            'name' => trim($_POST['name']),
            'description' => trim($_POST['description'] ?? ''),
            'color' => $_POST['color'] ?? '#007bff',
            'icon' => $_POST['icon'] ?? 'fas fa-folder',
            'parent_id' => $_POST['parent_id'] ? intval($_POST['parent_id']) : null,
            'sort_order' => intval($_POST['sort_order'] ?? 0)
        ];
        
        if (empty($categoryData['name'])) {
            $error_message = "❌ يرجى إدخال اسم التصنيف!";
        } else {
            if (addCategory($categoryData)) {
                $success_message = "✅ تم إضافة التصنيف بنجاح!";
            } else {
                $error_message = "❌ فشل في إضافة التصنيف!";
            }
        }
    }
}

// جلب بيانات التصنيفات مع الإحصائيات
$categories = getAllCategories(true);
$total_categories = count($categories);
$total_products = array_sum(array_column($categories, 'product_count'));
$total_stock = array_sum(array_column($categories, 'total_stock'));
$total_value = array_sum(array_column($categories, 'inventory_value'));
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة التصنيفات - نظام إدارة المخزون</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { 
            background: #f8f9fa; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .main-content {
            margin-right: 280px;
            padding: 20px;
            min-height: 100vh;
        }
        .category-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            margin-bottom: 20px;
            border-left: 5px solid;
        }
        .category-card:hover {
            transform: translateY(-5px);
        }
        .category-icon {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin: 0 auto 15px;
            color: white;
        }
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border: none;
            transition: transform 0.3s;
            height: 100%;
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
        .stats-icon {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 1.8rem;
        }
        .stats-number {
            font-size: 2.2rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .stats-label {
            color: #6c757d;
            font-size: 1rem;
        }
        .product-badge {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
        }
        .stock-badge {
            background: linear-gradient(45deg, #007bff, #0056b3);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
        }
        .value-badge {
            background: linear-gradient(45deg, #ffc107, #fd7e14);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
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
                        <h2 class="mb-1">إدارة التصنيفات</h2>
                        <p class="text-muted mb-0">تنظيم وتصنيف المنتجات في النظام</p>
                    </div>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                        <i class="fas fa-plus me-2"></i>إضافة تصنيف جديد
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
                            <div class="stats-icon" style="background: rgba(0, 123, 255, 0.1); color: #007bff;">
                                <i class="fas fa-folder"></i>
                            </div>
                            <div class="stats-number"><?php echo $total_categories; ?></div>
                            <div class="stats-label">إجمالي التصنيفات</div>
                        </div>
                    </div>
                    
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
                                <i class="fas fa-layer-group"></i>
                            </div>
                            <div class="stats-number"><?php echo $total_stock; ?></div>
                            <div class="stats-label">إجمالي المخزون</div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stats-card">
                            <div class="stats-icon" style="background: rgba(108, 117, 125, 0.1); color: #6c757d;">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <div class="stats-number"><?php echo formatCurrency($total_value); ?></div>
                            <div class="stats-label">قيمة المخزون</div>
                        </div>
                    </div>
                </div>

                <!-- شبكة التصنيفات -->
                <div class="row">
                    <?php if (empty($categories)): ?>
                        <div class="col-12">
                            <div class="alert alert-info text-center">
                                <i class="fas fa-info-circle me-2"></i>
                                لا توجد تصنيفات في النظام
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($categories as $category): ?>
                            <div class="col-xl-4 col-md-6">
                                <div class="card category-card" style="border-left-color: <?php echo $category['color']; ?>">
                                    <div class="card-body text-center">
                                        <!-- أيقونة التصنيف -->
                                        <div class="category-icon" style="background: <?php echo $category['color']; ?>">
                                            <i class="<?php echo $category['icon']; ?>"></i>
                                        </div>
                                        
                                        <!-- معلومات التصنيف -->
                                        <h5 class="card-title"><?php echo htmlspecialchars($category['name']); ?></h5>
                                        
                                        <?php if (!empty($category['description'])): ?>
                                            <p class="text-muted mb-3"><?php echo htmlspecialchars($category['description']); ?></p>
                                        <?php endif; ?>
                                        
                                        <!-- إحصائيات التصنيف -->
                                        <div class="row mb-3">
                                            <div class="col-4">
                                                <div class="product-badge">
                                                    <i class="fas fa-box me-1"></i>
                                                    <?php echo $category['product_count']; ?>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="stock-badge">
                                                    <i class="fas fa-layer-group me-1"></i>
                                                    <?php echo $category['total_stock']; ?>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="value-badge">
                                                    <i class="fas fa-dollar-sign me-1"></i>
                                                    <?php echo formatCurrency($category['inventory_value']); ?>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- أزرار التحكم -->
                                        <div class="btn-group w-100">
                                            <button class="btn btn-outline-primary btn-sm" onclick="viewCategoryProducts('<?php echo urlencode($category['name']); ?>')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-outline-success btn-sm" onclick="viewCategoryStats(<?php echo $category['id']; ?>)">
                                                <i class="fas fa-chart-bar"></i>
                                            </button>
                                            <button class="btn btn-outline-info btn-sm" onclick="editCategory(<?php echo $category['id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-outline-danger btn-sm" onclick="deleteCategory(<?php echo $category['id']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- مودال إضافة تصنيف جديد -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCategoryModalLabel">إضافة تصنيف جديد</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">اسم التصنيف <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" placeholder="أدخل اسم التصنيف" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">الوصف</label>
                            <textarea class="form-control" name="description" rows="3" placeholder="وصف التصنيف (اختياري)"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">اللون</label>
                                <input type="color" class="form-control form-control-color" name="color" value="#007bff" title="اختر لون التصنيف">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">الأيقونة</label>
                                <select class="form-select" name="icon">
                                    <option value="fas fa-folder">📁 مجلد</option>
                                    <option value="fas fa-mobile-alt">📱 هاتف</option>
                                    <option value="fas fa-laptop">💻 لابتوب</option>
                                    <option value="fas fa-print">🖨️ طابعة</option>
                                    <option value="fas fa-tv">📺 شاشة</option>
                                    <option value="fas fa-camera">📷 كاميرا</option>
                                    <option value="fas fa-headphones">🎧 سماعات</option>
                                    <option value="fas fa-wifi">📶 شبكة</option>
                                    <option value="fas fa-gamepad">🎮 ألعاب</option>
                                    <option value="fas fa-utensils">🍴 مطبخ</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">التصنيف الرئيسي</label>
                                <select class="form-select" name="parent_id">
                                    <option value="">لا يوجد (تصنيف رئيسي)</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">ترتيب العرض</label>
                                <input type="number" class="form-control" name="sort_order" value="0" min="0">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" name="add_category" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>إضافة التصنيف
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    // وظائف الأزرار
    function viewCategoryProducts(categoryName) {
        window.open('category_products.php?category=' + categoryName, '_blank');
    }

    function viewCategoryStats(categoryId) {
        window.open('category_stats.php?id=' + categoryId, '_blank');
    }

    function editCategory(categoryId) {
        alert('تعديل التصنيف رقم: ' + categoryId);
        // يمكن فتح مودال التعديل هنا
    }

    function deleteCategory(categoryId) {
        if (confirm('هل تريد حذف هذا التصنيف؟ لا يمكن حذف التصنيف إذا كان يحتوي على منتجات.')) {
            // إرسال طلب الحذف
            fetch('delete_category.php?id=' + categoryId, {
                method: 'DELETE'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('تم حذف التصنيف بنجاح');
                    location.reload();
                } else {
                    alert('خطأ: ' + data.message);
                }
            })
            .catch(error => {
                alert('حدث خطأ أثناء الحذف');
            });
        }
    }
    </script>
</body>
</html>