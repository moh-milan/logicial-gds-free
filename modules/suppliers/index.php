<?php
// modules/suppliers/index.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit;
}

require_once '../../config/database.php';

// معالجة إضافة مورد جديد
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_supplier'])) {
    $supplierData = [
        'name' => $_POST['name'],
        'contact_person' => $_POST['contact_person'] ?? '',
        'email' => $_POST['email'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'address' => $_POST['address'] ?? '',
        'city' => $_POST['city'] ?? '',
        'website' => $_POST['website'] ?? '',
        'notes' => $_POST['notes'] ?? ''
    ];
    
    if (addSupplier($supplierData)) {
        $success_message = "✅ تم إضافة المورد بنجاح!";
    } else {
        $error_message = "❌ فشل في إضافة المورد!";
    }
}

// جلب بيانات الموردين
$suppliers = getAllSuppliers();
$total_suppliers = count($suppliers);
$active_suppliers = count(array_filter($suppliers, function($supplier) {
    return $supplier['status'] === 'active';
}));
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة الموردين - نظام إدارة المخزون</title>
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
        .supplier-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .supplier-card:hover {
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
        .rating-stars {
            color: #ffc107;
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
                        <h2 class="mb-1">إدارة الموردين</h2>
                        <p class="text-muted mb-0">إدارة موردي المنتجات والخدمات - العملة: دينار جزائري (د.ج)</p>
                    </div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
                        <i class="fas fa-truck-loading me-2"></i>إضافة مورد جديد
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
                                <i class="fas fa-truck"></i>
                            </div>
                            <div class="stats-number"><?php echo $total_suppliers; ?></div>
                            <div class="stats-label">إجمالي الموردين</div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stats-card">
                            <div class="stats-icon" style="background: rgba(0, 123, 255, 0.1); color: #007bff;">
                                <i class="fas fa-user-check"></i>
                            </div>
                            <div class="stats-number"><?php echo $active_suppliers; ?></div>
                            <div class="stats-label">موردين نشطين</div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stats-card">
                            <div class="stats-icon" style="background: rgba(255, 193, 7, 0.1); color: #ffc107;">
                                <i class="fas fa-shopping-bag"></i>
                            </div>
                            <div class="stats-number"><?php echo getTotalSupplierOrders(); ?></div>
                            <div class="stats-label">إجمالي المشتريات</div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stats-card">
                            <div class="stats-icon" style="background: rgba(108, 117, 125, 0.1); color: #6c757d;">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <div class="stats-number">
                                <span style="font-family: Arial; font-weight: bold;"><?php echo formatCurrency(getTotalSupplierSpent()); ?></span>
                            </div>
                            <div class="stats-label">إجمالي المشتريات</div>
                        </div>
                    </div>
                </div>

                <!-- شريط البحث والتصفية -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <input type="text" class="form-control" id="searchInput" placeholder="🔍 ابحث عن مورد...">
                            </div>
                            <div class="col-md-3 mb-2">
                                <select class="form-control" id="statusFilter">
                                    <option value="">جميع الحالات</option>
                                    <option value="active">نشط</option>
                                    <option value="inactive">غير نشط</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-2">
                                <select class="form-control" id="cityFilter">
                                    <option value="">جميع المدن</option>
                                    <option value="الجزائر العاصمة">الجزائر العاصمة</option>
                                    <option value="وهران">وهران</option>
                                    <option value="قسنطينة">قسنطينة</option>
                                    <option value="عنابة">عنابة</option>
                                    <option value="باتنة">باتنة</option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <button class="btn btn-outline-primary w-100" id="filterBtn">
                                    <i class="fas fa-filter me-2"></i>تصفية
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- جدول الموردين -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-list me-2"></i>قائمة الموردين
                            <small class="text-muted">(<?php echo $total_suppliers; ?> مورد)</small>
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
                                        <th>معلومات الاتصال</th>
                                        <th>المدينة</th>
                                        <th>عدد المشتريات</th>
                                        <th>إجمالي المشتريات</th>
                                        <th>التقييم</th>
                                        <th>الحالة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($suppliers)): ?>
                                        <tr>
                                            <td colspan="9" class="text-center text-muted py-4">
                                                <i class="fas fa-truck fa-2x mb-2"></i><br>
                                                لا توجد موردين
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($suppliers as $index => $supplier): ?>
                                            <?php
                                            $avatar_bg = getAvatarColor($supplier['name']);
                                            $status_badge = $supplier['status'] === 'active' ? 'bg-success' : 'bg-secondary';
                                            $status_text = $supplier['status'] === 'active' ? 'نشط' : 'غير نشط';
                                            $status_icon = $supplier['status'] === 'active' ? 'fa-check' : 'fa-pause';
                                            $rating = $supplier['rating'] ?? 5;
                                            ?>
                                            <tr>
                                                <td><?php echo $index + 1; ?></td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="supplier-avatar me-3" style="background: <?php echo $avatar_bg; ?>">
                                                            <?php echo getInitials($supplier['name']); ?>
                                                        </div>
                                                        <div>
                                                            <strong><?php echo htmlspecialchars($supplier['name']); ?></strong>
                                                            <?php if (!empty($supplier['contact_person'])): ?>
                                                                <br><small class="text-muted"><?php echo htmlspecialchars($supplier['contact_person']); ?></small>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php if (!empty($supplier['email'])): ?>
                                                        <div><i class="fas fa-envelope text-primary me-2"></i><?php echo htmlspecialchars($supplier['email']); ?></div>
                                                    <?php endif; ?>
                                                    <?php if (!empty($supplier['phone'])): ?>
                                                        <div><i class="fas fa-phone text-success me-2"></i><?php echo htmlspecialchars($supplier['phone']); ?></div>
                                                    <?php endif; ?>
                                                    <?php if (!empty($supplier['website'])): ?>
                                                        <div><i class="fas fa-globe text-info me-2"></i><?php echo htmlspecialchars($supplier['website']); ?></div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($supplier['city'])): ?>
                                                        <span class="fw-bold"><?php echo htmlspecialchars($supplier['city']); ?></span>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="fw-bold text-primary"><?php echo $supplier['total_orders'] ?? 0; ?></span>
                                                    <br><small class="text-muted">عملية شراء</small>
                                                </td>
                                                <td>
                                                    <span class="fw-bold text-success"><?php echo formatCurrency($supplier['total_spent'] ?? 0); ?></span>
                                                    <br><small class="text-muted">د.ج</small>
                                                </td>
                                                <td>
                                                    <div class="rating-stars">
                                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                                            <i class="fas fa-star<?php echo $i <= $rating ? '' : '-empty'; ?>"></i>
                                                        <?php endfor; ?>
                                                        <br>
                                                        <small class="text-muted">(<?php echo $rating; ?>/5)</small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge <?php echo $status_badge; ?>">
                                                        <i class="fas <?php echo $status_icon; ?> me-1"></i><?php echo $status_text; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <button class="btn btn-outline-primary" onclick="editSupplier(<?php echo $supplier['id']; ?>)">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-outline-info" onclick="viewSupplier(<?php echo $supplier['id']; ?>)">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <button class="btn btn-outline-warning" onclick="viewPurchases(<?php echo $supplier['id']; ?>)">
                                                            <i class="fas fa-shopping-bag"></i>
                                                        </button>
                                                        <button class="btn btn-outline-danger" onclick="deleteSupplier(<?php echo $supplier['id']; ?>)">
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

    <!-- مودال إضافة مورد جديد -->
    <div class="modal fade" id="addSupplierModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">إضافة مورد جديد</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">اسم المورد <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" placeholder="أدخل اسم المورد" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">جهة الاتصال</label>
                                <input type="text" class="form-control" name="contact_person" placeholder="اسم الشخص المسؤول">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">البريد الإلكتروني</label>
                                <input type="email" class="form-control" name="email" placeholder="supplier@domain.dz">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">رقم الهاتف</label>
                                <input type="tel" class="form-control" name="phone" placeholder="055X-XXX-XXX">
                            </div>
                        </div>
                         <div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">المدينة</label>
        <select class="form-control" name="city">
            <option value="أدرار">أدرار</option>
            <option value="الشلف">الشلف</option>
            <option value="الأغواط">الأغواط</option>
            <option value="أم البواقي">أم البواقي</option>
            <option value="باتنة">باتنة</option>
            <option value="بجاية">بجاية</option>
            <option value="بسكرة">بسكرة</option>
            <option value="بشار">بشار</option>
            <option value="البليدة">البليدة</option>
            <option value="البويرة">البويرة</option>
            <option value="تمنراست">تمنراست</option>
            <option value="تبسة">تبسة</option>
            <option value="تلمسان">تلمسان</option>
            <option value="تيارت">تيارت</option>
            <option value="تيزي وزو">تيزي وزو</option>
            <option value="الجزائر العاصمة">الجزائر العاصمة</option>
            <option value="الجلفة">الجلفة</option>
            <option value="جيجل">جيجل</option>
            <option value="سطيف">سطيف</option>
            <option value="سعيدة">سعيدة</option>
            <option value="سكيكدة">سكيكدة</option>
            <option value="سيدي بلعباس">سيدي بلعباس</option>
            <option value="عنابة">عنابة</option>
            <option value="قالمة">قالمة</option>
            <option value="قسنطينة">قسنطينة</option>
            <option value="المدية">المدية</option>
            <option value="مستغانم">مستغانم</option>
            <option value="المسيلة">المسيلة</option>
            <option value="معسكر">معسكر</option>
            <option value="ورقلة">ورقلة</option>
            <option value="وهران">وهران</option>
            <option value="البيض">البيض</option>
            <option value="إليزي">إليزي</option>
            <option value="برج بوعريريج">برج بوعريريج</option>
            <option value="بومرداس">بومرداس</option>
            <option value="الطارف">الطارف</option>
            <option value="تندوف">تندوف</option>
            <option value="تيسمسيلت">تيسمسيلt</option>
            <option value="الوادي">الوادي</option>
            <option value="خنشلة">خنشلة</option>
            <option value="سوق أهراس">سوق أهراس</option>
            <option value="تيبازة">تيبازة</option>
            <option value="ميلة">ميلة</option>
            <option value="عين الدفلى">عين الدفلى</option>
            <option value="النعامة">النعامة</option>
            <option value="عين تموشنت">عين تموشنت</option>
            <option value="غرداية">غرداية</option>
            <option value="غليزان">غليزان</option>
            <option value="تيميمون">تيميمون</option>
            <option value="برج باجي مختار">برج باجي مختار</option>
            <option value="أولاد جلال">أولاد جلال</option>
            <option value="بني عباس">بني عباس</option>
            <option value="عين صالح">عين صالح</option>
            <option value="عين قزام">عين قزام</option>
            <option value="تقرت">تقرت</option>
            <option value="جانت">جانت</option>
            <option value="المغير">المغير</option>
            <option value="المنيعة">المنيعة</option>
            <option value="آفلو">آفلو</option>
            <option value="بريكة">بريكة</option>
            <option value="القنطرة">القنطرة</option>
            <option value="بير العاتر">بير العاتر</option>
            <option value="العريشة">العريشة</option>
            <option value="قصر الشلالة">قصر الشلالة</option>
            <option value="عين وسارة">عين وسارة</option>
            <option value="مسعد">مسعد</option>
            <option value="قصر البخاري">قصر البخاري</option>
            <option value="بوسعادة">بوسعادة</option>
            <option value="الأبيض سيدي الشيخ">الأبيض سيدي الشيخ</option>
        </select>
    </div>
    
    <div class="col-md-6 mb-3">
        <label class="form-label">الموقع الإلكتروني</label>
        <input type="url" class="form-control" name="website" placeholder="https://example.dz">
    </div>
</div>
                        
                        <div class="mb-3">
                            <label class="form-label">العنوان</label>
                            <textarea class="form-control" name="address" rows="2" placeholder="العنوان الكامل"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">ملاحظات</label>
                            <textarea class="form-control" name="notes" rows="3" placeholder="ملاحظات إضافية عن المورد"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" name="add_supplier" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>حفظ المورد
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    // جعل البحث والتصفية يعملان
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const statusFilter = document.getElementById('statusFilter');
        const cityFilter = document.getElementById('cityFilter');
        const filterBtn = document.getElementById('filterBtn');
        const supplierRows = document.querySelectorAll('tbody tr');

        function filterSuppliers() {
            const searchTerm = searchInput.value.toLowerCase();
            const selectedStatus = statusFilter.value;
            const selectedCity = cityFilter.value;

            supplierRows.forEach(row => {
                let showRow = true;

                // البحث
                if (searchTerm) {
                    const supplierName = row.cells[1].textContent.toLowerCase();
                    const contactPerson = row.cells[1].querySelector('small')?.textContent.toLowerCase() || '';
                    if (!supplierName.includes(searchTerm) && !contactPerson.includes(searchTerm)) {
                        showRow = false;
                    }
                }

                // التصفية بالحالة
                if (selectedStatus && showRow) {
                    const statusBadge = row.cells[7].querySelector('.badge');
                    const status = statusBadge.textContent.includes('نشط') ? 'active' : 'inactive';
                    if (status !== selectedStatus) {
                        showRow = false;
                    }
                }

                // التصفية بالمدينة
                if (selectedCity && showRow) {
                    const city = row.cells[3].textContent.trim();
                    if (city !== selectedCity) {
                        showRow = false;
                    }
                }

                row.style.display = showRow ? '' : 'none';
            });
        }

        // إضافة مستمعي الأحداث
        searchInput.addEventListener('input', filterSuppliers);
        statusFilter.addEventListener('change', filterSuppliers);
        cityFilter.addEventListener('change', filterSuppliers);
        filterBtn.addEventListener('click', filterSuppliers);
    });

    // وظائف الأزرار
    function editSupplier(id) {
        alert('تعديل المورد رقم: ' + id);
    }

    function viewSupplier(id) {
        alert('عرض تفاصيل المورد رقم: ' + id);
    }

    function viewPurchases(id) {
        alert('عرض مشتريات المورد رقم: ' + id);
    }

    function deleteSupplier(id) {
        if (confirm('هل تريد حذف هذا المورد؟')) {
            alert('تم حذف المورد رقم: ' + id);
        }
    }
    </script>
    
</body>
</html>