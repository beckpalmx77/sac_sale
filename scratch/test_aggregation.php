<?php
include('config/connect_db.php');

$start_time = microtime(true);

$price_code_prefix = 'CP';
$year = '2026';
$category = '';
$searchValue = '';

// Build matching products subquery
$baseQuery = " price_code LIKE :price_code ";
$params = array('price_code' => $price_code_prefix . "%");

if ($category !== '') {
    $baseQuery .= " AND product_id IN (SELECT DISTINCT SKU_CODE FROM ims_product_stock_balance WHERE ICCAT_NAME = :category) ";
    $params['category'] = $category;
}

$searchQuery = $baseQuery;
if ($searchValue != '') {
    $searchQuery .= " AND (product_id LIKE :search_pid OR name_t LIKE :search_name) ";
    $params['search_pid'] = "%" . $searchValue . "%";
    $params['search_name'] = "%" . $searchValue . "%";
}

// Stats Query with Optimized Subqueries
$stats_query = "
    SELECT 
        SUM(max_sales) as sum_max,
        SUM(min_sales) as sum_min,
        SUM(avg_sales) as sum_avg,
        SUM(avg_sales - stock) as sum_needed
    FROM (
        SELECT 
            p.product_id,
            COALESCE(st.total_stock, 0) as stock,
            GREATEST(COALESCE(s.m1,0), COALESCE(s.m2,0), COALESCE(s.m3,0), COALESCE(s.m4,0), COALESCE(s.m5,0)) as max_sales,
            LEAST(COALESCE(s.m1,0), COALESCE(s.m2,0), COALESCE(s.m3,0), COALESCE(s.m4,0), COALESCE(s.m5,0)) as min_sales,
            (COALESCE(s.m1,0) + COALESCE(s.m2,0) + COALESCE(s.m3,0) + COALESCE(s.m4,0) + COALESCE(s.m5,0)) / 5.0 as avg_sales
        FROM (SELECT DISTINCT product_id FROM ims_product WHERE " . $searchQuery . ") p
        LEFT JOIN (
            SELECT SKU_CODE, SUM(CAST(QTY AS DECIMAL(10,2))) as total_stock 
            FROM ims_product_stock_balance 
            WHERE SKU_CODE IN (SELECT DISTINCT product_id FROM ims_product WHERE " . $searchQuery . ")
            GROUP BY SKU_CODE
        ) st ON p.product_id = st.SKU_CODE
        LEFT JOIN (
            SELECT 
                SKU_CODE,
                SUM(CASE WHEN CAST(DI_MONTH AS UNSIGNED) = 1 THEN CAST(TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) as m1,
                SUM(CASE WHEN CAST(DI_MONTH AS UNSIGNED) = 2 THEN CAST(TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) as m2,
                SUM(CASE WHEN CAST(DI_MONTH AS UNSIGNED) = 3 THEN CAST(TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) as m3,
                SUM(CASE WHEN CAST(DI_MONTH AS UNSIGNED) = 4 THEN CAST(TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) as m4,
                SUM(CASE WHEN CAST(DI_MONTH AS UNSIGNED) = 5 THEN CAST(TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) as m5
            FROM ims_product_sale_cockpit
            WHERE DI_YEAR = :year
              AND SKU_CODE IN (SELECT DISTINCT product_id FROM ims_product WHERE " . $searchQuery . ")
            GROUP BY SKU_CODE
        ) s ON p.product_id = s.SKU_CODE
    ) t
";

$stmt_stats = $conn->prepare($stats_query);
// Bind all params for the main search query and the two nested subqueries
$bind_params = [];
foreach ($params as $k => $v) {
    $bind_params[$k] = $v;
}
$bind_params['year'] = $year;

$stmt_stats->execute($bind_params);
$stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);
print_r($stats);

$end_time = microtime(true);
echo "Execution time: " . ($end_time - $start_time) . " seconds\n";
