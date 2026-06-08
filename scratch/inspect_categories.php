<?php
include(__DIR__ . '/../config/connect_db.php');

echo "=== DISTINCT ICCAT_NAME IN STOCK BALANCE ===\n";
try {
    $stmt = $conn->query("SELECT ICCAT_NAME, COUNT(*) as cnt FROM ims_product_stock_balance WHERE ICCAT_NAME IS NOT NULL AND ICCAT_NAME != '' GROUP BY ICCAT_NAME ORDER BY cnt DESC");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  - '{$row['ICCAT_NAME']}' (Rows: {$row['cnt']})\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== DISTINCT ICCAT_NAME IN SALES COCKPIT ===\n";
try {
    $stmt = $conn->query("SELECT ICCAT_NAME, COUNT(*) as cnt FROM ims_product_sale_cockpit WHERE ICCAT_NAME IS NOT NULL AND ICCAT_NAME != '' GROUP BY ICCAT_NAME ORDER BY cnt DESC");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  - '{$row['ICCAT_NAME']}' (Rows: {$row['cnt']})\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
