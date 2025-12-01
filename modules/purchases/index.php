<?php
// modules/purchases/index.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit;
}

require_once '../../config/database.php';

// تأكد من وجود جدول المشتريات
setupPurchasesTable();

// معالجة إضافة عملية شراء جديدة
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_purchase'])) {
    $purchaseData = [
        'supplier_id' => intval($_POST['supplier_id']),
        'product_id' => intval($_POST['product_id']),
        'quantity' => intval($_POST['quantity']),
        'unit_cost' => floatval($_POST['unit_cost']),
        'total_cost' => floatval($_POST['quantity']) * floatval($_POST['unit_cost']),
        'purchase_date' => $_POST['purchase_date'] ?? date('Y-m-d H:i:s'),
        'notes' => trim($_POST['notes'] ?? '')
    ];
    
    if (addPurchase($purchaseData)) {
        $success_message = "✅ تمت عملية الشراء بنجاح!";
    } else {
        $error_message = "❌ فشل في إتمام عملية الشراء!";
    }
}

// جلب بيانات المشتريات
$purchases = getAllPurchases();
$total_purchases = count($purchases);
$today_purchases = getTodayPurchases();
$monthly_cost = getMonthlyPurchases();

// جلب الموردين والمنتجات المتاحة
$suppliers = getAllSuppliers();
$products = getAllProducts();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المشتريات - نظام إدارة المخزون</title>
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
        .purchase-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .purchase-card:hover {
            transform: translateY(-5px);
        }
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border: none;
        }
        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 1.5rem;
        }
        .supplier-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: white;
            font-size: 1.2rem;
        }
        .badge-completed { background: #28a745; }
        .badge-pending { background: #ffc107; }
        .badge-cancelled { background: #dc3545; }
        
        .btn-primary {
            background: linear-gradient(45deg, #007bff, #0056b3);
            border: none;
            padding: 10px 20px;
            font-weight: 600;
        }
        .btn-primary:hover {
            background: linear-gradient(45deg, #0056b3, #004085);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
        }
        
        .form-select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
            background-position: left 0.75rem center;
            background-repeat: no-repeat;
            background-size: 16px 12px;
            padding-left: 2.5rem;
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
                        <h2 class="mb-1">إدارة المشتريات</h2>
                        <p class="text-muted mb-0">إدارة عمليات الشراء والتوريد - العملة: دينار جزائري (د.ج)</p>
                    </div>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPurchaseModal">
                        <i class="fas fa-shopping-cart me-2"></i>عملية شراء جديدة
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
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div class="stats-number"><?php echo $total_purchases; ?></div>
                            <div class="stats-label">إجمالي المشتريات</div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stats-card">
                            <div class="stats-icon" style="background: rgba(40, 167, 69, 0.1); color: #28a745;">
                                <i class="fas fa-calendar-day"></i>
                            </div>
                            <div class="stats-number"><?php echo $today_purchases['count']; ?></div>
                            <div class="stats-label">مشتريات اليوم</div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stats-card">
                            <div class="stats-icon" style="background: rgba(255, 193, 7, 0.1); color: #ffc107;">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <div class="stats-number">
                                <span style="font-family: Arial; font-weight: bold;"><?php echo formatCurrency($today_purchases['cost']); ?></span>
                            </div>
                            <div class="stats-label">تكلفة اليوم</div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stats-card">
                            <div class="stats-icon" style="background: rgba(108, 117, 125, 0.1); color: #6c757d;">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="stats-number">
                                <span style="font-family: Arial; font-weight: bold;"><?php echo formatCurrency($monthly_cost); ?></span>
                            </div>
                            <div class="stats-label">تكلفة الشهر</div>
                        </div>
                    </div>
                </div>

                <!-- تحذير إذا لم توجد موردين أو منتجات -->
                <?php if (empty($suppliers) || empty($products)): ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>تنبيه!</strong> 
                        <?php if (empty($suppliers)): ?>
                            لا توجد موردين. يرجى إضافة موردين أولاً من قسم إدارة الموردين.
                            <a href="../suppliers/index.php" class="alert-link">الذهاب إلى إدارة الموردين</a>
                        <?php elseif (empty($products)): ?>
                            لا توجد منتجات. يرجى إضافة منتجات أولاً من قسم إدارة المنتجات.
                            <a href="../products/index.php" class="alert-link">الذهاب إلى إدارة المنتجات</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- شريط البحث والتصفية -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <input type="text" class="form-control" id="searchInput" placeholder="🔍 ابحث عن مورد أو منتج...">
                            </div>
                            <div class="col-md-2 mb-2">
                                <select class="form-control" id="statusFilter">
                                    <option value="">جميع الحالات</option>
                                    <option value="completed">مكتمل</option>
                                    <option value="pending">قيد الانتظار</option>
                                    <option value="cancelled">ملغي</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-2">
                                <input type="date" class="form-control" id="dateFilter">
                            </div>
                            <div class="col-md-2 mb-2">
                                <button class="btn btn-outline-primary w-100" id="filterBtn">
                                    <i class="fas fa-filter me-2"></i>تصفية
                                </button>
                            </div>
                            <div class="col-md-2 mb-2">
                                <button class="btn btn-outline-success w-100" id="resetBtn">
                                    <i class="fas fa-redo me-2"></i>إعادة تعيين
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- جدول المشتريات -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-list me-2"></i>سجل المشتريات
                            <small class="text-muted">(<?php echo $total_purchases; ?> عملية شراء)</small>
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
                                        <th>المورد</th>
                                        <th>المنتج</th>
                                        <th>الكمية</th>
                                        <th>سعر التكلفة</th>
                                        <th>التكلفة الإجمالية</th>
                                        <th>التاريخ</th>
                                        <th>الحالة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($purchases)): ?>
                                        <tr>
                                            <td colspan="9" class="text-center text-muted py-4">
                                                <i class="fas fa-shopping-cart fa-2x mb-2"></i><br>
                                                لا توجد عمليات شراء
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($purchases as $index => $purchase): ?>
                                            <?php
                                            $avatar_bg = getAvatarColor($purchase['supplier_name']);
                                            $status_badge = 'badge-' . $purchase['status'];
                                            $status_text = getStatusText($purchase['status']);
                                            $status_icon = getStatusIcon($purchase['status']);
                                            ?>
                                            <tr>
                                                <td><?php echo $index + 1; ?></td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="supplier-avatar me-3" style="background: <?php echo $avatar_bg; ?>">
                                                            <?php echo getInitials($purchase['supplier_name']); ?>
                                                        </div>
                                                        <div>
                                                            <strong><?php echo htmlspecialchars($purchase['supplier_name']); ?></strong>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($purchase['product_name']); ?></strong>
                                                    <br><small class="text-muted">كود: <?php echo htmlspecialchars($purchase['product_code']); ?></small>
                                                </td>
                                                <td>
                                                    <span class="fw-bold text-primary"><?php echo $purchase['quantity']; ?></span>
                                                    <br><small class="text-muted">وحدة</small>
                                                </td>
                                                <td>
                                                    <span class="fw-bold"><?php echo formatCurrency($purchase['unit_cost']); ?></span>
                                                    <br><small class="text-muted">د.ج</small>
                                                </td>
                                                <td>
                                                    <span class="fw-bold text-success"><?php echo formatCurrency($purchase['total_cost']); ?></span>
                                                    <br><small class="text-muted">د.ج</small>
                                                </td>
                                                <td>
                                                    <?php echo date('Y-m-d H:i', strtotime($purchase['purchase_date'])); ?>
                                                    <br><small class="text-muted"><?php echo time_elapsed_string($purchase['purchase_date']); ?></small>
                                                </td>
                                                <td>
                                                    <span class="badge <?php echo $status_badge; ?>">
                                                        <i class="fas <?php echo $status_icon; ?> me-1"></i><?php echo $status_text; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <button class="btn btn-outline-primary" onclick="editPurchase(<?php echo $purchase['id']; ?>)">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-outline-info" onclick="viewPurchase(<?php echo $purchase['id']; ?>)">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <button class="btn btn-outline-warning" onclick="printPurchase(<?php echo $purchase['id']; ?>)">
                                                            <i class="fas fa-receipt"></i>
                                                        </button>
                                                        <button class="btn btn-outline-danger" onclick="cancelPurchase(<?php echo $purchase['id']; ?>)">
                                                            <i class="fas fa-times"></i>
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

    <!-- مودال إضافة عملية شراء جديدة -->
    <div class="modal fade" id="addPurchaseModal" tabindex="-1" aria-labelledby="addPurchaseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addPurchaseModalLabel">عملية شراء جديدة</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">المورد <span class="text-danger">*</span></label>
                                <select class="form-select" name="supplier_id" id="supplierSelect" required>
                                    <option value="">اختر المورد...</option>
                                    <?php if (!empty($suppliers)): ?>
                                        <?php foreach ($suppliers as $supplier): ?>
                                            <option value="<?php echo $supplier['id']; ?>">
                                                <?php echo htmlspecialchars($supplier['name']); ?>
                                                <?php if (!empty($supplier['contact_person'])): ?>
                                                    - <?php echo htmlspecialchars($supplier['contact_person']); ?>
                                                <?php endif; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="" disabled>--- لا توجد موردين ---</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">المنتج <span class="text-danger">*</span></label>
                                <select class="form-select" name="product_id" id="productSelect" required onchange="updateProductInfo()">
                                    <option value="">اختر المنتج...</option>
                                    <?php if (!empty($products)): ?>
                                        <?php foreach ($products as $product): ?>
                                            <option value="<?php echo $product['id']; ?>" 
                                                    data-price="<?php echo $product['cost_price']; ?>"
                                                    data-stock="<?php echo $product['stock_quantity']; ?>"
                                                    data-code="<?php echo htmlspecialchars($product['barcode']); ?>">
                                                <?php echo htmlspecialchars($product['name']); ?> - 
                                                <?php echo htmlspecialchars($product['barcode']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="" disabled>--- لا توجد منتجات ---</option>
                                    <?php endif; ?>
                                </select>
                                <small class="form-text text-muted" id="stockInfo">المخزن الحالي: 0</small>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">الكمية <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="quantity" id="quantityInput" min="1" value="1" required onchange="calculateTotal()">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">سعر التكلفة <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control" name="unit_cost" id="unitCostInput" required onchange="calculateTotal()">
                                <small class="form-text text-muted">سعر الشراء: <span id="originalCost">0</span> د.ج</small>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">التكلفة الإجمالية</label>
                                <input type="text" class="form-control bg-light fw-bold" id="totalCostDisplay" readonly style="font-size: 1.2rem;">
                                <input type="hidden" name="total_cost" id="totalCostInput">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">تاريخ الشراء</label>
                                <input type="datetime-local" class="form-control" name="purchase_date" value="<?php echo date('Y-m-d\TH:i'); ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">ملاحظات</label>
                            <textarea class="form-control" name="notes" rows="3" placeholder="ملاحظات إضافية عن عملية الشراء"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" name="add_purchase" class="btn btn-primary" id="submitPurchaseBtn" <?php echo (empty($suppliers) || empty($products)) ? 'disabled' : ''; ?>>
                            <i class="fas fa-shopping-cart me-2"></i>إتمام الشراء
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    // تحديث معلومات المنتج والكمية
    function updateProductInfo() {
        const productSelect = document.getElementById('productSelect');
        const selectedOption = productSelect.options[productSelect.selectedIndex];
        const unitCost = selectedOption.getAttribute('data-price');
        const stock = selectedOption.getAttribute('data-stock');
        const code = selectedOption.getAttribute('data-code');
        
        if (unitCost && stock) {
            document.getElementById('unitCostInput').value = unitCost;
            document.getElementById('originalCost').textContent = parseFloat(unitCost).toLocaleString();
            document.getElementById('stockInfo').textContent = 'المخزن الحالي: ' + stock;
            
            // إعادة حساب الإجمالي
            calculateTotal();
        } else {
            document.getElementById('unitCostInput').value = '';
            document.getElementById('originalCost').textContent = '0';
            document.getElementById('stockInfo').textContent = 'المخزن الحالي: 0';
            document.getElementById('totalCostDisplay').value = '';
            document.getElementById('totalCostInput').value = '';
        }
    }
    
    // حساب التكلفة الإجمالية
    function calculateTotal() {
        const quantity = parseInt(document.getElementById('quantityInput').value) || 0;
        const unitCost = parseFloat(document.getElementById('unitCostInput').value) || 0;
        const total = quantity * unitCost;
        
        document.getElementById('totalCostDisplay').value = total.toLocaleString() + ' د.ج';
        document.getElementById('totalCostInput').value = total;
    }
    
    // تهيئة الصفحة عند التحميل
    document.addEventListener('DOMContentLoaded', function() {
        // تحديث معلومات المنتج عند فتح المودال
        const addPurchaseModal = document.getElementById('addPurchaseModal');
        if (addPurchaseModal) {
            addPurchaseModal.addEventListener('show.bs.modal', function () {
                updateProductInfo();
            });
        }
        
        // البحث والتصفية
        const searchInput = document.getElementById('searchInput');
        const statusFilter = document.getElementById('statusFilter');
        const dateFilter = document.getElementById('dateFilter');
        const filterBtn = document.getElementById('filterBtn');
        const resetBtn = document.getElementById('resetBtn');
        const purchaseRows = document.querySelectorAll('tbody tr');

        function filterPurchases() {
            const searchTerm = searchInput.value.toLowerCase();
            const selectedStatus = statusFilter.value;
            const selectedDate = dateFilter.value;

            purchaseRows.forEach(row => {
                let showRow = true;

                // البحث
                if (searchTerm && showRow) {
                    const supplierName = row.cells[1].textContent.toLowerCase();
                    const productName = row.cells[2].textContent.toLowerCase();
                    if (!supplierName.includes(searchTerm) && !productName.includes(searchTerm)) {
                        showRow = false;
                    }
                }

                // التصفية بالحالة
                if (selectedStatus && showRow) {
                    const statusBadge = row.cells[7].querySelector('.badge');
                    const status = statusBadge.classList.contains('badge-completed') ? 'completed' : 
                                  statusBadge.classList.contains('badge-pending') ? 'pending' : 'cancelled';
                    if (status !== selectedStatus) {
                        showRow = false;
                    }
                }

                // التصفية بالتاريخ
                if (selectedDate && showRow) {
                    const purchaseDate = row.cells[6].textContent.split(' ')[0];
                    if (purchaseDate !== selectedDate) {
                        showRow = false;
                    }
                }

                row.style.display = showRow ? '' : 'none';
            });
        }

        function resetFilters() {
            searchInput.value = '';
            statusFilter.value = '';
            dateFilter.value = '';
            purchaseRows.forEach(row => row.style.display = '');
        }

        // إضافة مستمعي الأحداث
        searchInput.addEventListener('input', filterPurchases);
        statusFilter.addEventListener('change', filterPurchases);
        dateFilter.addEventListener('change', filterPurchases);
        filterBtn.addEventListener('click', filterPurchases);
        resetBtn.addEventListener('click', resetFilters);
    });

    // وظائف الأزرار
    function editPurchase(id) {
        alert('تعديل عملية الشراء رقم: ' + id);
    }

    function viewPurchase(id) {
        alert('عرض تفاصيل عملية الشراء رقم: ' + id);
    }

    function printPurchase(id) {
        alert('طباعة إشعار الشراء رقم: ' + id);
        window.open('purchase_receipt.php?purchase_id=' + id, '_blank');
    }

    function cancelPurchase(id) {
        if (confirm('هل تريد إلغاء هذه العملية؟ سيتم خصم الكمية من المخزن.')) {
            alert('تم إلغاء عملية الشراء رقم: ' + id);
        }
    }
    </script>
    
</body>
</html>