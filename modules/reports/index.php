<?php
// modules/reports/index.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>التقارير - نظام إدارة المخزون</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; font-family: 'Segoe UI'; }
        .sidebar { background: linear-gradient(135deg, #2c3e50, #34495e); color: white; min-height: 100vh; padding: 0; }
        .main-content { padding: 20px; }
        .sidebar-header { padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.1); text-align: center; }
        .sidebar-menu { padding: 20px 0; }
        .menu-item { color: rgba(255,255,255,0.8); text-decoration: none; display: block; padding: 12px 20px; transition: all 0.3s; border-right: 3px solid transparent; }
        .menu-item:hover, .menu-item.active { background: rgba(255,255,255,0.1); color: white; border-right-color: #27ae60; }
        .stats-card { background: white; border-radius: 10px; padding: 25px; text-align: center; box-shadow: 0 2px 15px rgba(0,0,0,0.1); border: none; transition: transform 0.3s; }
        .stats-card:hover { transform: translateY(-5px); }
        .currency { font-family: Arial, sans-serif; font-weight: bold; color: #2c3e50; }
        .report-card { border-left: 4px solid #007bff; }
        .sales-card { border-left: 4px solid #28a745; }
        .products-card { border-left: 4px solid #ffc107; }
        .clients-card { border-left: 4px solid #dc3545; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <nav class="sidebar col-md-3">
                <div class="sidebar-header">
                    <h4 class="mb-0"><i class="fas fa-warehouse"></i><br><small>StockFlow الجزائر</small></h4>
                    <div class="mt-3">
                        <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <span class="text-white fw-bold"><?php echo isset($_SESSION['user_name']) ? strtoupper(substr($_SESSION['user_name'], 0, 1)) : 'A'; ?></span>
                        </div>
                        <div class="mt-2">
                            <div class="fw-bold"><?php echo $_SESSION['user_name'] ?? 'المستخدم'; ?></div>
                            <small class="text-muted"><?php echo $_SESSION['user_role'] ?? 'مدير'; ?></small>
                        </div>
                    </div>
                </div>
                <div class="sidebar-menu">
                    <a href="../dashboard/" class="menu-item"><i class="fas fa-tachometer-alt me-2"></i> لوحة التحكم</a>
                    <a href="../products/" class="menu-item"><i class="fas fa-boxes me-2"></i> إدارة المنتجات</a>
                    <a href="../clients/" class="menu-item"><i class="fas fa-users me-2"></i> إدارة العملاء</a>
                    <a href="../suppliers/" class="menu-item"><i class="fas fa-truck me-2"></i> إدارة الموردين</a>
                    <a href="../sales/" class="menu-item"><i class="fas fa-shopping-cart me-2"></i> إدارة المبيعات</a>
                    <a href="../purchases/" class="menu-item"><i class="fas fa-shopping-bag me-2"></i> إدارة المشتريات</a>
                    <a href="../categories/" class="menu-item"><i class="fas fa-tags me-2"></i> التصنيفات</a>
                    <a href="index.php" class="menu-item active"><i class="fas fa-chart-bar me-2"></i> التقارير</a>
                    <div class="mt-4 pt-3 border-top border-secondary">
                        <a href="../../index.php?logout=1" class="menu-item text-danger"><i class="fas fa-sign-out-alt me-2"></i> تسجيل الخروج</a>
                    </div>
                </div>
            </nav>
            <div class="main-content col-md-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="mb-1">التقارير والإحصائيات</h2>
                        <p class="text-muted mb-0">تحليلات شاملة للنظام - العملة: دينار جزائري (د.ج)</p>
                    </div>
                    <div class="btn-group">
                        <button class="btn btn-outline-primary">
                            <i class="fas fa-download me-2"></i>تصدير تقرير
                        </button>
                        <button class="btn btn-outline-success">
                            <i class="fas fa-print me-2"></i>طباعة
                        </button>
                    </div>
                </div>

                <!-- إحصائيات سريعة -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stats-card sales-card">
                            <div class="stats-icon" style="background: rgba(40, 167, 69, 0.1); color: #28a745;">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div class="stats-number">158</div>
                            <div class="stats-label">إجمالي المبيعات</div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stats-card report-card">
                            <div class="stats-icon" style="background: rgba(0, 123, 255, 0.1); color: #007bff;">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="stats-number">
                                <span class="currency">5,840,000 د.ج</span>
                            </div>
                            <div class="stats-label">إجمالي الإيرادات</div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stats-card products-card">
                            <div class="stats-icon" style="background: rgba(255, 193, 7, 0.1); color: #ffc107;">
                                <i class="fas fa-boxes"></i>
                            </div>
                            <div class="stats-number">342</div>
                            <div class="stats-label">المنتجات المباعة</div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stats-card clients-card">
                            <div class="stats-icon" style="background: rgba(220, 53, 69, 0.1); color: #dc3545;">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stats-number">89</div>
                            <div class="stats-label">عملاء نشطين</div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- المبيعات الشهرية -->
                    <div class="col-lg-8 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-bar me-2"></i>المبيعات الشهرية - 2024
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>الشهر</th>
                                                <th>عدد المبيعات</th>
                                                <th>إجمالي الإيرادات</th>
                                                <th>متوسط قيمة البيع</th>
                                                <th>النمو</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><strong>يناير 2024</strong></td>
                                                <td>42</td>
                                                <td class="text-success fw-bold">1,250,000 د.ج</td>
                                                <td class="text-info">29,762 د.ج</td>
                                                <td><span class="badge bg-success">+15%</span></td>
                                            </tr>
                                            <tr>
                                                <td><strong>فبراير 2024</strong></td>
                                                <td>38</td>
                                                <td class="text-success fw-bold">1,180,000 د.ج</td>
                                                <td class="text-info">31,053 د.ج</td>
                                                <td><span class="badge bg-warning">+5%</span></td>
                                            </tr>
                                            <tr>
                                                <td><strong>مارس 2024</strong></td>
                                                <td>45</td>
                                                <td class="text-success fw-bold">1,450,000 د.ج</td>
                                                <td class="text-info">32,222 د.ج</td>
                                                <td><span class="badge bg-success">+22%</span></td>
                                            </tr>
                                            <tr>
                                                <td><strong>أبريل 2024</strong></td>
                                                <td>33</td>
                                                <td class="text-success fw-bold">960,000 د.ج</td>
                                                <td class="text-info">29,091 د.ج</td>
                                                <td><span class="badge bg-danger">-12%</span></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- أفضل المنتجات مبيعاً -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-trophy me-2"></i>أفضل المنتجات مبيعاً
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="list-group list-group-flush">
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-mobile-alt text-primary me-2"></i>
                                            <strong>سامسونج جالاكسي S24</strong>
                                            <small class="text-muted d-block">هواتف ذكية</small>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-bold text-success">1,240,000 د.ج</div>
                                            <small class="text-muted">48 عملية بيع</small>
                                        </div>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-laptop text-success me-2"></i>
                                            <strong>لابتوب ديل XPS 13</strong>
                                            <small class="text-muted d-block">أجهزة كمبيوتر</small>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-bold text-success">980,000 د.ج</div>
                                            <small class="text-muted">25 عملية بيع</small>
                                        </div>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-print text-warning me-2"></i>
                                            <strong>طابعة ليزر HP</strong>
                                            <small class="text-muted d-block">طابعات</small>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-bold text-success">720,000 د.ج</div>
                                            <small class="text-muted">36 عملية بيع</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- التقارير السريعة -->
                    <div class="col-lg-4 mb-4">
                        <!-- تقارير سريعة -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-file-alt me-2"></i>تقارير سريعة
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="list-group list-group-flush">
                                    <a href="#" class="list-group-item list-group-item-action">
                                        <i class="fas fa-box text-primary me-2"></i>
                                        تقرير المنتجات
                                        <small class="text-muted d-block">تحليل أداء المنتجات</small>
                                    </a>
                                    <a href="#" class="list-group-item list-group-item-action">
                                        <i class="fas fa-users text-success me-2"></i>
                                        تقرير العملاء
                                        <small class="text-muted d-block">تحليل قاعدة العملاء</small>
                                    </a>
                                    <a href="#" class="list-group-item list-group-item-action">
                                        <i class="fas fa-shopping-cart text-warning me-2"></i>
                                        تقرير المبيعات
                                        <small class="text-muted d-block">تحليل أداء المبيعات</small>
                                    </a>
                                    <a href="#" class="list-group-item list-group-item-action">
                                        <i class="fas fa-truck text-info me-2"></i>
                                        تقرير الموردين
                                        <small class="text-muted d-block">تقييم أداء الموردين</small>
                                    </a>
                                    <a href="#" class="list-group-item list-group-item-action">
                                        <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                                        تقرير المخزون المنخفض
                                        <small class="text-muted d-block">المنتجات تحت الحد الأدنى</small>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- إحصائيات إضافية -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-pie me-2"></i>نسبة المبيعات
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>هواتف ذكية</span>
                                        <span class="fw-bold">42%</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-primary" style="width: 42%"></div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>أجهزة كمبيوتر</span>
                                        <span class="fw-bold">28%</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-success" style="width: 28%"></div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>طابعات</span>
                                        <span class="fw-bold">18%</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-warning" style="width: 18%"></div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>أخرى</span>
                                        <span class="fw-bold">12%</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-info" style="width: 12%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ملخص الأداء -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-tachometer-alt me-2"></i>ملخص الأداء
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="text-center">
                                    <div class="mb-3">
                                        <div class="h4 text-success">85%</div>
                                        <small class="text-muted">تحقيق الهدف الشهري</small>
                                    </div>
                                    <div class="mb-3">
                                        <div class="h4 text-primary">+24%</div>
                                        <small class="text-muted">نمو المبيعات</small>
                                    </div>
                                    <div class="mb-3">
                                        <div class="h4 text-warning">92%</div>
                                        <small class="text-muted">رضا العملاء</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // تفعيل عناصر Bootstrap
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        // تأثيرات بسيطة للبطاقات
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.stats-card');
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px)';
                });
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
        });
    </script>
</body>
</html>