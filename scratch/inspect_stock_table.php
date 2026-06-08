<?php
include(__DIR__ . '/../config/connect_db.php');

echo "=== SAMPLE ROWS IN STOCK BALANCE ===\n";
try {
    $stmt = $conn->query("SELECT * FROM ims_product_stock_balance LIMIT 10");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
