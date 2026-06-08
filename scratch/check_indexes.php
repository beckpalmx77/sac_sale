<?php
include('config/connect_db.php');

$tables = [
    'ims_product',
    'ims_product_stock_balance',
    'ims_product_sale_cockpit',
    'ims_product_sale_sac',
    'ims_product_sale_btc'
];

foreach ($tables as $t) {
    echo "=== INDEXES FOR: $t ===\n";
    try {
        $stmt = $conn->query("SHOW INDEX FROM $t");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "  Key: " . $row['Key_name'] . " | Column: " . $row['Column_name'] . " | Non_unique: " . $row['Non_unique'] . "\n";
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
    echo "\n";
}
