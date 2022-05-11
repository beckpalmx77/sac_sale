<?php
include('../config/connect_db.php');
include('../config/connect_sqlserver.php');
include('../cond_file/query_product_stock.php');

if ($_POST["action"] === 'GET_DATA') {

    $product_id = $_POST["product_id"];

    //$product_id = 'DS2157016-HT603';

    $sql_main = $select_query_stock . $sql_cond_stock . " AND SKU_CODE = '" . $product_id . "' " . $sql_group_stock . $sql_order_stock;

    //$my_file = fopen("sql_getsql.txt", "w") or die("Unable to open file!");
    //fwrite($my_file,$sql_main);
    //fclose($my_file);
    $create_date = date("Y-m-d H:i:s");
    $update_date = date("Y-m-d H:i:s");

    $statement = $conn_sqlsvr->query($sql_main);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);
    foreach ($results as $result) {
        $sql_find = "SELECT * FROM ims_product_stock_balance WHERE SKU_CODE = '" . $result["SKU_CODE"] . "'"
            . " AND ICCAT_CODE = '" . $result["ICCAT_CODE"] . "'"
            . " AND DI_DATE = '" . $result["DI_DATE"] . "'"
            . " AND WL_CODE = '" . $result["WL_CODE"] . "'"
            . " AND WH_CODE = '" . $result["WH_CODE"] . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            $sql = " UPDATE ims_product_stock_balance SET ICCAT_NAME=:ICCAT_NAME,SKU_NAME=:SKU_NAME,UTQ_QTY=:UTQ_QTY,QTY=:QTY
                ,WL_NAME=:WL_NAME,WH_NAME=:WH_NAME,update_date=:update_date WHERE SKU_CODE = :SKU_CODE AND DI_DATE = :DI_DATE
                 AND ICCAT_CODE=:ICCAT_CODE AND WL_CODE=:WL_CODE AND WH_CODE=:WH_CODE ";
            $query = $conn->prepare($sql);
            $query->bindParam(':ICCAT_NAME', $result["ICCAT_NAME"], PDO::PARAM_STR);
            $query->bindParam(':SKU_NAME', $result["SKU_NAME"], PDO::PARAM_STR);
            $query->bindParam(':UTQ_QTY', $result["UTQ_QTY"], PDO::PARAM_STR);
            $query->bindParam(':QTY', $result["QTY"], PDO::PARAM_STR);
            $query->bindParam(':WL_NAME', $result["WL_NAME"], PDO::PARAM_STR);
            $query->bindParam(':WH_NAME', $result["WH_NAME"], PDO::PARAM_STR);
            $query->bindParam(':update_date', $update_date, PDO::PARAM_STR);
            $query->bindParam(':SKU_CODE', $result["SKU_CODE"], PDO::PARAM_STR);
            $query->bindParam(':DI_DATE', $result["DI_DATE"], PDO::PARAM_STR);
            $query->bindParam(':ICCAT_CODE', $result["ICCAT_CODE"], PDO::PARAM_STR);
            $query->bindParam(':WL_CODE', $result["WL_CODE"], PDO::PARAM_STR);
            $query->bindParam(':WH_CODE', $result["WH_CODE"], PDO::PARAM_STR);
            $query->execute();
        } else {
            $sql = "INSERT INTO ims_product_stock_balance(ICCAT_CODE,ICCAT_NAME,DI_DATE,SKU_CODE,SKU_NAME,WH_CODE,WL_CODE,UTQ_QTY,QTY,WH_NAME,WL_NAME,create_date) 
                VALUES (:ICCAT_CODE,:ICCAT_NAME,:DI_DATE,:SKU_CODE,:SKU_NAME,:WH_CODE,:WL_CODE,:UTQ_QTY,:QTY,:WH_NAME,:WL_NAME,:create_date)";
            $query = $conn->prepare($sql);
            $query->bindParam(':ICCAT_CODE', $result["ICCAT_CODE"], PDO::PARAM_STR);
            $query->bindParam(':ICCAT_NAME', $result["ICCAT_NAME"], PDO::PARAM_STR);
            $query->bindParam(':DI_DATE', $result["DI_DATE"], PDO::PARAM_STR);
            $query->bindParam(':SKU_CODE', $result["SKU_CODE"], PDO::PARAM_STR);
            $query->bindParam(':SKU_NAME', $result["SKU_NAME"], PDO::PARAM_STR);
            $query->bindParam(':WH_CODE', $result["WH_CODE"], PDO::PARAM_STR);
            $query->bindParam(':WL_CODE', $result["WL_CODE"], PDO::PARAM_STR);
            $query->bindParam(':UTQ_QTY', $result["UTQ_QTY"], PDO::PARAM_STR);
            $query->bindParam(':QTY', $result["QTY"], PDO::PARAM_STR);
            $query->bindParam(':WH_NAME', $result["WH_NAME"], PDO::PARAM_STR);
            $query->bindParam(':WL_NAME', $result["WL_NAME"], PDO::PARAM_STR);
            $query->bindParam(':create_date', $create_date, PDO::PARAM_STR);
            $query->execute();
        }

    }
}

