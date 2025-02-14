<?php

// เชื่อมต่อฐานข้อมูล
require_once '../config/connect_lotto_db.php';

// รับค่า id ที่ส่งมาจาก AJAX
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // เตรียมคำสั่ง SQL เพื่อดึงสถานะการอนุมัติ
    $stmt = $conn->prepare("SELECT approve_status FROM ims_lotto WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    // ดึงข้อมูลสถานะการอนุมัติ
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        // ส่งสถานะกลับไปให้ฟอร์มใน popup
        echo $result['approve_status'];
    } else {
        echo 'ไม่พบข้อมูล';
    }
} else {
    echo 'Invalid ID';
}


