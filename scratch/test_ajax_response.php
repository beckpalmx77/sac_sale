<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Mock session variables to simulate admin login
session_start();
$_SESSION['alogin'] = 'admin';
$_SESSION['account_type'] = 'admin';

// Set POST variables
$_POST['action'] = 'GET_PRODUCT_ANALYSIS';
$_POST['draw'] = 1;
$_POST['start'] = 0;
$_POST['length'] = 10;
$_POST['year'] = '2026';
$_POST['channel'] = 'cockpit';
$_POST['category'] = ''; // default empty category (All)
$_POST['search']['value'] = '';
$_POST['order'][0]['dir'] = 'asc';

echo "=== SIMULATING GET_PRODUCT_ANALYSIS ===\n";

// Change directory to the model folder to make relative paths match web server environment
chdir(__DIR__ . '/../model');

try {
    ob_start();
    try {
        include('manage_stock_analysis_process.php');
    } catch (Throwable $t) {
        ob_end_clean();
        echo "Caught Throwable: " . $t->getMessage() . "\n";
        echo "Trace:\n" . $t->getTraceAsString() . "\n";
        exit(1);
    }
    $output = ob_get_clean();
    
    echo "Raw response length: " . strlen($output) . "\n";
    echo "First 1000 chars of response:\n";
    echo substr($output, 0, 1000) . "\n";
    
    // Check if it's valid JSON
    $json = json_decode($output, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "\n[SUCCESS] Response is valid JSON. Total display records: " . $json['iTotalDisplayRecords'] . "\n";
        if (count($json['aaData']) > 0) {
            echo "First product code in result: " . $json['aaData'][0]['product_id'] . "\n";
        } else {
            echo "[WARNING] aaData is empty!\n";
        }
    } else {
        echo "\n[ERROR] Response is not valid JSON! Error: " . json_last_error_msg() . "\n";
        echo "Response output was:\n" . $output . "\n";
    }
} catch (Exception $e) {
    echo "Outer Exception: " . $e->getMessage() . "\n";
}
