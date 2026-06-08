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

// Stats Query with Optimized JOINs instead of IN (SELECT ...)
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
            SELECT sb.SKU_CODE, SUM(CAST(sb.QTY AS DECIMAL(10,2))) as total_stock 
            FROM ims_product_stock_balance sb
            INNER JOIN ims_product ip ON sb.SKU_CODE = ip.product_id
            WHERE ip.price_code LIKE :price_code_stock
            GROUP BY sb.SKU_CODE
        ) st ON p.product_id = st.SKU_CODE
        LEFT JOIN (
            SELECT sc.SKU_CODE,
                SUM(CASE WHEN CAST(sc.DI_MONTH AS UNSIGNED) = 1 THEN CAST(sc.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) as m1,
                SUM(CASE WHEN CAST(sc.DI_MONTH AS UNSIGNED) = 2 THEN CAST(sc.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) as m2,
                SUM(CASE WHEN CAST(sc.DI_MONTH AS UNSIGNED) = 3 THEN CAST(sc.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) as m3,
                SUM(CASE WHEN CAST(sc.DI_MONTH AS UNSIGNED) = 4 THEN CAST(sc.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) as m4,
                SUM(CASE WHEN CAST(sc.DI_MONTH AS UNSIGNED) = 5 THEN CAST(sc.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) as m5
            FROM ims_product_sale_cockpit sc
            INNER JOIN ims_product ip ON sc.SKU_CODE = ip.product_id
            WHERE sc.DI_YEAR = :year
              AND ip.price_code LIKE :price_code_sales
            GROUP BY sc.SKU_CODE
        ) s ON p.product_id = s.SKU_CODE
    ) t
";

$stmt_stats = $conn->prepare($stats_query);
$bind_params = [];
foreach ($params as $k => $v) {
    $bind_params[$k] = $v;
}
$bind_params['price_code_stock'] = $price_code_prefix . "%";
$bind_params['price_code_sales'] = $price_code_prefix . "%";
$bind_params['year'] = $year;

$stmt_stats->execute($bind_params);
$stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);
print_r($stats);

$end_time = microtime(true);
echo "Execution time: " . ($end_time - $start_time) . " seconds\n";
