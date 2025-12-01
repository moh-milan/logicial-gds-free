<?php
// includes/sidebar.php - النسخة البسيطة

if (session_status() == PHP_SESSION_NONE) {
    @session_start();
}

if (empty($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}
?>
<!-- شريط جانبي بمسارات مطلقة مباشرة -->
<div style="background: #2c3e50; color: white; width: 280px; height: 100vh; position: fixed; right: 0; top: 0; padding: 20px; overflow-y: auto;">
    
    <!-- العنوان -->
    <div style="text-align: center; margin-bottom: 30px;">
        <h4 style="color: white; margin-bottom: 5px;">
            <i class="fas fa-warehouse"></i><br>
            <small>StockFlow الجزائر</small>
        </h4>
    </div>
    
    <!-- معلومات المستخدم -->
    <div style="text-align: center; margin-bottom: 30px; padding: 15px; background: rgba(255,255,255,0.1); border-radius: 10px;">
        <div style="background: #007bff; width: 50px; height: 50px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;">
            <span style="color: white; font-weight: bold;">
                <?php echo strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)); ?>
            </span>
        </div>
        <div style="margin-top: 10px;">
            <div style="color: white; font-weight: bold;"><?php echo $_SESSION['user_name'] ?? 'المستخدم'; ?></div>
            <small style="color: #ccc;"><?php echo $_SESSION['user_role'] ?? 'مدير'; ?></small>
        </div>
    </div>
    
    <!-- القائمة بمسارات مطلقة -->
    <div style="display: flex; flex-direction: column; gap: 5px;">
        <a href="/gestion_stock/modules/dashboard/index.php" 
           style="color: white; text-decoration: none; padding: 12px 15px; border-radius: 5px; display: flex; align-items: center; background: rgba(255,255,255,0.05);">
           <i class="fas fa-tachometer-alt me-2"></i> لوحة التحكم
        </a>
        
        <a href="/gestion_stock/modules/products/index.php" 
           style="color: white; text-decoration: none; padding: 12px 15px; border-radius: 5px; display: flex; align-items: center; background: rgba(255,255,255,0.05);">
           <i class="fas fa-boxes me-2"></i> إدارة المنتجات
        </a>
        
        <a href="/gestion_stock/modules/clients/index.php" 
           style="color: white; text-decoration: none; padding: 12px 15px; border-radius: 5px; display: flex; align-items: center; background: rgba(255,255,255,0.05);">
           <i class="fas fa-users me-2"></i> إدارة العملاء
        </a>
        
        <a href="/gestion_stock/modules/suppliers/index.php" 
           style="color: white; text-decoration: none; padding: 12px 15px; border-radius: 5px; display: flex; align-items: center; background: rgba(255,255,255,0.05);">
           <i class="fas fa-truck me-2"></i> إدارة الموردين
        </a>
        
        <a href="/gestion_stock/modules/sales/index.php" 
           style="color: white; text-decoration: none; padding: 12px 15px; border-radius: 5px; display: flex; align-items: center; background: rgba(255,255,255,0.05);">
           <i class="fas fa-shopping-cart me-2"></i> إدارة المبيعات
        </a>
        
        <a href="/gestion_stock/modules/purchases/index.php" 
           style="color: white; text-decoration: none; padding: 12px 15px; border-radius: 5px; display: flex; align-items: center; background: rgba(255,255,255,0.05);">
           <i class="fas fa-shopping-bag me-2"></i> إدارة المشتريات
        </a>
        
        <a href="/gestion_stock/modules/categories/index.php" 
           style="color: white; text-decoration: none; padding: 12px 15px; border-radius: 5px; display: flex; align-items: center; background: rgba(255,255,255,0.05);">
           <i class="fas fa-tags me-2"></i> التصنيفات
        </a>
        
        <a href="/gestion_stock/modules/reports/index.php" 
           style="color: white; text-decoration: none; padding: 12px 15px; border-radius: 5px; display: flex; align-items: center; background: rgba(255,255,255,0.05);">
           <i class="fas fa-chart-bar me-2"></i> التقارير
        </a>
    </div>
    
    <!-- تسجيل الخروج -->
    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #555;">
        <a href="/gestion_stock/modules/auth/logout.php" 
           style="color: #ff6b6b; text-decoration: none; padding: 12px 15px; border-radius: 5px; display: flex; align-items: center; background: rgba(255,255,255,0.05);">
           <i class="fas fa-sign-out-alt me-2"></i> تسجيل الخروج
        </a>
    </div>
</div>