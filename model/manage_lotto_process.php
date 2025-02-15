<?php

// เชื่อมต่อฐานข้อมูล
require_once '../config/connect_lotto_db.php';

// รับค่าที่ส่งมาจาก AJAX
if ($_POST["action"] === 'SAVE_PERIOD_RESULT') {

    $period_no = $_POST['period_no'];
    $period_month = $_POST['period_month'];
    $period_year = $_POST['period_year'];
    $lotto_number_result = $_POST['lotto_number_result'];
    $lotto_type = $_POST['lotto_type'];  // รับค่า lotto_type ด้วย

    // ตรวจสอบข้อมูลในฐานข้อมูล โดยใช้ period_no, period_month, period_year และ lotto_type
    $stmt = $conn->prepare("SELECT COUNT(*) FROM ims_lotto_period WHERE period_no = :period_no AND period_month = :period_month AND period_year = :period_year AND lotto_type = :lotto_type");
    $stmt->bindParam(':period_no', $period_no, PDO::PARAM_INT);
    $stmt->bindParam(':period_month', $period_month, PDO::PARAM_INT);
    $stmt->bindParam(':period_year', $period_year, PDO::PARAM_INT);
    $stmt->bindParam(':lotto_type', $lotto_type, PDO::PARAM_STR);
    $stmt->execute();

    $rowCount = $stmt->fetchColumn();

    if ($rowCount > 0) {
        // ถ้ามีข้อมูล ให้ทำการ UPDATE
        $stmt = $conn->prepare("UPDATE ims_lotto_period SET lotto_number_result = :lotto_number_result WHERE period_no = :period_no AND period_month = :period_month AND period_year = :period_year AND lotto_type = :lotto_type");
        $stmt->bindParam(':lotto_number_result', $lotto_number_result, PDO::PARAM_STR);
        $stmt->bindParam(':period_no', $period_no, PDO::PARAM_INT);
        $stmt->bindParam(':period_month', $period_month, PDO::PARAM_INT);
        $stmt->bindParam(':period_year', $period_year, PDO::PARAM_INT);
        $stmt->bindParam(':lotto_type', $lotto_type, PDO::PARAM_STR);

        if ($stmt->execute()) {
            echo 'update_success';
        } else {
            echo 'update_error';
        }
    } else {
        // ถ้าไม่มีข้อมูล ให้ทำการ INSERT
        $stmt = $conn->prepare("INSERT INTO ims_lotto_period (period_no, period_month, period_year, lotto_number_result, lotto_type) VALUES (:period_no, :period_month, :period_year, :lotto_number_result, :lotto_type)");
        $stmt->bindParam(':period_no', $period_no, PDO::PARAM_INT);
        $stmt->bindParam(':period_month', $period_month, PDO::PARAM_INT);
        $stmt->bindParam(':period_year', $period_year, PDO::PARAM_INT);
        $stmt->bindParam(':lotto_number_result', $lotto_number_result, PDO::PARAM_STR);
        $stmt->bindParam(':lotto_type', $lotto_type, PDO::PARAM_STR);

        if ($stmt->execute()) {
            echo 'insert_success';
        } else {
            echo 'insert_error';
        }
    }

} else {
    echo 'Invalid input';
}

