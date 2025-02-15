<?php
include('../config/connect_lotto_db.php');

// ตรวจสอบว่าเป็น POST request หรือไม่
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // รับค่าที่ส่งมาจากฟอร์ม
    $period_no = $_POST['period_no'];
    $period_month = $_POST['period_month'];
    $period_year = $_POST['period_year'];

    // สร้างคำสั่ง SQL สำหรับดึงข้อมูลจาก ims_lotto_period
    $sql = "SELECT * FROM ims_lotto_period WHERE period_no = :period_no AND period_month = :period_month AND period_year = :period_year ORDER BY lotto_type DESC";

    // เตรียมคำสั่ง SQL
    $stmt = $conn->prepare($sql);

    // ผูกค่าพารามิเตอร์กับตัวแปร
    $stmt->bindParam(':period_no', $period_no, PDO::PARAM_STR);
    $stmt->bindParam(':period_month', $period_month, PDO::PARAM_STR);
    $stmt->bindParam(':period_year', $period_year, PDO::PARAM_STR);

    // ทำการ execute คำสั่ง SQL
    $stmt->execute();

    // ตรวจสอบผลลัพธ์
    if ($stmt->rowCount() > 0) {
        // กำหนดชื่อไฟล์ Excel
        $fileName = "lotto_results_" . date('Y-m-d_H-i-s') . ".csv";

        // กำหนด header สำหรับการดาวน์โหลดไฟล์
        @header('Content-type: text/csv; charset=UTF-8');
        @header('Content-Encoding: UTF-8');
        @header("Content-Disposition: attachment; filename=" . $fileName);

        // สร้างไฟล์ CSV ด้วยฟังก์ชัน fputcsv()
        $output = fopen('php://output', 'w');

        // เขียนชื่อหัวข้อของแต่ละคอลัมน์
        fputcsv($output, array('ลำดับ', 'ประเภทรางวัล', 'ผลรางวัล', 'ผู้ถูกรางวัล', 'หมายเลขที่เลือก', 'เบอร์โทร', 'จังหวัด'));

        // แสดงผลรางวัล
        $line_no = 1;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // ตรวจสอบประเภทของรางวัล
            if ($row['lotto_type'] == 2) {
                $lotto_type_desc = "เลขท้าย 3 ตัว ";
                $where = "WHERE lotto_number = " . $row['lotto_number_result'];
            } else {
                $lotto_type_desc = "เลขท้าย 2 ตัว ";
                $lotto_number_result_last2 = substr($row['lotto_number_result'], -2);
                $where = "WHERE lotto_number LIKE '%" . $lotto_number_result_last2 . "'"; // เปรียบเทียบเลขท้าย 2 ตัว
            }

            // ค้นหาผู้ถูกรางวัลจากตาราง ims_lotto
            $sql_str = "SELECT * FROM ims_lotto " . $where;
            $stmt_lotto = $conn->prepare($sql_str);
            $stmt_lotto->execute();

            if ($stmt_lotto->rowCount() > 0) {
                // ถ้ามีผู้ถูกรางวัล
                while ($lotto = $stmt_lotto->fetch(PDO::FETCH_ASSOC)) {
                    // เขียนข้อมูลในไฟล์ CSV
                    fputcsv($output, array(
                        $line_no++,
                        $lotto_type_desc,
                        $row['lotto_number_result'],
                        $lotto['lotto_name'],
                        $lotto['lotto_number'],
                        $lotto['lotto_phone'],
                        $lotto['lotto_province']
                    ));
                }
            }
        }

        // ปิดไฟล์
        fclose($output);
    } else {
        echo "ไม่พบผลรางวัลที่ค้นหา";
    }
}
?>
