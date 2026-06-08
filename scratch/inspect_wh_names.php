<?php
include(__DIR__ . '/../config/connect_db.php');

echo "=== WAREHOUSE NAMES MAPPING ===\n";
try {
    $stmt = $conn->query("SELECT WH_CODE, WH_NAME, COUNT(*) as cnt FROM ims_product_stock_balance GROUP BY WH_CODE, WH_NAME");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  Code: '{$row['WH_CODE']}' -> Name: '{$row['WH_NAME']}' (Rows: {$row['cnt']})\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
