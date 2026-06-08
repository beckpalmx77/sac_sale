<?php
include(__DIR__ . '/../config/connect_db.php');

$query = "CREATE TABLE IF NOT EXISTS ims_product_branch_replenishment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id VARCHAR(50) NOT NULL,
    year VARCHAR(10) NOT NULL,
    channel VARCHAR(20) NOT NULL,
    branch_name VARCHAR(50) NOT NULL,
    needed_qty DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_product_branch_year_channel (product_id, year, channel, branch_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

try {
    $conn->query($query);
    echo "[SUCCESS] Table 'ims_product_branch_replenishment' created successfully or already exists.\n";
} catch (Exception $e) {
    echo "[ERROR] Failed to create table: " . $e->getMessage() . "\n";
}
