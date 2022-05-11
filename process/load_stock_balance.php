<?php
include('../config/connect_db.php');
include('../config/connect_sqlserver.php');
include('../cond_file/query_product_stock.php');

if ($_POST["action"] === 'GET_STOCK') {

    $product_id = $_POST["product_id"];
    //$product_id = '001-A1';
    $return_arr = array();

    $sql_get = " SELECT SKU_CODE,SKU_NAME,WH_CODE,WL_CODE,sum(CAST(QTY AS DECIMAL(10,2))) as  QTY FROM ims_product_stock_balance "
        . " WHERE SKU_CODE = '" . $product_id . "'"
        . " GROUP BY SKU_CODE,SKU_NAME,WH_CODE,WL_CODE "
        . " HAVING sum(CAST(QTY AS DECIMAL(10,2))) > 0 ";
    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    $my_file = fopen("sql_getsqlmain.txt", "w") or die("Unable to open file!");
    fwrite($my_file, " sql_getdatamain = " . $sql_get);
    fclose($my_file);

    foreach ($results as $result) {
        $return_arr[] = array("SKU_CODE" => $result['SKU_CODE'],
            "SKU_NAME" => $result['SKU_NAME'],
            "WH_CODE" => $result['WH_CODE'],
            "WL_CODE" => $result['WL_CODE'],
            "QTY" => number_format($result['QTY'], 2));
    }

    $stock = json_encode($return_arr);
    file_put_contents("stock.json", $stock);

    echo json_encode($return_arr);

}
