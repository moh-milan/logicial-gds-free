<?php
// modules/clients/index.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit;
}

require_once '../../config/database.php';

// معالجة إضافة عميل جديد
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_client'])) {
    $clientData = [
        'name' => $_POST['name'],
        'email' => $_POST['email'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'address' => $_POST['address'] ?? '',
        'company' => $_POST['company'] ?? '',
        'notes' => $_POST['notes'] ?? ''
    ];
    
    if (addClient($clientData)) {
        $success_message = "✅ تم إضافة العميل بنجاح!";
    } else {
        $error_message = "❌ فشل في إضافة العميل!";
    }
}

// جلب بيانات العملاء
$clients = getAllClients();
$total_clients = count($clients);
$active_clients = count(array_filter($clients, function($client) {
    return $client['status'] === 'active';
}));
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة العملاء - نظام إدارة المخزون</title>
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
        .client-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .client-card:hover {
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
        .client-avatar {
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
                        <h2 class="mb-1">إدارة العملاء</h2>
                        <p class="text-muted mb-0">إدارة وعرض جميع عملاء النظام - العملة: دينار جزائري (د.ج)</p>
                    </div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addClientModal">
                        <i class="fas fa-user-plus me-2"></i>إضافة عميل جديد
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
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stats-number"><?php echo $total_clients; ?></div>
                            <div class="stats-label">إجمالي العملاء</div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stats-card">
                            <div class="stats-icon" style="background: rgba(0, 123, 255, 0.1); color: #007bff;">
                                <i class="fas fa-user-check"></i>
                            </div>
                            <div class="stats-number"><?php echo $active_clients; ?></div>
                            <div class="stats-label">عملاء نشطين</div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stats-card">
                            <div class="stats-icon" style="background: rgba(255, 193, 7, 0.1); color: #ffc107;">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div class="stats-number"><?php echo getTotalClientOrders(); ?></div>
                            <div class="stats-label">إجمالي الطلبات</div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stats-card">
                            <div class="stats-icon" style="background: rgba(108, 117, 125, 0.1); color: #6c757d;">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <div class="stats-number">
                                <span style="font-family: Arial; font-weight: bold;"><?php echo formatCurrency(getTotalClientRevenue()); ?></span>
                            </div>
                            <div class="stats-label">إجمالي المبيعات</div>
                        </div>
                    </div>
                </div>

                <!-- شريط البحث والتصفية -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <input type="text" class="form-control" id="searchInput" placeholder="🔍 ابحث عن عميل...">
                            </div>
                            <div class="col-md-3 mb-2">
                                <select class="form-control" id="statusFilter">
                                    <option value="">جميع الحالات</option>
                                    <option value="active">نشط</option>
                                    <option value="inactive">غير نشط</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-2">
                                <select class="form-control" id="typeFilter">
                                    <option value="">جميع الأنواع</option>
                                    <option value="individual">فرد</option>
                                    <option value="company">شركة</option>
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

                <!-- جدول العملاء -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-list me-2"></i>قائمة العملاء
                            <small class="text-muted">(<?php echo $total_clients; ?> عميل)</small>
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
                                        <th>معلومات الاتصال</th>
                                        <th>الشركة</th>
                                        <th>عدد الطلبات</th>
                                        <th>إجمالي المشتريات</th>
                                        <th>الحالة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($clients)): ?>
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">
                                                <i class="fas fa-users fa-2x mb-2"></i><br>
                                                لا توجد عملاء
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($clients as $index => $client): ?>
                                            <?php
                                            $avatar_bg = getAvatarColor($client['name']);
                                            $status_badge = $client['status'] === 'active' ? 'bg-success' : 'bg-secondary';
                                            $status_text = $client['status'] === 'active' ? 'نشط' : 'غير نشط';
                                            $status_icon = $client['status'] === 'active' ? 'fa-check' : 'fa-pause';
                                            ?>
                                            <tr>
                                                <td><?php echo $index + 1; ?></td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="client-avatar me-3" style="background: <?php echo $avatar_bg; ?>">
                                                            <?php echo getInitials($client['name']); ?>
                                                        </div>
                                                        <div>
                                                            <strong><?php echo htmlspecialchars($client['name']); ?></strong>
                                                            <?php if (!empty($client['type'])): ?>
                                                                <br><small class="text-muted"><?php echo $client['type'] === 'company' ? 'شركة' : 'فرد'; ?></small>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php if (!empty($client['email'])): ?>
                                                        <div><i class="fas fa-envelope text-primary me-2"></i><?php echo htmlspecialchars($client['email']); ?></div>
                                                    <?php endif; ?>
                                                    <?php if (!empty($client['phone'])): ?>
                                                        <div><i class="fas fa-phone text-success me-2"></i><?php echo htmlspecialchars($client['phone']); ?></div>
                                                    <?php endif; ?>
                                                    <?php if (!empty($client['address'])): ?>
                                                        <div><i class="fas fa-map-marker-alt text-warning me-2"></i><?php echo htmlspecialchars(substr($client['address'], 0, 30)) . '...'; ?></div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($client['company'])): ?>
                                                        <strong><?php echo htmlspecialchars($client['company']); ?></strong>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="fw-bold text-primary"><?php echo $client['total_orders'] ?? 0; ?></span>
                                                    <br><small class="text-muted">طلب</small>
                                                </td>
                                                <td>
                                                    <span class="fw-bold text-success"><?php echo formatCurrency($client['total_spent'] ?? 0); ?></span>
                                                    <br><small class="text-muted">د.ج</small>
                                                </td>
                                                <td>
                                                    <span class="badge <?php echo $status_badge; ?>">
                                                        <i class="fas <?php echo $status_icon; ?> me-1"></i><?php echo $status_text; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <button class="btn btn-outline-primary" onclick="editClient(<?php echo $client['id']; ?>)">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-outline-info" onclick="viewClient(<?php echo $client['id']; ?>)">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <button class="btn btn-outline-success" onclick="viewOrders(<?php echo $client['id']; ?>)">
                                                            <i class="fas fa-shopping-cart"></i>
                                                        </button>
                                                        <button class="btn btn-outline-danger" onclick="deleteClient(<?php echo $client['id']; ?>)">
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

    <!-- مودال إضافة عميل جديد -->
    <div class="modal fade" id="addClientModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">إضافة عميل جديد</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">اسم العميل <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" placeholder="أدخل اسم العميل" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">نوع العميل</label>
                                <select class="form-control" name="type">
                                    <option value="individual">فرد</option>
                                    <option value="company">شركة</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">البريد الإلكتروني</label>
                                <input type="email" class="form-control" name="email" placeholder="example@domain.dz">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">رقم الهاتف</label>
                                <input type="tel" class="form-control" name="phone" placeholder="055X-XXX-XXX">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">اسم الشركة</label>
                                <input type="text" class="form-control" name="company" placeholder="اسم الشركة (إذا كان عميل شركة)">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">الحالة</label>
                                <select class="form-control" name="status">
                                    <option value="active">نشط</option>
                                    <option value="inactive">غير نشط</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">العنوان</label>
                            <textarea class="form-control" name="address" rows="2" placeholder="العنوان الكامل"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">ملاحظات</label>
                            <textarea class="form-control" name="notes" rows="3" placeholder="ملاحظات إضافية عن العميل"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" name="add_client" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>حفظ العميل
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
        const typeFilter = document.getElementById('typeFilter');
        const filterBtn = document.getElementById('filterBtn');
        const clientRows = document.querySelectorAll('tbody tr');

        function filterClients() {
            const searchTerm = searchInput.value.toLowerCase();
            const selectedStatus = statusFilter.value;
            const selectedType = typeFilter.value;

            clientRows.forEach(row => {
                let showRow = true;

                // البحث
                if (searchTerm) {
                    const clientName = row.cells[1].textContent.toLowerCase();
                    const clientCompany = row.cells[3].textContent.toLowerCase();
                    if (!clientName.includes(searchTerm) && !clientCompany.includes(searchTerm)) {
                        showRow = false;
                    }
                }

                // التصفية بالحالة
                if (selectedStatus && showRow) {
                    const statusBadge = row.cells[6].querySelector('.badge');
                    const status = statusBadge.textContent.includes('نشط') ? 'active' : 'inactive';
                    if (status !== selectedStatus) {
                        showRow = false;
                    }
                }

                // التصفية بالنوع
                if (selectedType && showRow) {
                    const clientType = row.cells[1].textContent.includes('شركة') ? 'company' : 'individual';
                    if (clientType !== selectedType) {
                        showRow = false;
                    }
                }

                row.style.display = showRow ? '' : 'none';
            });
        }

        // إضافة مستمعي الأحداث
        searchInput.addEventListener('input', filterClients);
        statusFilter.addEventListener('change', filterClients);
        typeFilter.addEventListener('change', filterClients);
        filterBtn.addEventListener('click', filterClients);
    });

    // وظائف الأزرار
    function editClient(id) {
        alert('تعديل العميل رقم: ' + id);
    }

    function viewClient(id) {
        alert('عرض تفاصيل العميل رقم: ' + id);
    }

    function viewOrders(id) {
        alert('عرض طلبات العميل رقم: ' + id);
    }

    function deleteClient(id) {
        if (confirm('هل تريد حذف هذا العميل؟')) {
            alert('تم حذف العميل رقم: ' + id);
        }
    }
    </script>
    
</body>
</html>