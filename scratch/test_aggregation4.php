<?php
include('config/connect_db.php');

$start_time = microtime(true);

$price_code_prefix = 'CP';
$year = '2026';
$category = '';
$searchValue = '';

// Build matching products subqueries
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

$sq_main = str_replace(":", ":main_", $searchQuery);
$sq_st = str_replace(":", ":st_", $searchQuery);
$sq_s = str_replace(":", ":s_", $searchQuery);

$single_query = "
    SELECT 
        SUM(stock) as sum_stock,
        SUM(m1) as sum_m1,
        SUM(m2) as sum_m2,
        SUM(m3) as sum_m3,
        SUM(m4) as sum_m4,
        SUM(m5) as sum_m5,
        SUM(m6) as sum_m6,
        SUM(total_sales) as sum_total_sales,
        SUM(max_sales) as sum_max,
        SUM(min_sales) as sum_min,
        SUM(avg_sales) as sum_avg,
        SUM(avg_sales - stock) as sum_needed
    FROM (
        SELECT 
            p.product_id,
            COALESCE(st.total_stock, 0) as stock,
            COALESCE(s.m1, 0) as m1,
            COALESCE(s.m2, 0) as m2,
            COALESCE(s.m3, 0) as m3,
            COALESCE(s.m4, 0) as m4,
            COALESCE(s.m5, 0) as m5,
            COALESCE(s.m6, 0) as m6,
            (COALESCE(s.m1, 0) + COALESCE(s.m2, 0) + COALESCE(s.m3, 0) + COALESCE(s.m4, 0) + COALESCE(s.m5, 0) + COALESCE(s.m6, 0)) as total_sales,
            GREATEST(COALESCE(s.m1,0), COALESCE(s.m2,0), COALESCE(s.m3,0), COALESCE(s.m4,0), COALESCE(s.m5,0)) as max_sales,
            LEAST(COALESCE(s.m1,0), COALESCE(s.m2,0), COALESCE(s.m3,0), COALESCE(s.m4,0), COALESCE(s.m5,0)) as min_sales,
            (COALESCE(s.m1,0) + COALESCE(s.m2,0) + COALESCE(s.m3,0) + COALESCE(s.m4,0) + COALESCE(s.m5,0)) / 5.0 as avg_sales
        FROM (SELECT DISTINCT product_id FROM ims_product WHERE " . $sq_main . ") p
        LEFT JOIN (
            SELECT sb.SKU_CODE, SUM(CAST(sb.QTY AS DECIMAL(10,2))) as total_stock 
            FROM ims_product_stock_balance sb
            INNER JOIN (SELECT DISTINCT product_id FROM ims_product WHERE " . $sq_st . ") ip ON sb.SKU_CODE = ip.product_id
            GROUP BY sb.SKU_CODE
        ) st ON p.product_id = st.SKU_CODE
        LEFT JOIN (
            SELECT sc.SKU_CODE,
                SUM(CASE WHEN CAST(sc.DI_MONTH AS UNSIGNED) = 1 THEN CAST(sc.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) as m1,
                SUM(CASE WHEN CAST(sc.DI_MONTH AS UNSIGNED) = 2 THEN CAST(sc.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) as m2,
                SUM(CASE WHEN CAST(sc.DI_MONTH AS UNSIGNED) = 3 THEN CAST(sc.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) as m3,
                SUM(CASE WHEN CAST(sc.DI_MONTH AS UNSIGNED) = 4 THEN CAST(sc.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) as m4,
                SUM(CASE WHEN CAST(sc.DI_MONTH AS UNSIGNED) = 5 THEN CAST(sc.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) as m5,
                SUM(CASE WHEN CAST(sc.DI_MONTH AS UNSIGNED) = 6 THEN CAST(sc.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) as m6
            FROM ims_product_sale_cockpit sc
            INNER JOIN (SELECT DISTINCT product_id FROM ims_product WHERE " . $sq_s . ") ip ON sc.SKU_CODE = ip.product_id
            WHERE sc.DI_YEAR = :year
            GROUP BY sc.SKU_CODE
        ) s ON p.product_id = s.SKU_CODE
    ) t2
";

$stmt_stats = $conn->prepare($single_query);
$final_params = ['year' => $year];
foreach ($params as $k => $v) {
    $final_params['main_' . $k] = $v;
    $final_params['st_' . $k] = $v;
    $final_params['s_' . $k] = $v;
}

$stmt_stats->execute($final_params);
$result = $stmt_stats->fetch(PDO::FETCH_ASSOC);
print_r($result);

$end_time = microtime(true);
echo "Execution time: " . ($end_time - $start_time) . " seconds\n";
