<?php
include(__DIR__ . '/../config/connect_db.php');

echo "=== MATCHING PRODUCTS WITH ICCAT_NAME ===\n";
try {
    $total_products = $conn->query("SELECT COUNT(*) FROM ims_product")->fetchColumn();
    echo "Total products in master: $total_products\n";
    
    // Check match with stock balance
    $stk_match = $conn->query("
        SELECT COUNT(DISTINCT p.product_id) 
        FROM ims_product p 
        INNER JOIN ims_product_stock_balance s ON p.product_id = s.SKU_CODE 
        WHERE s.ICCAT_NAME IS NOT NULL AND s.ICCAT_NAME != ''
    ")->fetchColumn();
    echo "Matched products with stock balance: $stk_match\n";
    
    // Check match with sales cockpit
    $sale_match = $conn->query("
        SELECT COUNT(DISTINCT p.product_id) 
        FROM ims_product p 
        INNER JOIN ims_product_sale_cockpit s ON p.product_id = s.SKU_CODE 
        WHERE s.ICCAT_NAME IS NOT NULL AND s.ICCAT_NAME != ''
    ")->fetchColumn();
    echo "Matched products with sales cockpit: $sale_match\n";

    // Check match with either
    $either_match = $conn->query("
        SELECT COUNT(DISTINCT p.product_id) 
        FROM ims_product p 
        WHERE p.product_id IN (
            SELECT SKU_CODE FROM ims_product_stock_balance WHERE ICCAT_NAME IS NOT NULL AND ICCAT_NAME != ''
            UNION
            SELECT SKU_CODE FROM ims_product_sale_cockpit WHERE ICCAT_NAME IS NOT NULL AND ICCAT_NAME != ''
        )
    ")->fetchColumn();
    echo "Matched products with either table: $either_match\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
