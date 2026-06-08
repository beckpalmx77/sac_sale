<?php
include(__DIR__ . '/../config/connect_db.php');

$price_code_prefix = 'CP';
$sales_table = 'ims_product_sale_cockpit';
$year = '2026';
$category = ''; // no category filter

echo "=== CALCULATING AGGREGATE SUMS VIA MYSQL ===\n";

$start_time = microtime(true);

// 1. Build subquery for filtered product IDs in master
$product_filter_sql = "SELECT DISTINCT product_id FROM ims_product WHERE price_code LIKE :price_code";
$params = ['price_code' => $price_code_prefix . '%'];

if ($category !== '') {
    $product_filter_sql .= " AND product_id IN (SELECT DISTINCT SKU_CODE FROM ims_product_stock_balance WHERE ICCAT_NAME = :category)";
    $params['category'] = $category;
}

try {
    // 2. Aggregate Sales (m1 to m6)
    $sql_sales = "SELECT 
        SUM(CASE WHEN CAST(DI_MONTH AS UNSIGNED) = 1 THEN CAST(TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) as sum_m1,
        SUM(CASE WHEN CAST(DI_MONTH AS UNSIGNED) = 2 THEN CAST(TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) as sum_m2,
        SUM(CASE WHEN CAST(DI_MONTH AS UNSIGNED) = 3 THEN CAST(TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) as sum_m3,
        SUM(CASE WHEN CAST(DI_MONTH AS UNSIGNED) = 4 THEN CAST(TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) as sum_m4,
        SUM(CASE WHEN CAST(DI_MONTH AS UNSIGNED) = 5 THEN CAST(TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) as sum_m5,
        SUM(CASE WHEN CAST(DI_MONTH AS UNSIGNED) = 6 THEN CAST(TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) as sum_m6
    FROM $sales_table
    WHERE SKU_CODE IN ($product_filter_sql) AND DI_YEAR = :year";
    
    $stmt_sales = $conn->prepare($sql_sales);
    $sales_params = array_merge($params, ['year' => $year]);
    $stmt_sales->execute($sales_params);
    $sales_sums = $stmt_sales->fetch(PDO::FETCH_ASSOC);
    
    // 3. Aggregate Stock (MySQL)
    $sql_stk = "SELECT SUM(CAST(QTY AS DECIMAL(10,2))) as total_stock FROM ims_product_stock_balance WHERE SKU_CODE IN ($product_filter_sql)";
    $stmt_stk = $conn->prepare($sql_stk);
    $stmt_stk->execute($params);
    $sum_stock = $stmt_stk->fetch(PDO::FETCH_ASSOC)['total_stock'] ?? 0;
    
    // 4. Calculate AVG sales (Sum of Jan-May average)
    // Wait, the sum of AVG is actually (sum_m1 + sum_m2 + sum_m3 + sum_m4 + sum_m5) / 5!
    // Let's verify this!
    $sum_jan_may = (float)$sales_sums['sum_m1'] + (float)$sales_sums['sum_m2'] + (float)$sales_sums['sum_m3'] + (float)$sales_sums['sum_m4'] + (float)$sales_sums['sum_m5'];
    $sum_avg = $sum_jan_may / 5;
    
    // 5. Total sales (m1 to m6)
    $sum_total_sales = $sum_jan_may + (float)$sales_sums['sum_m6'];
    
    $elapsed = microtime(true) - $start_time;
    echo "Time taken: " . round($elapsed, 4) . " seconds\n";
    echo "Sales sums:\n";
    print_r($sales_sums);
    echo "Stock sum: $sum_stock\n";
    echo "Calculated Sum of AVG: $sum_avg\n";
    echo "Calculated Sum of Total Sales: $sum_total_sales\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
