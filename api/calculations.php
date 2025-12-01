<?php
// api/calculations.php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../modules/calculations/financial_calculations.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $period = $_GET['period'] ?? 'month';
    $type = $_GET['type'] ?? 'all';
    
    $response = [];
    
    switch($type) {
        case 'sales':
            $response['total_sales'] = FinancialCalculations::getTotalSales($period);
            $response['formatted_sales'] = formatCurrency($response['total_sales']);
            break;
            
        case 'purchases':
            $response['total_purchases'] = FinancialCalculations::getTotalPurchases($period);
            $response['formatted_purchases'] = formatCurrency($response['total_purchases']);
            break;
            
        case 'profit':
            $response['net_profit'] = FinancialCalculations::getNetProfit($period);
            $response['formatted_profit'] = formatCurrency($response['net_profit']);
            $response['profit_margin'] = FinancialCalculations::getProfitMargin($period);
            break;
            
        case 'all':
        default:
            $response = [
                'total_sales' => FinancialCalculations::getTotalSales($period),
                'total_purchases' => FinancialCalculations::getTotalPurchases($period),
                'net_profit' => FinancialCalculations::getNetProfit($period),
                'profit_margin' => FinancialCalculations::getProfitMargin($period),
                'formatted_sales' => formatCurrency(FinancialCalculations::getTotalSales($period)),
                'formatted_purchases' => formatCurrency(FinancialCalculations::getTotalPurchases($period)),
                'formatted_profit' => formatCurrency(FinancialCalculations::getNetProfit($period)),
                'period' => $period
            ];
            break;
    }
    
    echo json_encode($response);
}
?>