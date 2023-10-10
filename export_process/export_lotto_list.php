<?php

require_once '../config/connect_lotto_db.php';

date_default_timezone_set('Asia/Bangkok');

$filename = "sac_lotto_list" . "-" . date('m/d/Y H:i:s', time()) . ".csv";

@header('Content-type: text/csv; charset=UTF-8');
@header('Content-Encoding: UTF-8');
@header("Content-Disposition: attachment; filename=" . $filename);

$select_query_daily = "  SELECT * FROM ims_lotto ORDER BY id ";

$String_Sql = $select_query_daily ;

/* $my_file = fopen("PGROUP.txt", "w") or die("Unable to open file!");
fwrite($my_file,$String_Sql);
fclose($my_file);
*/

$data = "ลำดับ,ชื่อร้าน,หมายเลขโทรศัพท์,จังหวัด,หมายเลขที่เลือก,ชื่อ Sale,วันที่บันทึก\n";

$query = $conn->prepare($String_Sql);
$query->execute();
$results = $query->fetchAll(PDO::FETCH_OBJ);

if ($query->rowCount() >= 1) {
    $line_no = 0;
    foreach ($results as $result) {
        $line_no++;
        $data .= " " . $line_no . ",";
        $data .= " " . $result->lotto_name . ",";
        $data .= " " . "[ " .$result->lotto_phone . " ]" . ",";
        $data .= " " . $result->lotto_province . ",";
        $data .= " " . $result->lotto_number . ",";
        $data .= " " . $result->sale_name . ",";
        $data .= " " . $result->create_date . "\n";

        //$data .= str_replace(",", "^", $row['WL_CODE']) . "\n";
    }

}

// $data = iconv("utf-8", "tis-620", $data);
$data = iconv("utf-8", "windows-874", $data);
echo $data;

exit();
