<?php
include('config/connect_db.php');

try {
    $stmt = $conn->query("SELECT * FROM ims_permission");
    $perms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($perms as $p) {
        print_r($p);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
