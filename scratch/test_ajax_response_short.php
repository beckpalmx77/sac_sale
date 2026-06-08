<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
$_SESSION['alogin'] = 'admin';
$_SESSION['account_type'] = 'admin';

$_POST['action'] = 'GET_PRODUCT_ANALYSIS';
$_POST['draw'] = 1;
$_POST['start'] = 0;
$_POST['length'] = 10;
$_POST['year'] = '2026';
$_POST['channel'] = 'cockpit';
$_POST['category'] = ''; 
$_POST['search']['value'] = '';
$_POST['order'][0]['dir'] = 'asc';

chdir(__DIR__ . '/../model');

try {
    ob_start();
    include('manage_stock_analysis_process.php');
    $output = ob_get_clean();
    
    $json = json_decode($output, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "JSON_VALID: YES\n";
        echo "TotalRecords: " . $json['iTotalRecords'] . "\n";
        echo "TotalDisplayRecords: " . $json['iTotalDisplayRecords'] . "\n";
        echo "DataRowsCount: " . count($json['aaData']) . "\n";
    } else {
        echo "JSON_VALID: NO\n";
        echo "JSON_ERROR: " . json_last_error_msg() . "\n";
        echo "Output snippet: " . substr($output, 0, 500) . "\n";
    }
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
