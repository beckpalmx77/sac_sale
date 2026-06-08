<?php
include(__DIR__ . '/../config/connect_db.php');

$required_tables = [
    'ims_product',
    'ims_product_sale_cockpit',
    'ims_product_sale_sac',
    'ims_product_sale_btc',
    'ims_product_stock_balance',
    'ims_product_branch_replenishment'
];

echo "=== CHECKING MYSQL TABLES ===\n";
try {
    $stmt = $conn->query("SHOW TABLES");
    $existing_tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($required_tables as $table) {
        if (in_array($table, $existing_tables)) {
            echo "[OK] Table '$table' exists.\n";
            // Print columns for the replenishment table
            if ($table === 'ims_product_branch_replenishment') {
                echo "     Columns of $table:\n";
                $cols = $conn->query("DESCRIBE $table")->fetchAll(PDO::FETCH_ASSOC);
                foreach ($cols as $col) {
                    echo "      - {$col['Field']} ({$col['Type']}) " . ($col['Key'] ? "Key: {$col['Key']}" : "") . "\n";
                }
            }
        } else {
            echo "[MISSING] Table '$table' is NOT present in the database.\n";
        }
    }
} catch (Exception $e) {
    echo "Error querying tables: " . $e->getMessage() . "\n";
}
