<?php
include(__DIR__ . '/../config/connect_db.php');

echo "=== COLUMNS OF ims_product ===\n";
try {
    $stmt = $conn->query("DESCRIBE ims_product");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  - {$row['Field']} ({$row['Type']})\n";
    }
    
    echo "\n=== SAMPLE DATA FROM ims_product ===\n";
    $stmt = $conn->query("SELECT * FROM ims_product LIMIT 3");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
