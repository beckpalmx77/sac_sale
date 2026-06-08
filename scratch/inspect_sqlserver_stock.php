<?php
include(__DIR__ . '/../config/connect_sqlserver.php');

echo "=== SQL SERVER STOCK VIEW: v_stock_movement ===\n";
if (isset($conn_sqlsvr) && $conn_sqlsvr) {
    try {
        $stmt = $conn_sqlsvr->query("SELECT TOP 10 * FROM v_stock_movement");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $idx => $row) {
            echo "Row $idx:\n";
            foreach ($row as $k => $v) {
                echo "  $k: $v\n";
            }
        }
        
        echo "\n=== DISTINCT WAREHOUSES IN SQL SERVER ===\n";
        $stmt = $conn_sqlsvr->query("SELECT DISTINCT WH_CODE FROM v_stock_movement");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "  - '" . $row['WH_CODE'] . "'\n";
        }
    } catch (Exception $e) {
        echo "Error querying SQL Server: " . $e->getMessage() . "\n";
    }
} else {
    echo "SQL Server connection not active.\n";
}
