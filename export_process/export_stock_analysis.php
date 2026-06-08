<?php
session_start();
error_reporting(0);

if (empty($_SESSION['alogin'])) {
    header("Location: ../index.php");
    exit();
}

include('../config/connect_db.php');
include('../config/connect_sqlserver.php');

require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

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

$year = $_GET['year'] ?? '2026';
$channel = $_GET['channel'] ?? 'cockpit';
$category = $_GET['category'] ?? '';
$searchValue = $_GET['search'] ?? '';

// Force channel based on user account type
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

// Build query
$baseQuery = " price_code LIKE :price_code ";
$searchArray = array('price_code' => $price_code_prefix . "%");

if ($category !== '') {
    $baseQuery .= " AND product_id IN (SELECT DISTINCT SKU_CODE FROM ims_product_stock_balance WHERE ICCAT_NAME = :category) ";
    $searchArray['category'] = $category;
}

$searchQuery = $baseQuery;
if ($searchValue != '') {
    $searchQuery .= " AND (product_id LIKE :search_pid OR name_t LIKE :search_name) ";
    $searchArray['search_pid'] = "%" . $searchValue . "%";
    $searchArray['search_name'] = "%" . $searchValue . "%";
}

// Fetch all matching products
$sql_getdata = "SELECT MIN(id) as id, product_id, name_t FROM ims_product WHERE " . $searchQuery . " GROUP BY product_id ORDER BY product_id ASC";
$stmt = $conn->prepare($sql_getdata);
$stmt->execute($searchArray);
$empRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- PRE-LOOKUP DATA FOR OPTIMAL PERFORMANCE ---

// 1. Stock balances
$sql_stock_all = "SELECT SKU_CODE, SUM(CAST(QTY AS DECIMAL(10,2))) as total_stock 
                  FROM ims_product_stock_balance 
                  WHERE SKU_CODE IN (SELECT DISTINCT product_id FROM ims_product WHERE " . $searchQuery . ")
                  GROUP BY SKU_CODE";
$stmt_stock_all = $conn->prepare($sql_stock_all);
$stmt_stock_all->execute($searchArray);
$stock_lookup = [];
while ($st_row = $stmt_stock_all->fetch(PDO::FETCH_ASSOC)) {
    $stock_lookup[trim($st_row['SKU_CODE'])] = (float)$st_row['total_stock'];
}

// 2. Monthly sales
$sql_sales_all = "SELECT SKU_CODE, CAST(DI_MONTH AS UNSIGNED) as month_num, SUM(CAST(TRD_QTY AS DECIMAL(10,2))) as qty 
                  FROM " . $sales_table . " 
                  WHERE SKU_CODE IN (SELECT DISTINCT product_id FROM ims_product WHERE " . $searchQuery . ")
                    AND DI_YEAR = :year
                  GROUP BY SKU_CODE, DI_MONTH";
$sales_all_params = $searchArray;
$sales_all_params['year'] = $year;
$stmt_sales_all = $conn->prepare($sql_sales_all);
$stmt_sales_all->execute($sales_all_params);
$sales_lookup = [];
while ($sl_row = $stmt_sales_all->fetch(PDO::FETCH_ASSOC)) {
    $sku = trim($sl_row['SKU_CODE']);
    $m = (int)$sl_row['month_num'];
    $qty = (float)$sl_row['qty'];
    if (!isset($sales_lookup[$sku])) {
        $sales_lookup[$sku] = array_fill(1, 12, 0.0);
    }
    $sales_lookup[$sku][$m] = $qty;
}

// 3. Branch Stock
$sql_b_stock = "SELECT SKU_CODE, WH_CODE, SUM(CAST(QTY AS DECIMAL(10,2))) as total_stock 
                FROM ims_product_stock_balance 
                WHERE SKU_CODE IN (SELECT DISTINCT product_id FROM ims_product WHERE " . $searchQuery . ")
                GROUP BY SKU_CODE, WH_CODE";
$stmt_b_stock = $conn->prepare($sql_b_stock);
$stmt_b_stock->execute($searchArray);
$branch_stock_lookup = [];
$branches = ['340', 'ราชพฤกษ์', 'บางใหญ่', 'บางบอน'];
while ($bs_row = $stmt_b_stock->fetch(PDO::FETCH_ASSOC)) {
    $sku = trim($bs_row['SKU_CODE']);
    $wh = trim($bs_row['WH_CODE']);
    $std_b = get_standard_branch_from_stock($wh);
    if ($std_b !== null) {
        if (!isset($branch_stock_lookup[$sku])) {
            $branch_stock_lookup[$sku] = array_fill_keys($branches, 0.0);
        }
        $branch_stock_lookup[$sku][$std_b] += (float)$bs_row['total_stock'];
    }
}

// 4. Branch Sales
$sql_b_sales = "SELECT SKU_CODE, BRANCH, CAST(DI_MONTH AS UNSIGNED) as month_num, SUM(CAST(TRD_QTY AS DECIMAL(10,2))) as qty 
                FROM " . $sales_table . " 
                WHERE SKU_CODE IN (SELECT DISTINCT product_id FROM ims_product WHERE " . $searchQuery . ")
                  AND DI_YEAR = :year
                GROUP BY SKU_CODE, BRANCH, DI_MONTH";
$stmt_b_sales = $conn->prepare($sql_b_sales);
$stmt_b_sales->execute($sales_all_params);
$branch_sales_lookup = [];
while ($bssl_row = $stmt_b_sales->fetch(PDO::FETCH_ASSOC)) {
    $sku = trim($bssl_row['SKU_CODE']);
    $br = trim($bssl_row['BRANCH']);
    $m = (int)$bssl_row['month_num'];
    $qty = (float)$bssl_row['qty'];
    
    $std_b = get_standard_branch_from_sales($br);
    if ($std_b !== null) {
        if (!isset($branch_sales_lookup[$sku])) {
            $branch_sales_lookup[$sku] = [];
            foreach ($branches as $b) {
                $branch_sales_lookup[$sku][$b] = [
                    'tot' => 0.0,
                    'months' => array_fill(1, 12, 0.0)
                ];
            }
        }
        if ($m >= 1 && $m <= 6) { // Jan-Jun sales
            $branch_sales_lookup[$sku][$std_b]['tot'] += $qty;
        }
        if ($m >= 1 && $m <= 12) {
            $branch_sales_lookup[$sku][$std_b]['months'][$m] += $qty;
        }
    }
}

// 5. Replenishment overrides
$sql_saved = "SELECT product_id, branch_name, needed_qty 
              FROM ims_product_branch_replenishment 
              WHERE year = :year AND channel = :channel";
$stmt_saved = $conn->prepare($sql_saved);
$stmt_saved->execute(['year' => $year, 'channel' => $channel]);
$saved_lookup = [];
while ($sv_row = $stmt_saved->fetch(PDO::FETCH_ASSOC)) {
    $sku = trim($sv_row['product_id']);
    $b = trim($sv_row['branch_name']);
    $saved_lookup[$sku][$b] = (float)$sv_row['needed_qty'];
}

// --- CONSTRUCT THE SPREADSHEET ---

$data = [];
$total_stock_all = 0.0;
$total_m_sales = array_fill(1, 6, 0.0);
$total_total_sales = 0.0;
$total_max = 0.0;
$total_min = 0.0;
$total_avg = 0.0;
$total_needed = 0.0;

$total_b_sales = array_fill_keys($branches, 0.0);
$total_b_stock = array_fill_keys($branches, 0.0);
$total_b_needed = array_fill_keys($branches, 0.0);

foreach ($empRecords as $rowItem) {
    $p_code = trim($rowItem['product_id']);
    $p_name = $rowItem['name_t'];
    
    $stock = $stock_lookup[$p_code] ?? 0.0;
    $b_monthly_12 = $sales_lookup[$p_code] ?? array_fill(1, 12, 0.0);
    
    $jan_may = array_slice($b_monthly_12, 0, 5, true);
    $max_sales = count($jan_may) ? max($jan_may) : 0.0;
    $min_sales = count($jan_may) ? min($jan_may) : 0.0;
    $avg_sales = count($jan_may) ? (array_sum($jan_may) / 5) : 0.0;
    $needed = $avg_sales - $stock;
    $total_sales = array_sum(array_slice($b_monthly_12, 0, 6, true)); // Jan to Jun total
    
    $b_stock = $branch_stock_lookup[$p_code] ?? array_fill_keys($branches, 0.0);
    
    $b_sales_tot = array_fill_keys($branches, 0.0);
    $b_sales_avg = array_fill_keys($branches, 0.0);
    if (isset($branch_sales_lookup[$p_code])) {
        foreach ($branches as $b) {
            $b_sales_tot[$b] = $branch_sales_lookup[$p_code][$b]['tot'];
            $jan_may_b = array_slice($branch_sales_lookup[$p_code][$b]['months'], 0, 5, true);
            $b_sales_avg[$b] = array_sum($jan_may_b) / 5;
        }
    }
    
    $b_needed = [];
    foreach ($branches as $b) {
        if (isset($saved_lookup[$p_code][$b])) {
            $b_needed[$b] = $saved_lookup[$p_code][$b];
        } else {
            $b_needed[$b] = $b_sales_avg[$b] - $b_stock[$b];
        }
    }
    
    $data[] = [
        'product_id' => $p_code,
        'name_t' => $p_name,
        'm1' => $b_monthly_12[1],
        'm2' => $b_monthly_12[2],
        'm3' => $b_monthly_12[3],
        'm4' => $b_monthly_12[4],
        'm5' => $b_monthly_12[5],
        'm6' => $b_monthly_12[6],
        'total_sales' => $total_sales,
        'stock' => $stock,
        'max' => $max_sales,
        'min' => $min_sales,
        'avg' => $avg_sales,
        'needed' => $needed,
        'sales_340' => $b_sales_tot['340'],
        'sales_ratchaphruek' => $b_sales_tot['ราชพฤกษ์'],
        'sales_bangyai' => $b_sales_tot['บางใหญ่'],
        'sales_bangbon' => $b_sales_tot['บางบอน'],
        'stock_340' => $b_stock['340'],
        'stock_ratchaphruek' => $b_stock['ราชพฤกษ์'],
        'stock_bangyai' => $b_stock['บางใหญ่'],
        'stock_bangbon' => $b_stock['บางบอน'],
        'needed_340' => $b_needed['340'],
        'needed_ratchaphruek' => $b_needed['ราชพฤกษ์'],
        'needed_bangyai' => $b_needed['บางใหญ่'],
        'needed_bangbon' => $b_needed['บางบอน']
    ];
    
    // Accumulate totals
    $total_stock_all += $stock;
    for ($i = 1; $i <= 6; $i++) {
        $total_m_sales[$i] += $b_monthly_12[$i];
    }
    $total_total_sales += $total_sales;
    $total_max += $max_sales;
    $total_min += $min_sales;
    $total_avg += $avg_sales;
    $total_needed += $needed;
    
    foreach ($branches as $b) {
        $total_b_sales[$b] += $b_sales_tot[$b];
        $total_b_stock[$b] += $b_stock[$b];
        $total_b_needed[$b] += $b_needed[$b];
    }
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('วิเคราะห์สต็อกสินค้า');

// 1. Write Header Row 1 (Merged categories)
$sheet->setCellValue('A1', 'ข้อมูลสินค้า');
$sheet->mergeCells('A1:B1');

$sheet->setCellValue('C1', 'ยอดขายรายเดือน');
$sheet->mergeCells('C1:H1');

$sheet->setCellValue('I1', 'ผลรวม');

$sheet->setCellValue('J1', 'สถิติ & สต็อกรวม');
$sheet->mergeCells('J1:N1');

$sheet->setCellValue('O1', 'ยอดขายรายสาขา');
$sheet->mergeCells('O1:R1');

$sheet->setCellValue('S1', 'STOCK รายสาขา');
$sheet->mergeCells('S1:V1');

$sheet->setCellValue('W1', 'ความต้องการเพิ่มรายสาขา');
$sheet->mergeCells('W1:Z1');

// Style Row 1
$styleRow1 = [
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F3C88']]
];
$sheet->getStyle('A1:Z1')->applyFromArray($styleRow1);

// 2. Write Header Row 2 (Sub headers)
$headers = [
    'รหัสสินค้า', 'ชื่อรายละเอียดสินค้า',
    'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.',
    'ผลรวมทั้งหมด', 'STOCK', 'MAX', 'MIN', 'AVG', 'ส่งไปเพิ่ม',
    '340', 'ราชพฤกษ์', 'บางใหญ่', 'บางบอน',
    '340', 'ราชพฤกษ์', 'บางใหญ่', 'บางบอน',
    '340', 'ราชพฤกษ์', 'บางใหญ่', 'บางบอน'
];

$colIdx = 1;
foreach ($headers as $h) {
    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
    $sheet->setCellValue($colLetter . '2', $h);
    $colIdx++;
}

$styleRow2 = [
    'font' => ['bold' => true, 'color' => ['rgb' => '486581']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8F9FA']]
];
$sheet->getStyle('A2:Z2')->applyFromArray($styleRow2);

// 3. Write Row 3 (Grand Totals Row)
$sheet->setCellValue('A3', 'ผลรวมทั้งหมด');
$sheet->mergeCells('A3:B3');

$sheet->setCellValue('C3', $total_m_sales[1]);
$sheet->setCellValue('D3', $total_m_sales[2]);
$sheet->setCellValue('E3', $total_m_sales[3]);
$sheet->setCellValue('F3', $total_m_sales[4]);
$sheet->setCellValue('G3', $total_m_sales[5]);
$sheet->setCellValue('H3', $total_m_sales[6]);
$sheet->setCellValue('I3', $total_total_sales);
$sheet->setCellValue('J3', $total_stock_all);
$sheet->setCellValue('K3', $total_max);
$sheet->setCellValue('L3', $total_min);
$sheet->setCellValue('M3', $total_avg);
$sheet->setCellValue('N3', $total_needed);

$sheet->setCellValue('O3', $total_b_sales['340']);
$sheet->setCellValue('P3', $total_b_sales['ราชพฤกษ์']);
$sheet->setCellValue('Q3', $total_b_sales['บางใหญ่']);
$sheet->setCellValue('R3', $total_b_sales['บางบอน']);

$sheet->setCellValue('S3', $total_b_stock['340']);
$sheet->setCellValue('T3', $total_b_stock['ราชพฤกษ์']);
$sheet->setCellValue('U3', $total_b_stock['บางใหญ่']);
$sheet->setCellValue('V3', $total_b_stock['บางบอน']);

$sheet->setCellValue('W3', $total_b_needed['340']);
$sheet->setCellValue('X3', $total_b_needed['ราชพฤกษ์']);
$sheet->setCellValue('Y3', $total_b_needed['บางใหญ่']);
$sheet->setCellValue('Z3', $total_b_needed['บางบอน']);

$styleRow3 = [
    'font' => ['bold' => true, 'color' => ['rgb' => '000000']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EEF2F7']]
];
$sheet->getStyle('A3:Z3')->applyFromArray($styleRow3);

// 4. Write Data Rows
$rowNum = 4;
foreach ($data as $item) {
    $sheet->setCellValue('A' . $rowNum, $item['product_id']);
    $sheet->setCellValue('B' . $rowNum, $item['name_t']);
    $sheet->setCellValue('C' . $rowNum, $item['m1']);
    $sheet->setCellValue('D' . $rowNum, $item['m2']);
    $sheet->setCellValue('E' . $rowNum, $item['m3']);
    $sheet->setCellValue('F' . $rowNum, $item['m4']);
    $sheet->setCellValue('G' . $rowNum, $item['m5']);
    $sheet->setCellValue('H' . $rowNum, $item['m6']);
    $sheet->setCellValue('I' . $rowNum, $item['total_sales']);
    $sheet->setCellValue('J' . $rowNum, $item['stock']);
    $sheet->setCellValue('K' . $rowNum, $item['max']);
    $sheet->setCellValue('L' . $rowNum, $item['min']);
    $sheet->setCellValue('M' . $rowNum, $item['avg']);
    $sheet->setCellValue('N' . $rowNum, $item['needed']);
    
    // Branch sales
    $sheet->setCellValue('O' . $rowNum, $item['sales_340']);
    $sheet->setCellValue('P' . $rowNum, $item['sales_ratchaphruek']);
    $sheet->setCellValue('Q' . $rowNum, $item['sales_bangyai']);
    $sheet->setCellValue('R' . $rowNum, $item['sales_bangbon']);
    
    // Branch stocks
    $sheet->setCellValue('S' . $rowNum, $item['stock_340']);
    $sheet->setCellValue('T' . $rowNum, $item['stock_ratchaphruek']);
    $sheet->setCellValue('U' . $rowNum, $item['stock_bangyai']);
    $sheet->setCellValue('V' . $rowNum, $item['stock_bangbon']);
    
    // Branch needed
    $sheet->setCellValue('W' . $rowNum, $item['needed_340']);
    $sheet->setCellValue('X' . $rowNum, $item['needed_ratchaphruek']);
    $sheet->setCellValue('Y' . $rowNum, $item['needed_bangyai']);
    $sheet->setCellValue('Z' . $rowNum, $item['needed_bangbon']);
    
    $rowNum++;
}

// 5. Apply formatting (Borders & AutoColumn Width & Numeric format)
$borderStyle = [
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => 'D0D0D0']
        ]
    ]
];
$sheet->getStyle('A1:Z' . ($rowNum - 1))->applyFromArray($borderStyle);

// Set number formats for numeric columns (C to Z)
for ($col = 3; $col <= 26; $col++) {
    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
    $sheet->getStyle($colLetter . '3:' . $colLetter . ($rowNum - 1))
          ->getNumberFormat()
          ->setFormatCode('#,##0.00');
}

// Enable auto-size column widths
foreach (range('A', 'Z') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Output file headers for Excel download
$fileName = "Stock_Analysis_" . $channel . "_" . $year . "_" . date('Ymd_His') . ".xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit();
