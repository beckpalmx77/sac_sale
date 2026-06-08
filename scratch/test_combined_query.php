<?php
include('config/connect_db.php');

$start_time = microtime(true);

$price_code_prefix = 'CP';
$year = '2026';
$channel = 'cockpit';
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

$combined_query = "
    SELECT 
        -- Main stock and sales
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
        SUM(avg_sales - stock) as sum_needed,
        
        -- Branch Stocks
        SUM(stock_340) as sum_stock_340,
        SUM(stock_ratchaphruek) as sum_stock_ratchaphruek,
        SUM(stock_bangyai) as sum_stock_bangyai,
        SUM(stock_bangbon) as sum_stock_bangbon,
        
        -- Branch Sales
        SUM(sales_340) as sum_sales_340,
        SUM(sales_ratchaphruek) as sum_sales_ratchaphruek,
        SUM(sales_bangyai) as sum_sales_bangyai,
        SUM(sales_bangbon) as sum_sales_bangbon,
        
        -- Branch Needed
        SUM(needed_340) as sum_needed_340,
        SUM(needed_ratchaphruek) as sum_needed_ratchaphruek,
        SUM(needed_bangyai) as sum_needed_bangyai,
        SUM(needed_bangbon) as sum_needed_bangbon
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
            (COALESCE(s.m1,0) + COALESCE(s.m2,0) + COALESCE(s.m3,0) + COALESCE(s.m4,0) + COALESCE(s.m5,0)) / 5.0 as avg_sales,
            
            -- Branch Stocks
            COALESCE(st.stock_340, 0) as stock_340,
            COALESCE(st.stock_ratchaphruek, 0) as stock_ratchaphruek,
            COALESCE(st.stock_bangyai, 0) as stock_bangyai,
            COALESCE(st.stock_bangbon, 0) as stock_bangbon,
            
            -- Branch Sales
            COALESCE(s.sales_340, 0) as sales_340,
            COALESCE(s.sales_ratchaphruek, 0) as sales_ratchaphruek,
            COALESCE(s.sales_bangyai, 0) as sales_bangyai,
            COALESCE(s.sales_bangbon, 0) as sales_bangbon,
            
            -- Branch Needed
            COALESCE(rep_340.needed_qty, (COALESCE(s.avg_340, 0) - COALESCE(st.stock_340, 0))) as needed_340,
            COALESCE(rep_rp.needed_qty, (COALESCE(s.avg_ratchaphruek, 0) - COALESCE(st.stock_ratchaphruek, 0))) as needed_ratchaphruek,
            COALESCE(rep_by.needed_qty, (COALESCE(s.avg_bangyai, 0) - COALESCE(st.stock_bangyai, 0))) as needed_bangyai,
            COALESCE(rep_bb.needed_qty, (COALESCE(s.avg_bangbon, 0) - COALESCE(st.stock_bangbon, 0))) as needed_bangbon
        FROM (SELECT DISTINCT product_id FROM ims_product WHERE " . $sq_main . ") p
        -- Branch Stocks
        LEFT JOIN (
            SELECT 
                sb.SKU_CODE,
                SUM(CAST(sb.QTY AS DECIMAL(10,2))) as total_stock,
                SUM(CASE WHEN sb.WH_CODE IN ('T007', 'T008', 'SAC') THEN CAST(sb.QTY AS DECIMAL(10,2)) ELSE 0 END) as stock_340,
                SUM(CASE WHEN sb.WH_CODE = 'T009' OR sb.WH_CODE LIKE '%RP%' OR sb.WH_CODE LIKE '%ราชพฤกษ์%' THEN CAST(sb.QTY AS DECIMAL(10,2)) ELSE 0 END) as stock_ratchaphruek,
                SUM(CASE WHEN sb.WH_CODE = 'T010' OR sb.WH_CODE LIKE '%BY%' OR sb.WH_CODE LIKE '%บางใหญ่%' THEN CAST(sb.QTY AS DECIMAL(10,2)) ELSE 0 END) as stock_bangyai,
                SUM(CASE WHEN sb.WH_CODE = 'T011' OR sb.WH_CODE LIKE '%BB%' OR sb.WH_CODE LIKE '%บางบอน%' THEN CAST(sb.QTY AS DECIMAL(10,2)) ELSE 0 END) as stock_bangbon
            FROM ims_product_stock_balance sb
            INNER JOIN (SELECT DISTINCT product_id FROM ims_product WHERE " . $sq_st . ") ip ON sb.SKU_CODE = ip.product_id
            GROUP BY sb.SKU_CODE
        ) st ON p.product_id = st.SKU_CODE
        -- Branch Sales (Jan-Jun) and Avg Sales (Jan-May)
        LEFT JOIN (
            SELECT 
                sc.SKU_CODE,
                SUM(CASE WHEN CAST(sc.DI_MONTH AS UNSIGNED) = 1 THEN CAST(sc.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) as m1,
                SUM(CASE WHEN CAST(sc.DI_MONTH AS UNSIGNED) = 2 THEN CAST(sc.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) as m2,
                SUM(CASE WHEN CAST(sc.DI_MONTH AS UNSIGNED) = 3 THEN CAST(sc.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) as m3,
                SUM(CASE WHEN CAST(sc.DI_MONTH AS UNSIGNED) = 4 THEN CAST(sc.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) as m4,
                SUM(CASE WHEN CAST(sc.DI_MONTH AS UNSIGNED) = 5 THEN CAST(sc.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) as m5,
                SUM(CASE WHEN CAST(sc.DI_MONTH AS UNSIGNED) = 6 THEN CAST(sc.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) as m6,
                
                SUM(CASE WHEN sc.BRANCH IN ('CP-340', '340', 'SAC') THEN CAST(sc.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) as sales_340,
                SUM(CASE WHEN sc.BRANCH IN ('CP-RP', 'RQ') OR sc.BRANCH LIKE '%ราชพฤกษ์%' THEN CAST(sc.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) as sales_ratchaphruek,
                SUM(CASE WHEN sc.BRANCH = 'CP-BY' OR sc.BRANCH LIKE '%บางใหญ่%' THEN CAST(sc.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) as sales_bangyai,
                SUM(CASE WHEN sc.BRANCH = 'CP-BB' OR sc.BRANCH LIKE '%บางบอน%' THEN CAST(sc.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) as sales_bangbon,
                
                SUM(CASE WHEN (sc.BRANCH IN ('CP-340', '340', 'SAC') AND CAST(sc.DI_MONTH AS UNSIGNED) BETWEEN 1 AND 5) THEN CAST(sc.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) / 5.0 as avg_340,
                SUM(CASE WHEN ((sc.BRANCH IN ('CP-RP', 'RQ') OR sc.BRANCH LIKE '%ราชพฤกษ์%') AND CAST(sc.DI_MONTH AS UNSIGNED) BETWEEN 1 AND 5) THEN CAST(sc.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) / 5.0 as avg_ratchaphruek,
                SUM(CASE WHEN ((sc.BRANCH = 'CP-BY' OR sc.BRANCH LIKE '%บางใหญ่%') AND CAST(sc.DI_MONTH AS UNSIGNED) BETWEEN 1 AND 5) THEN CAST(sc.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) / 5.0 as avg_bangyai,
                SUM(CASE WHEN ((sc.BRANCH = 'CP-BB' OR sc.BRANCH LIKE '%บางบอน%') AND CAST(sc.DI_MONTH AS UNSIGNED) BETWEEN 1 AND 5) THEN CAST(sc.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) / 5.0 as avg_bangbon
            FROM ims_product_sale_cockpit sc
            INNER JOIN (SELECT DISTINCT product_id FROM ims_product WHERE " . $sq_s . ") ip ON sc.SKU_CODE = ip.product_id
            WHERE sc.DI_YEAR = :year
            GROUP BY sc.SKU_CODE
        ) s ON p.product_id = s.SKU_CODE
        -- Overrides
        LEFT JOIN ims_product_branch_replenishment rep_340 ON p.product_id = rep_340.product_id AND rep_340.year = :year_rep1 AND rep_340.channel = :channel_rep1 AND rep_340.branch_name = '340'
        LEFT JOIN ims_product_branch_replenishment rep_rp ON p.product_id = rep_rp.product_id AND rep_rp.year = :year_rep2 AND rep_rp.channel = :channel_rep2 AND rep_rp.branch_name = 'ราชพฤกษ์'
        LEFT JOIN ims_product_branch_replenishment rep_by ON p.product_id = rep_by.product_id AND rep_by.year = :year_rep3 AND rep_by.channel = :channel_rep3 AND rep_by.branch_name = 'บางใหญ่'
        LEFT JOIN ims_product_branch_replenishment rep_bb ON p.product_id = rep_bb.product_id AND rep_bb.year = :year_rep4 AND rep_bb.channel = :channel_rep4 AND rep_bb.branch_name = 'บางบอน'
    ) t_combined
";

$stmt = $conn->prepare($combined_query);
$final_params = [
    'year' => $year,
    'year_rep1' => $year, 'channel_rep1' => $channel,
    'year_rep2' => $year, 'channel_rep2' => $channel,
    'year_rep3' => $year, 'channel_rep3' => $channel,
    'year_rep4' => $year, 'channel_rep4' => $channel
];
foreach ($params as $k => $v) {
    $final_params['main_' . $k] = $v;
    $final_params['st_' . $k] = $v;
    $final_params['s_' . $k] = $v;
}

$stmt->execute($final_params);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
print_r($result);

$end_time = microtime(true);
echo "Execution time: " . ($end_time - $start_time) . " seconds\n";
