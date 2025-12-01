<?php
// modules/calculations/financial_calculations.php
require_once '../../config/database.php';

class FinancialCalculations {
    
    public static function getTotalSales($period = 'month') {
        $data = [
            'today' => 125000,
            'week' => 850000,
            'month' => 5840000,
            'year' => 45800000
        ];
        return $data[$period] ?? 0;
    }
    
    public static function getTotalPurchases($period = 'month') {
        $data = [
            'today' => 75000,
            'week' => 520000,
            'month' => 3450000,
            'year' => 28900000
        ];
        return $data[$period] ?? 0;
    }
    
    public static function getNetProfit($period = 'month') {
        $sales = self::getTotalSales($period);
        $purchases = self::getTotalPurchases($period);
        $expenses = self::getTotalExpenses($period);
        
        return $sales - $purchases - $expenses;
    }
    
    public static function getTotalExpenses($period = 'month') {
        $data = [
            'today' => 15000,
            'week' => 95000,
            'month' => 420000,
            'year' => 3850000
        ];
        return $data[$period] ?? 0;
    }
    
    public static function getProfitMargin($period = 'month') {
        $sales = self::getTotalSales($period);
        $profit = self::getNetProfit($period);
        
        if ($sales > 0) {
            return ($profit / $sales) * 100;
        }
        return 0;
    }
    
    public static function getProductsStats() {
        return [
            'total_products' => 156,
            'low_stock' => 12,
            'out_of_stock' => 3,
            'total_value' => 12450000
        ];
    }
    
    public static function getClientsStats() {
        return [
            'total_clients' => 89,
            'active_clients' => 67,
            'new_this_month' => 12,
            'total_orders' => 245
        ];
    }
}
?>