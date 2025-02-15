<?php
function formatNumber($number,$digit) {
    // ตรวจสอบว่าเป็นตัวเลขหรือไม่
    if (!is_numeric($number)) {
        return "Invalid input"; // กรณีไม่ใช่ตัวเลข
    }

    // เพิ่ม 0 นำหน้าหากเป็นหลักเดียว
    return str_pad($number, $digit, '0', STR_PAD_LEFT);
}
