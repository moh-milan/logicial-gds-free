<?php
// modules/dashboard/index.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit;
}

require_once '../calculations/financial_calculations.php';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم - نظام إدارة المخزون</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { 
            background: #f8f9fa; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .main-content {
            padding: 20px;
            margin-right: 280px;
            width: calc(100% - 280px);
        }
        .stats-card { 
            background: white; 
            border-radius: 10px; 
            padding: 25px; 
            text-align: center; 
            box-shadow: 0 2px 15px rgba(0,0,0,0.1); 
            border: none; 
            transition: transform 0.3s; 
            border-left: 4px solid #007bff;
            height: 100%;
        }
        .stats-card:hover { 
            transform: translateY(-5px); 
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
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
        .stats-number {
            font-size: 1.8rem;
            font-weight: bold;
            margin: 10px 0;
        }
        .stats-label {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }
        .currency { 
            font-family: Arial, sans-serif; 
            font-weight: bold; 
            color: #2c3e50; 
        }
        .profit-positive { color: #28a745; }
        .profit-negative { color: #dc3545; }
        
        @media (max-width: 768px) {
            .main-content {
                margin-right: 0;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include '../../includes/sidebar.php'; ?>
            
            <div class="main-content col-md-9">
                <!-- محتوى لوحة التحكم -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="mb-1">لوحة التحكم</h2>
                        <p class="text-muted mb-0">مرحباً <?php echo $_SESSION['user_name']; ?>! - نظرة عامة على أداء المتجر</p>
                    </div>
                    <div class="btn-group">
                        <button class="btn btn-outline-primary active" onclick="changePeriod('today')">اليوم</button>
                        <button class="btn btn-outline-primary" onclick="changePeriod('week')">أسبوع</button>
                        <button class="btn btn-outline-primary" onclick="changePeriod('month')">شهر</button>
                    </div>
                </div>

                <!-- البطاقات الحسابية -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stats-card" style="border-left-color: #28a745;">
                            <div class="stats-icon" style="background: rgba(40, 167, 69, 0.1); color: #28a745;">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div class="stats-number">
                                <span class="currency" id="total-sales"><?php echo formatCurrency(FinancialCalculations::getTotalSales('month')); ?></span>
                            </div>
                            <div class="stats-label">إجمالي المبيعات</div>
                            <small class="text-muted" id="sales-period">هذا الشهر</small>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stats-card" style="border-left-color: #dc3545;">
                            <div class="stats-icon" style="background: rgba(220, 53, 69, 0.1); color: #dc3545;">
                                <i class="fas fa-shopping-bag"></i>
                            </div>
                            <div class="stats-number">
                                <span class="currency" id="total-purchases"><?php echo formatCurrency(FinancialCalculations::getTotalPurchases('month')); ?></span>
                            </div>
                            <div class="stats-label">إجمالي المشتريات</div>
                            <small class="text-muted" id="purchases-period">هذا الشهر</small>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stats-card" style="border-left-color: #ffc107;">
                            <div class="stats-icon" style="background: rgba(255, 193, 7, 0.1); color: #ffc107;">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="stats-number">
                                <span class="currency" id="net-profit"><?php echo formatCurrency(FinancialCalculations::getNetProfit('month')); ?></span>
                            </div>
                            <div class="stats-label">صافي الربح</div>
                            <small class="text-muted" id="profit-period">هذا الشهر</small>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stats-card" style="border-left-color: #17a2b8;">
                            <div class="stats-icon" style="background: rgba(23, 162, 184, 0.1); color: #17a2b8;">
                                <i class="fas fa-percentage"></i>
                            </div>
                            <div class="stats-number">
                                <span id="profit-margin" class="<?php echo FinancialCalculations::getProfitMargin('month') >= 0 ? 'profit-positive' : 'profit-negative'; ?>">
                                    <?php echo number_format(FinancialCalculations::getProfitMargin('month'), 1); ?>%
                                </span>
                            </div>
                            <div class="stats-label">هامش الربح</div>
                            <small class="text-muted" id="margin-period">هذا الشهر</small>
                        </div>
                    </div>
                </div>

                <!-- المزيد من الإحصائيات -->
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header bg-primary text-white">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-boxes me-2"></i>إحصائيات المنتجات
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php $productsStats = FinancialCalculations::getProductsStats(); ?>
                                <div class="row text-center">
                                    <div class="col-6 mb-3">
                                        <div class="h4 text-primary"><?php echo $productsStats['total_products']; ?></div>
                                        <small class="text-muted">إجمالي المنتجات</small>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <div class="h4 text-warning"><?php echo $productsStats['low_stock']; ?></div>
                                        <small class="text-muted">منخفضة المخزون</small>
                                    </div>
                                    <div class="col-6">
                                        <div class="h4 text-danger"><?php echo $productsStats['out_of_stock']; ?></div>
                                        <small class="text-muted">منتهية المخزون</small>
                                    </div>
                                    <div class="col-6">
                                        <div class="h4 text-success"><?php echo formatCurrency($productsStats['total_value']); ?></div>
                                        <small class="text-muted">قيمة المخزون</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header bg-success text-white">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-users me-2"></i>إحصائيات العملاء
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php $clientsStats = FinancialCalculations::getClientsStats(); ?>
                                <div class="row text-center">
                                    <div class="col-6 mb-3">
                                        <div class="h4 text-primary"><?php echo $clientsStats['total_clients']; ?></div>
                                        <small class="text-muted">إجمالي العملاء</small>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <div class="h4 text-success"><?php echo $clientsStats['active_clients']; ?></div>
                                        <small class="text-muted">عملاء نشطين</small>
                                    </div>
                                    <div class="col-6">
                                        <div class="h4 text-info"><?php echo $clientsStats['new_this_month']; ?></div>
                                        <small class="text-muted">جدد هذا الشهر</small>
                                    </div>
                                    <div class="col-6">
                                        <div class="h4 text-warning"><?php echo $clientsStats['total_orders']; ?></div>
                                        <small class="text-muted">إجمالي الطلبات</small>
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
        // تحديث الإحصائيات حسب الفترة
        function changePeriod(period) {
            const periods = {
                'today': 'اليوم',
                'week': 'هذا الأسبوع', 
                'month': 'هذا الشهر'
            };
            
            // تحديث النصوص
            document.querySelectorAll('#sales-period, #purchases-period, #profit-period, #margin-period')
                .forEach(el => el.textContent = periods[period]);
            
            // في التطبيق الحقيقي، هنا نطلب البيانات من السيرفر عبر AJAX
            fetch(`../../api/calculations.php?period=${period}&type=all`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('total-sales').textContent = data.formatted_sales;
                    document.getElementById('total-purchases').textContent = data.formatted_purchases;
                    document.getElementById('net-profit').textContent = data.formatted_profit;
                    
                    const marginElement = document.getElementById('profit-margin');
                    marginElement.textContent = data.profit_margin.toFixed(1) + '%';
                    marginElement.className = data.profit_margin >= 0 ? 'profit-positive' : 'profit-negative';
                })
                .catch(error => console.error('Error:', error));
            
            // تحديث الأزرار النشطة
            document.querySelectorAll('.btn-group .btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');
        }
        
        // تأثيرات البطاقات
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