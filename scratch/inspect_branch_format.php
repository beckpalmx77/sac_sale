<?php
include(__DIR__ . '/../config/connect_db.php');

echo "=== BRANCHES IN COCKPIT ===\n";
try {
    $stmt = $conn->query("SELECT DISTINCT BRANCH FROM ims_product_sale_cockpit");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  - '" . $row['BRANCH'] . "'\n";
    }
} catch (Exception $e) { echo "Error: " . $e->getMessage() . "\n"; }

echo "\n=== BRANCHES IN SAC ===\n";
try {
    $stmt = $conn->query("SELECT DISTINCT BRANCH FROM ims_product_sale_sac");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  - '" . $row['BRANCH'] . "'\n";
    }
} catch (Exception $e) { echo "Error: " . $e->getMessage() . "\n"; }

echo "\n=== BRANCHES IN BTC ===\n";
try {
    $stmt = $conn->query("SELECT DISTINCT BRANCH FROM ims_product_sale_btc");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  - '" . $row['BRANCH'] . "'\n";
    }
} catch (Exception $e) { echo "Error: " . $e->getMessage() . "\n"; }
