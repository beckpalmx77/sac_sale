<?php
include('../config/connect_db.php');

if ($_POST["action"] === 'GET_STOCK') {

    $product_id_detail ="";
    $sql_get = "";
    $totalRecords = 0;
    $totalRecordwithFilter = 0;
    $record = 0;
    
    $product_id_detail = $_POST["product_id_detail"];

    $sql_get = " SELECT SKU_CODE,SKU_NAME,WH_CODE,WL_CODE,sum(CAST(QTY AS DECIMAL(10,2))) as  QTY FROM ims_product_stock_balance "
        . " WHERE SKU_CODE = '" . $product_id_detail . "'"
        . " GROUP BY SKU_CODE,SKU_NAME,WH_CODE,WL_CODE "
        . " HAVING sum(CAST(QTY AS DECIMAL(10,2))) > 0 ";

    $my_file = fopen("get_stock.txt", "w") or die("Unable to open file!");
    fwrite($my_file, $sql_get);
    fclose($my_file);

    ## Read value
    $draw = $_POST['draw'];
    $row = $_POST['start'];
    //$rowperpage = $_POST['length']; // Rows display per page
    //$columnIndex = $_POST['order'][0]['column']; // Column index
    //$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
    //$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
    //$searchValue = $_POST['search']['value']; // Search value

    $stmt = $conn->prepare($sql_get);
    $stmt->execute();
    $empRecords = $stmt->fetchAll();
    $data = array();

    foreach ($empRecords as $row) {
        $record++;
        if ($_POST['sub_action'] === "GET_MASTER") {
            $data[] = array(
                "SKU_CODE" => $row['SKU_CODE'],
                "SKU_NAME" => $row['SKU_NAME'],
                "WH_CODE" => $row['WH_CODE'],
                "WL_CODE" => $row['WL_CODE'],
                "WH_WL_CODE" => $row['WH_CODE'] . " : " . $row['WL_CODE'],
                "QTY" => number_format($row['QTY'], 2));
        }
    }

    $totalRecords = $record;
    $totalRecordwithFilter = $record;

    //$ret = $draw . " | " . $totalRecords . " | " . $totalRecordwithFilter ;
    //$my_file = fopen("get_stock.txt", "w") or die("Unable to open file!");
    //fwrite($my_file, " ret = " . $ret);
    //fclose($my_file);

## Response Return Value
    $response = array(
        "draw" => intval($draw),
        "iTotalRecords" => $totalRecords,
        "iTotalDisplayRecords" => $totalRecordwithFilter,
        "aaData" => $data
    );

    echo json_encode($response);


}
