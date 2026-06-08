<?php
include('config/connect_db.php');

try {
    $stmt = $conn->query("DESCRIBE ims_user");
    $cols = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $cols[] = $row['Field'] . " (" . $row['Type'] . ")";
    }
    echo "Columns: " . implode(", ", $cols) . "\n";
    
    $stmt_data = $conn->query("SELECT * FROM ims_user LIMIT 5");
    $users = $stmt_data->fetchAll(PDO::FETCH_ASSOC);
    foreach ($users as $u) {
        echo "Email: " . $u['email'] . " | Account Type: " . $u['account_type'] . " | Branch: " . ($u['branch'] ?? 'N/A') . " | WH: " . ($u['warehouse'] ?? 'N/A') . "\n";
        print_r($u);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
