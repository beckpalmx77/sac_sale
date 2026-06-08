<?php
include(__DIR__ . '/../config/connect_db.php');

echo "=== WAREHOUSES IN STOCK BALANCE ===\n";
try {
    $stmt = $conn->query("SELECT DISTINCT WH_CODE FROM ims_product_stock_balance");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  - '" . $row['WH_CODE'] . "'\n";
    }
} catch (Exception $e) { echo "Error: " . $e->getMessage() . "\n"; }
