<?php

// เชื่อมต่อฐานข้อมูล
require_once '../config/connect_lotto_db.php';

// รับค่าที่ส่งมาจาก AJAX
if (isset($_POST['approve_status']) && isset($_POST['id'])) {
    $approveStatus = $_POST['approve_status'];
    $id = $_POST['id'];

    // เตรียมคำสั่ง SQL สำหรับการอัปเดตสถานะ
    $stmt = $conn->prepare("UPDATE ims_lotto SET approve_status = :approve_status WHERE id = :id");
    $stmt->bindParam(':approve_status', $approveStatus, PDO::PARAM_STR);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    // ทำการอัปเดตข้อมูล
    if ($stmt->execute()) {
        echo 'success';
    } else {
        echo 'error';
    }
} else {
    echo 'Invalid input';
}


