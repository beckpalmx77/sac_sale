<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/connect_sqlserver.php');

// Helper functions for mapping DB codes to standard branch names
function get_standard_branch_from_sales($db_branch) {
    $db_branch = strtoupper(trim($db_branch));
    if ($db_branch === 'CP-340' || $db_branch === '340' || $db_branch === 'SAC') {
        return '340';
    }
    if ($db_branch === 'CP-RP' || $db_branch === 'RQ' || stripos($db_branch, 'ราชพฤกษ์') !== false) {
        return 'ราชพฤกษ์';
    }
    if ($db_branch === 'CP-BY' || stripos($db_branch, 'บางใหญ่') !== false) {
        return 'บางใหญ่';
    }
    if ($db_branch === 'CP-BB' || stripos($db_branch, 'บางบอน') !== false) {
        return 'บางบอน';
    }
    return null;
}

function get_standard_branch_from_stock($wh_code) {
    $wh_code = strtoupper(trim($wh_code));
    if ($wh_code === 'T007' || $wh_code === 'T008' || $wh_code === 'SAC' || stripos($wh_code, '340') !== false) {
        return '340';
    }
    if ($wh_code === 'T009' || stripos($wh_code, 'RP') !== false || stripos($wh_code, 'ราชพฤกษ์') !== false) {
        return 'ราชพฤกษ์';
    }
    if ($wh_code === 'T010' || stripos($wh_code, 'BY') !== false || stripos($wh_code, 'บางใหญ่') !== false) {
        return 'บางใหญ่';
    }
    if ($wh_code === 'T011' || stripos($wh_code, 'BB') !== false || stripos($wh_code, 'บางบอน') !== false) {
        return 'บางบอน';
    }
    return null;
}

// Auto create the replenishment override table if not exists
try {
    $conn->query("CREATE TABLE IF NOT EXISTS ims_product_branch_replenishment (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id VARCHAR(50) NOT NULL,
        year VARCHAR(10) NOT NULL,
        channel VARCHAR(20) NOT NULL,
        branch_name VARCHAR(50) NOT NULL,
        needed_qty DECIMAL(10, 2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uq_product_branch_year_channel (product_id, year, channel, branch_name)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
} catch (Exception $e) {}

if ($_POST["action"] === 'GET_PRODUCT_ANALYSIS') {
    $draw = $_POST['draw'];
    $row = $_POST['start'];
    $rowperpage = $_POST['length'];
    $columnSortOrder = $_POST['order'][0]['dir'] ?? 'asc';
    $searchValue = $_POST['search']['value'] ?? '';
    
    $year = $_POST['year'] ?? '2026';
    $channel = $_POST['channel'] ?? 'cockpit'; // 'cockpit', 'sac', or 'btc'
    
    // Force channel if account_type is cockpit or btc
    if ($_SESSION['account_type'] === 'cockpit') {
        $channel = 'cockpit';
    } elseif ($_SESSION['account_type'] === 'btc') {
        $channel = 'btc';
    }
    
    if ($channel === 'sac') {
        $sales_table = 'ims_product_sale_sac';
        $price_code_prefix = 'S';
    } elseif ($channel === 'btc') {
        $sales_table = 'ims_product_sale_btc';
        $price_code_prefix = 'BTC';
    } else {
        $sales_table = 'ims_product_sale_cockpit';
        $price_code_prefix = 'CP';
    }

    $searchQuery = " price_code LIKE :price_code ";
    $searchArray = array('price_code' => $price_code_prefix . "%");

    if ($searchValue != '') {
        $searchQuery .= " AND (product_id LIKE :product_id OR name_t LIKE :name_t) ";
        $searchArray['product_id'] = "%" . $searchValue . "%";
        $searchArray['name_t'] = "%" . $searchValue . "%";
    }

    // Total records count
    $stmt = $conn->prepare("SELECT COUNT(DISTINCT product_id) AS allcount FROM ims_product WHERE price_code LIKE :price_code");
    $stmt->execute(['price_code' => $price_code_prefix . "%"]);
    $totalRecords = $stmt->fetch()['allcount'];

    // Filtered records count
    $stmt = $conn->prepare("SELECT COUNT(DISTINCT product_id) AS allcount FROM ims_product WHERE " . $searchQuery);
    $stmt->execute($searchArray);
    $totalRecordwithFilter = $stmt->fetch()['allcount'];

    // Fetch records (grouped by product_id to prevent duplicates)
    $sql_getdata = "SELECT MIN(id) as id, product_id, name_t, unit_name, price FROM ims_product WHERE " . $searchQuery
        . " GROUP BY product_id ORDER BY product_id " . $columnSortOrder . " LIMIT :limit, :offset";

    $stmt = $conn->prepare($sql_getdata);
    foreach ($searchArray as $key => $search) {
        $stmt->bindValue(':' . $key, $search, PDO::PARAM_STR);
    }
    $stmt->bindValue(':limit', (int)$row, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$rowperpage, PDO::PARAM_INT);
    $stmt->execute();
    $empRecords = $stmt->fetchAll();

    $data = array();
    
    // Check if SQL Server connection is active for live stock query
    $sqlserver_active = isset($conn_sqlsvr) && $conn_sqlsvr;

    foreach ($empRecords as $rowItem) {
        $p_code = trim($rowItem['product_id']);
        $p_name = $rowItem['name_t'];
        
        // 1. Fetch STOCK
        $stock = 0;
        if ($sqlserver_active) {
            try {
                // Fetch live from SQL Server view v_stock_movement
                $sql_stk = "SELECT SUM(CAST(QTY AS DECIMAL(10,2))) as total_stock FROM v_stock_movement WHERE SKU_CODE = :sku";
                $stmt_stk = $conn_sqlsvr->prepare($sql_stk);
                $stmt_stk->execute(['sku' => $p_code]);
                $stock = $stmt_stk->fetch(PDO::FETCH_ASSOC)['total_stock'] ?? 0;
            } catch (Exception $e) {
                // Fallback to MySQL if SQL Server query fails
                $sql_stk = "SELECT SUM(CAST(QTY AS DECIMAL(10,2))) as total_stock FROM ims_product_stock_balance WHERE SKU_CODE = :sku";
                $stmt_stk = $conn->prepare($sql_stk);
                $stmt_stk->execute(['sku' => $p_code]);
                $stock = $stmt_stk->fetch(PDO::FETCH_ASSOC)['total_stock'] ?? 0;
            }
        } else {
            // Fetch from MySQL stock balance table
            $sql_stk = "SELECT SUM(CAST(QTY AS DECIMAL(10,2))) as total_stock FROM ims_product_stock_balance WHERE SKU_CODE = :sku";
            $stmt_stk = $conn->prepare($sql_stk);
            $stmt_stk->execute(['sku' => $p_code]);
            $stock = $stmt_stk->fetch(PDO::FETCH_ASSOC)['total_stock'] ?? 0;
        }

        // 2. Fetch monthly sales overall (Jan to Jun)
        $b_monthly_12 = array_fill(1, 12, 0.0);
        try {
            $sql_sales_all = "SELECT CAST(DI_MONTH AS UNSIGNED) as month_num, SUM(CAST(TRD_QTY AS DECIMAL(10,2))) as qty 
                              FROM " . $sales_table . " 
                              WHERE SKU_CODE = :sku AND DI_YEAR = :year AND CAST(DI_MONTH AS UNSIGNED) BETWEEN 1 AND 12
                              GROUP BY DI_MONTH";
            $stmt_sales_all = $conn->prepare($sql_sales_all);
            $stmt_sales_all->execute(['sku' => $p_code, 'year' => $year]);
            while ($s_row = $stmt_sales_all->fetch(PDO::FETCH_ASSOC)) {
                $m = (int)$s_row['month_num'];
                if ($m >= 1 && $m <= 12) {
                    $b_monthly_12[$m] = (float)$s_row['qty'];
                }
            }
        } catch (Exception $e) {}

        $jan_may = array_slice($b_monthly_12, 0, 5, true);
        $max_sales = count($jan_may) ? max($jan_may) : 0.0;
        $min_sales = count($jan_may) ? min($jan_may) : 0.0;
        $avg_sales = count($jan_may) ? (array_sum($jan_may) / 5) : 0.0;
        $needed = $avg_sales - $stock;
        $total_sales = array_sum(array_slice($b_monthly_12, 0, 6, true)); // Jan to Jun total

        // 3. Branches breakdown (340, ราชพฤกษ์, บางใหญ่, บางบอน)
        $branches = ['340', 'ราชพฤกษ์', 'บางใหญ่', 'บางบอน'];
        
        // Branch Stock
        $branch_stock = array_fill_keys($branches, 0.0);
        if ($sqlserver_active) {
            try {
                $sql_b_stk = "SELECT WH_CODE, SUM(CAST(QTY AS DECIMAL(10,2))) as total_stock 
                              FROM v_stock_movement 
                              WHERE SKU_CODE = :sku 
                              GROUP BY WH_CODE";
                $stmt_b_stk = $conn_sqlsvr->prepare($sql_b_stk);
                $stmt_b_stk->execute(['sku' => $p_code]);
                while ($bs_row = $stmt_b_stk->fetch(PDO::FETCH_ASSOC)) {
                    $wh = trim($bs_row['WH_CODE']);
                    $std_b = get_standard_branch_from_stock($wh);
                    if ($std_b !== null) {
                        $branch_stock[$std_b] += (float)$bs_row['total_stock'];
                    }
                }
            } catch (Exception $e) {}
        } else {
            try {
                $sql_b_stk = "SELECT WH_CODE, SUM(CAST(QTY AS DECIMAL(10,2))) as total_stock 
                              FROM ims_product_stock_balance 
                              WHERE SKU_CODE = :sku 
                              GROUP BY WH_CODE";
                $stmt_b_stk = $conn->prepare($sql_b_stk);
                $stmt_b_stk->execute(['sku' => $p_code]);
                while ($bs_row = $stmt_b_stk->fetch(PDO::FETCH_ASSOC)) {
                    $wh = trim($bs_row['WH_CODE']);
                    $std_b = get_standard_branch_from_stock($wh);
                    if ($std_b !== null) {
                        $branch_stock[$std_b] += (float)$bs_row['total_stock'];
                    }
                }
            } catch (Exception $e) {}
        }

        // Branch Sales & Avg
        $branch_sales_tot = array_fill_keys($branches, 0.0);
        $branch_sales_avg = array_fill_keys($branches, 0.0);
        try {
            $sql_b_sales = "SELECT BRANCH, CAST(DI_MONTH AS UNSIGNED) as month_num, SUM(CAST(TRD_QTY AS DECIMAL(10,2))) as qty 
                            FROM " . $sales_table . " 
                            WHERE SKU_CODE = :sku AND DI_YEAR = :year 
                            GROUP BY BRANCH, DI_MONTH";
            $stmt_b_sales = $conn->prepare($sql_b_sales);
            $stmt_b_sales->execute(['sku' => $p_code, 'year' => $year]);
            
            $temp_b_m = [];
            foreach ($branches as $b) { $temp_b_m[$b] = array_fill(1, 5, 0.0); }
            
            while ($bs_row = $stmt_b_sales->fetch(PDO::FETCH_ASSOC)) {
                $br = trim($bs_row['BRANCH']);
                $m = (int)$bs_row['month_num'];
                $qty = (float)$bs_row['qty'];
                
                $std_b = get_standard_branch_from_sales($br);
                if ($std_b !== null) {
                    if ($m >= 1 && $m <= 6) { // Jan to Jun total
                        $branch_sales_tot[$std_b] += $qty;
                    }
                    if ($m >= 1 && $m <= 5) { // Jan to May average
                        $temp_b_m[$std_b][$m] += $qty;
                    }
                }
            }
            
            foreach ($branches as $b) {
                $branch_sales_avg[$b] = array_sum($temp_b_m[$b]) / 5;
            }
        } catch (Exception $e) {}

        // Load saved replenishment overrides
        $saved_needed = array_fill_keys($branches, null);
        try {
            $sql_saved = "SELECT branch_name, needed_qty FROM ims_product_branch_replenishment 
                          WHERE product_id = :sku AND year = :year AND channel = :channel";
            $stmt_saved = $conn->prepare($sql_saved);
            $stmt_saved->execute(['sku' => $p_code, 'year' => $year, 'channel' => $channel]);
            while ($sv_row = $stmt_saved->fetch(PDO::FETCH_ASSOC)) {
                $saved_needed[trim($sv_row['branch_name'])] = (float)$sv_row['needed_qty'];
            }
        } catch (Exception $e) {}

        // Branch replenishment needed
        $branch_needed = [];
        foreach ($branches as $b) {
            if ($saved_needed[$b] !== null) {
                $branch_needed[$b] = $saved_needed[$b];
            } else {
                $branch_needed[$b] = $branch_sales_avg[$b] - $branch_stock[$b];
            }
        }

        $data[] = array(
            "product_id" => $p_code,
            "name_t" => $p_name,
            "m1" => number_format($b_monthly_12[1], 2),
            "m2" => number_format($b_monthly_12[2], 2),
            "m3" => number_format($b_monthly_12[3], 2),
            "m4" => number_format($b_monthly_12[4], 2),
            "m5" => number_format($b_monthly_12[5], 2),
            "m6" => number_format($b_monthly_12[6], 2),
            "total_sales" => number_format($total_sales, 2),
            "stock" => number_format($stock, 2),
            "max" => number_format($max_sales, 2),
            "min" => number_format($min_sales, 2),
            "avg" => number_format($avg_sales, 2),
            "needed" => number_format($needed, 2),
            "needed_raw" => $needed,
            // Branch sales
            "sales_340" => number_format($branch_sales_tot['340'], 2),
            "sales_ratchaphruek" => number_format($branch_sales_tot['ราชพฤกษ์'], 2),
            "sales_bangyai" => number_format($branch_sales_tot['บางใหญ่'], 2),
            "sales_bangbon" => number_format($branch_sales_tot['บางบอน'], 2),
            // Branch stocks
            "stock_340" => number_format($branch_stock['340'], 2),
            "stock_ratchaphruek" => number_format($branch_stock['ราชพฤกษ์'], 2),
            "stock_bangyai" => number_format($branch_stock['บางใหญ่'], 2),
            "stock_bangbon" => number_format($branch_stock['บางบอน'], 2),
            // Branch needed
            "needed_340" => number_format($branch_needed['340'], 2),
            "needed_340_raw" => $branch_needed['340'],
            "needed_ratchaphruek" => number_format($branch_needed['ราชพฤกษ์'], 2),
            "needed_ratchaphruek_raw" => $branch_needed['ราชพฤกษ์'],
            "needed_bangyai" => number_format($branch_needed['บางใหญ่'], 2),
            "needed_bangyai_raw" => $branch_needed['บางใหญ่'],
            "needed_bangbon" => number_format($branch_needed['บางบอน'], 2),
            "needed_bangbon_raw" => $branch_needed['บางบอน'],
            "detail" => "<button type='button' name='detail' id='" . $rowItem['id'] . "' data-sku='" . $p_code . "' data-name='" . htmlspecialchars($p_name, ENT_QUOTES) . "' class='btn btn-info btn-xs show-branch-detail' data-toggle='tooltip' title='ดูรายละเอียดรายสาขา'>รายละเอียดสาขา</button>"
        );
    }

    $response = array(
        "draw" => intval($draw),
        "iTotalRecords" => $totalRecords,
        "iTotalDisplayRecords" => $totalRecordwithFilter,
        "aaData" => $data
    );

    echo json_encode($response);
    exit();
}

if ($_POST["action"] === 'GET_BRANCH_DETAILS') {
    $p_code = trim($_POST["sku"]);
    $year = $_POST["year"] ?? '2026';
    $channel = $_POST["channel"] ?? 'cockpit';
    if ($_SESSION['account_type'] === 'cockpit') {
        $channel = 'cockpit';
    } elseif ($_SESSION['account_type'] === 'btc') {
        $channel = 'btc';
    }
    
    if ($channel === 'sac') {
        $sales_table = 'ims_product_sale_sac';
    } elseif ($channel === 'btc') {
        $sales_table = 'ims_product_sale_btc';
    } else {
        $sales_table = 'ims_product_sale_cockpit';
    }
    
    // We analyze 4 main branches: 340, ราชพฤกษ์, บางใหญ่, บางบอน
    $branches = ['340', 'ราชพฤกษ์', 'บางใหญ่', 'บางบอน'];
    
    // Load saved replenishment overrides
    $saved_needed = array_fill_keys($branches, null);
    try {
        $sql_saved = "SELECT branch_name, needed_qty FROM ims_product_branch_replenishment 
                      WHERE product_id = :sku AND year = :year AND channel = :channel";
        $stmt_saved = $conn->prepare($sql_saved);
        $stmt_saved->execute(['sku' => $p_code, 'year' => $year, 'channel' => $channel]);
        while ($sv_row = $stmt_saved->fetch(PDO::FETCH_ASSOC)) {
            $saved_needed[trim($sv_row['branch_name'])] = (float)$sv_row['needed_qty'];
        }
    } catch (Exception $e) {}
    
    // Standardize branch matching
    $branch_data = [];
    
    // Check if SQL Server connection is active
    $sqlserver_active = isset($conn_sqlsvr) && $conn_sqlsvr;

    // 1. Calculate Mapped Branch Stocks (Grouped from all WHs for this SKU)
    $b_stock_mapped = array_fill_keys($branches, 0.0);
    if ($sqlserver_active) {
        try {
            $sql_stk = "SELECT WH_CODE, SUM(CAST(QTY AS DECIMAL(10,2))) as total_stock FROM v_stock_movement WHERE SKU_CODE = :sku GROUP BY WH_CODE";
            $stmt_stk = $conn_sqlsvr->prepare($sql_stk);
            $stmt_stk->execute(['sku' => $p_code]);
            while ($stk_row = $stmt_stk->fetch(PDO::FETCH_ASSOC)) {
                $wh = trim($stk_row['WH_CODE']);
                $std_b = get_standard_branch_from_stock($wh);
                if ($std_b !== null) {
                    $b_stock_mapped[$std_b] += (float)$stk_row['total_stock'];
                }
            }
        } catch (Exception $e) {
            // MySQL fallback
            $sql_stk = "SELECT WH_CODE, SUM(CAST(QTY AS DECIMAL(10,2))) as total_stock FROM ims_product_stock_balance WHERE SKU_CODE = :sku GROUP BY WH_CODE";
            $stmt_stk = $conn->prepare($sql_stk);
            $stmt_stk->execute(['sku' => $p_code]);
            while ($stk_row = $stmt_stk->fetch(PDO::FETCH_ASSOC)) {
                $wh = trim($stk_row['WH_CODE']);
                $std_b = get_standard_branch_from_stock($wh);
                if ($std_b !== null) {
                    $b_stock_mapped[$std_b] += (float)$stk_row['total_stock'];
                }
            }
        }
    } else {
        $sql_stk = "SELECT WH_CODE, SUM(CAST(QTY AS DECIMAL(10,2))) as total_stock FROM ims_product_stock_balance WHERE SKU_CODE = :sku GROUP BY WH_CODE";
        $stmt_stk = $conn->prepare($sql_stk);
        $stmt_stk->execute(['sku' => $p_code]);
        while ($stk_row = $stmt_stk->fetch(PDO::FETCH_ASSOC)) {
            $wh = trim($stk_row['WH_CODE']);
            $std_b = get_standard_branch_from_stock($wh);
            if ($std_b !== null) {
                $b_stock_mapped[$std_b] += (float)$stk_row['total_stock'];
            }
        }
    }

    // 2. Calculate Mapped Branch Monthly Sales (Grouped from all BRANCH/Month combinations)
    $b_sales_monthly = [];
    foreach ($branches as $b) {
        $b_sales_monthly[$b] = array_fill(1, 12, 0.0);
    }
    
    try {
        $sql_b_sales = "SELECT BRANCH, CAST(DI_MONTH AS UNSIGNED) as month_num, SUM(CAST(TRD_QTY AS DECIMAL(10,2))) as qty 
                        FROM " . $sales_table . " 
                        WHERE SKU_CODE = :sku AND DI_YEAR = :year 
                        GROUP BY BRANCH, DI_MONTH";
        $stmt_b_sales = $conn->prepare($sql_b_sales);
        $stmt_b_sales->execute(['sku' => $p_code, 'year' => $year]);
        while ($s_row = $stmt_b_sales->fetch(PDO::FETCH_ASSOC)) {
            $br = trim($s_row['BRANCH']);
            $m = (int)$s_row['month_num'];
            $qty = (float)$s_row['qty'];
            
            $std_b = get_standard_branch_from_sales($br);
            if ($std_b !== null && $m >= 1 && $m <= 12) {
                $b_sales_monthly[$std_b][$m] += $qty;
            }
        }
    } catch (Exception $e) {}

    foreach ($branches as $b) {
        $b_stock = $b_stock_mapped[$b];
        $b_monthly_12 = $b_sales_monthly[$b];
        
        $b_sales_total = array_sum($b_monthly_12);
        $b_avg_sales = array_sum(array_slice($b_monthly_12, 0, 5, true)) / 5; // Jan to May average
        
        $is_overridden = ($saved_needed[$b] !== null);
        $b_needed = $is_overridden ? $saved_needed[$b] : ($b_avg_sales - $b_stock);

        $branch_data[] = [
            'branch_name' => $b,
            'sales' => number_format($b_sales_total, 2),
            'stock' => number_format($b_stock, 2),
            'avg' => number_format($b_avg_sales, 2),
            'needed' => number_format($b_needed, 2),
            'needed_raw' => $b_needed,
            'calculated_needed_raw' => $b_avg_sales - $b_stock,
            'is_overridden' => $is_overridden,
            'months' => [
                'm1' => number_format($b_monthly_12[1], 2),
                'm2' => number_format($b_monthly_12[2], 2),
                'm3' => number_format($b_monthly_12[3], 2),
                'm4' => number_format($b_monthly_12[4], 2),
                'm5' => number_format($b_monthly_12[5], 2),
                'm6' => number_format($b_monthly_12[6], 2),
                'm7' => number_format($b_monthly_12[7], 2),
                'm8' => number_format($b_monthly_12[8], 2),
                'm9' => number_format($b_monthly_12[9], 2),
                'm10' => number_format($b_monthly_12[10], 2),
                'm11' => number_format($b_monthly_12[11], 2),
                'm12' => number_format($b_monthly_12[12], 2)
            ]
        ];
    }
    
    echo json_encode($branch_data);
    exit();
}

if ($_POST["action"] === 'SAVE_BRANCH_REPLENISHMENT') {
    $p_code = trim($_POST["sku"]);
    $year = $_POST["year"] ?? '2026';
    $channel = $_POST["channel"] ?? 'cockpit';
    if ($_SESSION['account_type'] === 'cockpit') {
        $channel = 'cockpit';
    } elseif ($_SESSION['account_type'] === 'btc') {
        $channel = 'btc';
    }
    
    $branch_values = $_POST["branch_values"] ?? []; // Array of branch_name => needed_qty
    
    try {
        $conn->beginTransaction();
        
        $sql_upsert = "INSERT INTO ims_product_branch_replenishment (product_id, year, channel, branch_name, needed_qty) 
                       VALUES (:sku, :year, :channel, :branch_name, :needed_qty) 
                       ON DUPLICATE KEY UPDATE needed_qty = :needed_qty_update, updated_at = CURRENT_TIMESTAMP";
        $stmt = $conn->prepare($sql_upsert);
        
        foreach ($branch_values as $b_name => $qty_val) {
            $qty = floatval($qty_val);
            $stmt->execute([
                'sku' => $p_code,
                'year' => $year,
                'channel' => $channel,
                'branch_name' => $b_name,
                'needed_qty' => $qty,
                'needed_qty_update' => $qty
            ]);
        }
        
        $conn->commit();
        echo json_encode(['status' => 'success', 'message' => 'บันทึกความต้องการเพิ่มสำเร็จ']);
    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
    }
    exit();
}
