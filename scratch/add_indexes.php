<?php
include('config/connect_db.php');

// Disable execution timeout
set_time_limit(0);

echo "Adding indexes to improve search and aggregation performance...\n";

$queries = [
    // ims_product_stock_balance
    "CREATE INDEX IF NOT EXISTS idx_stock_sku ON ims_product_stock_balance (SKU_CODE(30))",
    "CREATE INDEX IF NOT EXISTS idx_stock_cat ON ims_product_stock_balance (ICCAT_NAME(100))",
    "CREATE INDEX IF NOT EXISTS idx_stock_wh ON ims_product_stock_balance (WH_CODE(30))",
    
    // ims_product_sale_cockpit
    "CREATE INDEX IF NOT EXISTS idx_sale_cp_sku ON ims_product_sale_cockpit (SKU_CODE(30))",
    "CREATE INDEX IF NOT EXISTS idx_sale_cp_year ON ims_product_sale_cockpit (DI_YEAR(10))",
    "CREATE INDEX IF NOT EXISTS idx_sale_cp_branch ON ims_product_sale_cockpit (BRANCH(30))",
    
    // ims_product_sale_sac
    "CREATE INDEX IF NOT EXISTS idx_sale_sac_sku ON ims_product_sale_sac (SKU_CODE(30))",
    "CREATE INDEX IF NOT EXISTS idx_sale_sac_year ON ims_product_sale_sac (DI_YEAR(10))",
    "CREATE INDEX IF NOT EXISTS idx_sale_sac_branch ON ims_product_sale_sac (BRANCH(30))",
    
    // ims_product_sale_btc
    "CREATE INDEX IF NOT EXISTS idx_sale_btc_sku ON ims_product_sale_btc (SKU_CODE(30))",
    "CREATE INDEX IF NOT EXISTS idx_sale_btc_year ON ims_product_sale_btc (DI_YEAR(10))",
    "CREATE INDEX IF NOT EXISTS idx_sale_btc_branch ON ims_product_sale_btc (BRANCH(30))"
];

foreach ($queries as $q) {
    echo "Running: $q\n";
    $start = microtime(true);
    try {
        $conn->exec($q);
        $duration = microtime(true) - $start;
        echo "Success (took " . round($duration, 3) . "s)\n";
    } catch (Exception $e) {
        // In older MySQL versions, CREATE INDEX IF NOT EXISTS might not be supported.
        // If it fails, try standard CREATE INDEX without IF NOT EXISTS (we will catch duplicate error if it exists)
        echo "IF NOT EXISTS failed. Trying standard CREATE INDEX...\n";
        $q_std = str_replace(" IF NOT EXISTS", "", $q);
        try {
            $conn->exec($q_std);
            $duration = microtime(true) - $start;
            echo "Success (took " . round($duration, 3) . "s)\n";
        } catch (Exception $ex) {
            echo "Failed: " . $ex->getMessage() . "\n";
        }
    }
}

echo "All index operations completed.\n";
