<?php
// modules/sales/index.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit;
}

require_once '../../config/database.php';

// تأكد من وجود جدول المبيعات
setupSalesTable();

// معالجة إضافة عملية بيع جديدة
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_sale'])) {
    $saleData = [
        'customer_name' => trim($_POST['customer_name']),
        'customer_phone' => trim($_POST['customer_phone'] ?? ''),
        'customer_email' => trim($_POST['customer_email'] ?? ''),
        'product_id' => intval($_POST['product_id']),
        'quantity' => intval($_POST['quantity']),
        'unit_price' => floatval($_POST['unit_price']),
        'total_amount' => floatval($_POST['quantity']) * floatval($_POST['unit_price']),
        'payment_method' => $_POST['payment_method'],
        'sale_date' => $_POST['sale_date'] ?? date('Y-m-d H:i:s'),
        'notes' => trim($_POST['notes'] ?? '')
    ];
    
    if (addSale($saleData)) {
        $success_message = "✅ تمت عملية البيع بنجاح!";
    } else {
        $error_message = "❌ فشل في إتمام عملية البيع!";
    }
}

// جلب بيانات المبيعات
$sales = getAllSales();
$total_sales = count($sales);
$today_sales = getTodaySales();
$monthly_revenue = getMonthlyRevenue();

// جلب المنتجات المتاحة من قاعدة البيانات
$available_products = getAvailableProducts();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المبيعات - نظام إدارة المخزون</title>
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
        .sale-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .sale-card:hover {
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
        .customer-avatar {
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
        
        .btn-success {
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            padding: 10px 20px;
            font-weight: 600;
        }
        .btn-success:hover {
            background: linear-gradient(45deg, #218838, #1e9e8a);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }
        
        .product-option {
            padding: 8px 12px;
            border-bottom: 1px solid #eee;
        }
        .product-option:last-child {
            border-bottom: none;
        }
        
        /* تحسين مظهر القائمة المنسدلة */
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
                        <h2 class="mb-1">إدارة المبيعات</h2>
                        <p class="text-muted mb-0">إدارة عمليات البيع والمبيعات - العملة: دينار جزائري (د.ج)</p>
                    </div>
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addSaleModal">
                        <i class="fas fa-cash-register me-2"></i>عملية بيع جديدة
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
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div class="stats-number"><?php echo $total_sales; ?></div>
                            <div class="stats-label">إجمالي المبيعات</div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stats-card">
                            <div class="stats-icon" style="background: rgba(0, 123, 255, 0.1); color: #007bff;">
                                <i class="fas fa-calendar-day"></i>
                            </div>
                            <div class="stats-number"><?php echo $today_sales['count']; ?></div>
                            <div class="stats-label">مبيعات اليوم</div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stats-card">
                            <div class="stats-icon" style="background: rgba(255, 193, 7, 0.1); color: #ffc107;">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="stats-number">
                                <span style="font-family: Arial; font-weight: bold;"><?php echo formatCurrency($today_sales['revenue']); ?></span>
                            </div>
                            <div class="stats-label">إيرادات اليوم</div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stats-card">
                            <div class="stats-icon" style="background: rgba(108, 117, 125, 0.1); color: #6c757d;">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <div class="stats-number">
                                <span style="font-family: Arial; font-weight: bold;"><?php echo formatCurrency($monthly_revenue); ?></span>
                            </div>
                            <div class="stats-label">إيرادات الشهر</div>
                        </div>
                    </div>
                </div>

                <!-- تحذير إذا لم توجد منتجات -->
                <?php if (empty($available_products)): ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>تنبيه!</strong> لا توجد منتجات متاحة في المخزن. يرجى إضافة منتجات أولاً من قسم إدارة المنتجات.
                        <a href="../products/index.php" class="alert-link">الذهاب إلى إدارة المنتجات</a>
                    </div>
                <?php endif; ?>

                <!-- شريط البحث والتصفية -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <input type="text" class="form-control" id="searchInput" placeholder="🔍 ابحث عن عميل أو منتج...">
                            </div>
                            <div class="col-md-2 mb-2">
                                <select class="form-control" id="statusFilter">
                                    <option value="">جميع الحالات</option>
                                    <option value="completed">مكتمل</option>
                                    <option value="pending">قيد الانتظار</option>
                                    <option value="cancelled">ملغي</option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <select class="form-control" id="paymentFilter">
                                    <option value="">جميع طرق الدفع</option>
                                    <option value="cash">نقدي</option>
                                    <option value="card">بطاقة</option>
                                    <option value="transfer">تحويل</option>
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
                        </div>
                    </div>
                </div>

                <!-- جدول المبيعات -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-list me-2"></i>سجل المبيعات
                            <small class="text-muted">(<?php echo $total_sales; ?> عملية بيع)</small>
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
                                        <th>العميل</th>
                                        <th>المنتج</th>
                                        <th>الكمية</th>
                                        <th>سعر الوحدة</th>
                                        <th>المبلغ الإجمالي</th>
                                        <th>طريقة الدفع</th>
                                        <th>التاريخ</th>
                                        <th>الحالة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($sales)): ?>
                                        <tr>
                                            <td colspan="10" class="text-center text-muted py-4">
                                                <i class="fas fa-shopping-cart fa-2x mb-2"></i><br>
                                                لا توجد عمليات بيع
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($sales as $index => $sale): ?>
                                            <?php
                                            $avatar_bg = getAvatarColor($sale['customer_name']);
                                            $status_badge = 'badge-' . $sale['status'];
                                            $status_text = getStatusText($sale['status']);
                                            $status_icon = getStatusIcon($sale['status']);
                                            ?>
                                            <tr>
                                                <td><?php echo $index + 1; ?></td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="customer-avatar me-3" style="background: <?php echo $avatar_bg; ?>">
                                                            <?php echo getInitials($sale['customer_name']); ?>
                                                        </div>
                                                        <div>
                                                            <strong><?php echo htmlspecialchars($sale['customer_name']); ?></strong>
                                                            <?php if (!empty($sale['customer_phone'])): ?>
                                                                <br><small class="text-muted"><?php echo htmlspecialchars($sale['customer_phone']); ?></small>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($sale['product_name']); ?></strong>
                                                    <br><small class="text-muted">كود: <?php echo htmlspecialchars($sale['product_code']); ?></small>
                                                </td>
                                                <td>
                                                    <span class="fw-bold text-primary"><?php echo $sale['quantity']; ?></span>
                                                    <br><small class="text-muted">وحدة</small>
                                                </td>
                                                <td>
                                                    <span class="fw-bold"><?php echo formatCurrency($sale['unit_price']); ?></span>
                                                    <br><small class="text-muted">د.ج</small>
                                                </td>
                                                <td>
                                                    <span class="fw-bold text-success"><?php echo formatCurrency($sale['total_amount']); ?></span>
                                                    <br><small class="text-muted">د.ج</small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info">
                                                        <?php echo getPaymentMethodText($sale['payment_method']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php echo date('Y-m-d H:i', strtotime($sale['sale_date'])); ?>
                                                    <br><small class="text-muted"><?php echo time_elapsed_string($sale['sale_date']); ?></small>
                                                </td>
                                                <td>
                                                    <span class="badge <?php echo $status_badge; ?>">
                                                        <i class="fas <?php echo $status_icon; ?> me-1"></i><?php echo $status_text; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <button class="btn btn-outline-primary" onclick="editSale(<?php echo $sale['id']; ?>)">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-outline-info" onclick="viewSale(<?php echo $sale['id']; ?>)">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <button class="btn btn-outline-warning" onclick="printInvoice(<?php echo $sale['id']; ?>)">
                                                            <i class="fas fa-receipt"></i>
                                                        </button>
                                                        <button class="btn btn-outline-danger" onclick="cancelSale(<?php echo $sale['id']; ?>)">
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

    <!-- مودال إضافة عملية بيع جديدة -->
    <div class="modal fade" id="addSaleModal" tabindex="-1" aria-labelledby="addSaleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addSaleModalLabel">عملية بيع جديدة</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">اسم العميل <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="customer_name" placeholder="أدخل اسم العميل" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">رقم الهاتف</label>
                                <input type="tel" class="form-control" name="customer_phone" placeholder="055X-XXX-XXX">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">البريد الإلكتروني</label>
                                <input type="email" class="form-control" name="customer_email" placeholder="customer@domain.dz">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">طريقة الدفع <span class="text-danger">*</span></label>
                                <select class="form-select" name="payment_method" required>
                                    <option value="cash">نقدي</option>
                                    <option value="card">بطاقة ائتمان</option>
                                    <option value="transfer">تحويل بنكي</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">المنتج <span class="text-danger">*</span></label>
                                <select class="form-select" name="product_id" id="productSelect" required onchange="updateProductInfo()">
                                    <option value="">اختر المنتج من القائمة...</option>
                                    <?php if (!empty($available_products)): ?>
                                        <?php foreach ($available_products as $product): ?>
                                            <option value="<?php echo $product['id']; ?>" 
                                                    data-price="<?php echo $product['selling_price']; ?>"
                                                    data-stock="<?php echo $product['current_stock']; ?>"
                                                    data-code="<?php echo htmlspecialchars($product['code']); ?>"
                                                    data-category="<?php echo htmlspecialchars($product['category'] ?? ''); ?>">
                                                <?php echo htmlspecialchars($product['name']); ?> - 
                                                <?php echo htmlspecialchars($product['code']); ?> 
                                                (المخزن: <?php echo $product['current_stock']; ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="" disabled>--- لا توجد منتجات متاحة ---</option>
                                    <?php endif; ?>
                                </select>
                                <small class="form-text text-muted" id="stockInfo">المخزن المتاح: 0</small>
                                <small class="form-text text-info" id="productDetails"></small>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">الكمية <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="quantity" id="quantityInput" min="1" value="1" required onchange="calculateTotal()">
                                <small class="form-text text-danger" id="quantityError" style="display: none;"></small>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">سعر الوحدة <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control" name="unit_price" id="unitPriceInput" required onchange="calculateTotal()">
                                <small class="form-text text-muted">سعر البيع: <span id="originalPrice">0</span> د.ج</small>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">المبلغ الإجمالي</label>
                                <input type="text" class="form-control bg-light fw-bold" id="totalAmountDisplay" readonly style="font-size: 1.2rem;">
                                <input type="hidden" name="total_amount" id="totalAmountInput">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">تاريخ البيع</label>
                                <input type="datetime-local" class="form-control" name="sale_date" value="<?php echo date('Y-m-d\TH:i'); ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">ملاحظات</label>
                            <textarea class="form-control" name="notes" rows="3" placeholder="ملاحظات إضافية عن عملية البيع"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" name="add_sale" class="btn btn-success" id="submitSaleBtn" <?php echo empty($available_products) ? 'disabled' : ''; ?>>
                            <i class="fas fa-cash-register me-2"></i>إتمام البيع
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
        const unitPrice = selectedOption.getAttribute('data-price');
        const stock = selectedOption.getAttribute('data-stock');
        const code = selectedOption.getAttribute('data-code');
        const category = selectedOption.getAttribute('data-category');
        
        if (unitPrice && stock) {
            document.getElementById('unitPriceInput').value = unitPrice;
            document.getElementById('originalPrice').textContent = parseFloat(unitPrice).toLocaleString();
            document.getElementById('stockInfo').textContent = 'المخزن المتاح: ' + stock;
            document.getElementById('quantityInput').max = stock;
            
            // عرض تفاصيل المنتج
            let details = 'كود: ' + code;
            if (category) {
                details += ' | التصنيف: ' + category;
            }
            document.getElementById('productDetails').textContent = details;
            
            // إعادة حساب الإجمالي
            calculateTotal();
            
            // إخفاء رسالة الخطأ إذا كانت موجودة
            document.getElementById('quantityError').style.display = 'none';
            document.getElementById('submitSaleBtn').disabled = false;
        } else {
            document.getElementById('unitPriceInput').value = '';
            document.getElementById('originalPrice').textContent = '0';
            document.getElementById('stockInfo').textContent = 'المخزن المتاح: 0';
            document.getElementById('productDetails').textContent = '';
            document.getElementById('totalAmountDisplay').value = '';
            document.getElementById('totalAmountInput').value = '';
        }
    }
    
    // حساب المبلغ الإجمالي والتحقق من الكمية
    function calculateTotal() {
        const quantity = parseInt(document.getElementById('quantityInput').value) || 0;
        const unitPrice = parseFloat(document.getElementById('unitPriceInput').value) || 0;
        const maxStock = parseInt(document.getElementById('quantityInput').max) || 0;
        const total = quantity * unitPrice;
        
        // التحقق من أن الكمية لا تتجاوز المخزن المتاح
        const quantityError = document.getElementById('quantityError');
        const submitBtn = document.getElementById('submitSaleBtn');
        
        if (quantity > maxStock) {
            quantityError.textContent = 'الكمية المطلوبة (' + quantity + ') تتجاوز المخزن المتاح (' + maxStock + ')';
            quantityError.style.display = 'block';
            submitBtn.disabled = true;
        } else if (quantity <= 0) {
            quantityError.textContent = 'الكمية يجب أن تكون أكبر من صفر';
            quantityError.style.display = 'block';
            submitBtn.disabled = true;
        } else {
            quantityError.style.display = 'none';
            submitBtn.disabled = false;
        }
        
        document.getElementById('totalAmountDisplay').value = total.toLocaleString() + ' د.ج';
        document.getElementById('totalAmountInput').value = total;
    }
    
    // تهيئة الصفحة عند التحميل
    document.addEventListener('DOMContentLoaded', function() {
        // تحديث معلومات المنتج عند فتح المودال
        const addSaleModal = document.getElementById('addSaleModal');
        if (addSaleModal) {
            addSaleModal.addEventListener('show.bs.modal', function () {
                updateProductInfo();
            });
        }
        
        // البحث والتصفية
        const searchInput = document.getElementById('searchInput');
        const statusFilter = document.getElementById('statusFilter');
        const paymentFilter = document.getElementById('paymentFilter');
        const dateFilter = document.getElementById('dateFilter');
        const filterBtn = document.getElementById('filterBtn');
        const saleRows = document.querySelectorAll('tbody tr');

        function filterSales() {
            const searchTerm = searchInput.value.toLowerCase();
            const selectedStatus = statusFilter.value;
            const selectedPayment = paymentFilter.value;
            const selectedDate = dateFilter.value;

            saleRows.forEach(row => {
                let showRow = true;

                // البحث
                if (searchTerm && showRow) {
                    const customerName = row.cells[1].textContent.toLowerCase();
                    const productName = row.cells[2].textContent.toLowerCase();
                    if (!customerName.includes(searchTerm) && !productName.includes(searchTerm)) {
                        showRow = false;
                    }
                }

                // التصفية بالحالة
                if (selectedStatus && showRow) {
                    const statusBadge = row.cells[8].querySelector('.badge');
                    const status = statusBadge.classList.contains('badge-completed') ? 'completed' : 
                                  statusBadge.classList.contains('badge-pending') ? 'pending' : 'cancelled';
                    if (status !== selectedStatus) {
                        showRow = false;
                    }
                }

                // التصفية بطريقة الدفع
                if (selectedPayment && showRow) {
                    const payment = row.cells[6].textContent.trim();
                    const paymentMap = {'نقدي': 'cash', 'بطاقة': 'card', 'تحويل': 'transfer'};
                    if (paymentMap[payment] !== selectedPayment) {
                        showRow = false;
                    }
                }

                // التصفية بالتاريخ
                if (selectedDate && showRow) {
                    const saleDate = row.cells[7].textContent.split(' ')[0];
                    if (saleDate !== selectedDate) {
                        showRow = false;
                    }
                }

                row.style.display = showRow ? '' : 'none';
            });
        }

        // إضافة مستمعي الأحداث
        searchInput.addEventListener('input', filterSales);
        statusFilter.addEventListener('change', filterSales);
        paymentFilter.addEventListener('change', filterSales);
        dateFilter.addEventListener('change', filterSales);
        filterBtn.addEventListener('click', filterSales);
    });

    // وظائف الأزرار
    function editSale(id) {
        alert('تعديل عملية البيع رقم: ' + id);
    }

    function viewSale(id) {
        alert('عرض تفاصيل عملية البيع رقم: ' + id);
    }

    function printInvoice(id) {
        alert('طباعة فاتورة البيع رقم: ' + id);
        window.open('invoice.php?sale_id=' + id, '_blank');
    }

    function cancelSale(id) {
        if (confirm('هل تريد إلغاء هذه العملية؟ سيتم إرجاع الكمية إلى المخزن.')) {
            alert('تم إلغاء عملية البيع رقم: ' + id);
        }
    }
    </script>
    
</body>
</html>