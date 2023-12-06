<?php

include('../config/connect_lotto_db.php');

$table_name = $_POST["table_name"];

if ($_POST["action"] === 'GET_DATA') {

    $id = $_POST["id"];

    $return_arr = array();

    $sql_get = "SELECT * FROM ims_lotto WHERE id = " . $id;
    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "lotto_name" => $result['lotto_name'],
            "lotto_phone" => $result['lotto_phone'],
            "lotto_province" => $result['lotto_province'],
            "sale_name" => $result['sale_name'],
            "create_date" => $result['create_date'],
            "client_ip_address" => $result['client_ip_address'],
            "lotto_number" => $result['lotto_number']);
    }

    //file_put_contents('sql_data_arr.txt', print_r($return_arr, true));

    echo json_encode($return_arr);

}

if ($_POST["action"] === 'CHECK_NUMBER_DATA') {
    $cond = $_POST["cond"];
    $return_arr = array();
    $sql_get = "SELECT count(*) as record_counts  FROM " . $table_name . " " . $cond;

    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);
    foreach ($results as $result) {
        $record = $result['record_counts'];
    }

    /*
        $my_file = fopen("sql_getdata.txt", "w") or die("Unable to open file!");
        fwrite($my_file, " sql_get = " . $sql_get . " Count = " . $record);
        fclose($my_file);
    */

    echo $record;

}

if ($_POST["action"] === 'SAVE_DATA') {

    $ins = 3;
    $sql = "";
    $lotto_name = $_POST["lotto_name"];
    //$lotto_phone = $_POST["lotto_phone"];
    $lotto_phone = str_replace("-", "", $_POST["lotto_phone"]);
    $lotto_province = $_POST["lotto_province"];
    $sale_name = $_POST["sale_name"];

    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        //ip from share internet
        $client_ip_address = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        //ip pass from proxy
        $client_ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $client_ip_address = $_SERVER['REMOTE_ADDR'];
    }

    //$lotto_number = $_POST["lotto_number"];

    $lotto_number = sprintf("%03d", $_POST["lotto_number"]);

    $cond = " WHERE lotto_name = '" . $lotto_name . "'" . " OR lotto_phone = '" . $lotto_phone . "' OR lotto_number = '" . $lotto_number . "' ";

    $data = $lotto_name . " | " . $lotto_phone . " | " . $lotto_province . " | " . $lotto_number . " | " . $client_ip_address;

    $return_arr = array();
    $sql_get = "SELECT count(*) as record_counts  FROM " . $table_name . $cond;

    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);
    foreach ($results as $result) {
        $record = $result['record_counts'];
    }

    if ($record <= 0) {

        $sql = "INSERT INTO ims_lotto(lotto_name,lotto_phone,lotto_province,lotto_number,sale_name,client_ip_address)
            VALUES (:lotto_name,:lotto_phone,:lotto_province,:lotto_number,:sale_name,:client_ip_address)";
        $query = $conn->prepare($sql);
        $query->bindParam(':lotto_name', $lotto_name, PDO::PARAM_STR);
        $query->bindParam(':lotto_phone', $lotto_phone, PDO::PARAM_STR);
        $query->bindParam(':lotto_province', $lotto_province, PDO::PARAM_STR);
        $query->bindParam(':lotto_number', $lotto_number, PDO::PARAM_STR);
        $query->bindParam(':sale_name', $sale_name, PDO::PARAM_STR);
        $query->bindParam(':client_ip_address', $client_ip_address, PDO::PARAM_STR);
        $query->execute();

        $lastInsertId = $conn->lastInsertId();
        if ($lastInsertId) {
            $reserve_status = 'Y';
            $sql_update = "UPDATE ims_number_reserve SET reserve_status=:reserve_status            
            WHERE lotto_number = :lotto_number";
            $query = $conn->prepare($sql_update);
            $query->bindParam(':reserve_status', $reserve_status, PDO::PARAM_STR);
            $query->bindParam(':lotto_number', $lotto_number, PDO::PARAM_STR);
            $query->execute();
            $ins = 1;
        } else {
            $ins = 3;
        }
    } else {
        $ins = 2;
    }

    //$my_file = fopen("sql_getdata1.txt", "w") or die("Unable to open file!");
    //fwrite($my_file, " record = " . $record . " : " . $sql . " : ins = " . $ins);
    //fclose($my_file);

    if ($record <= 0 && $ins == 1) {
        echo 1;
    } else {
        echo 3;
    }

}

if ($_POST["action"] === 'DELETE1') {
    $lotto_number = $_POST["id"];
    $sql_find = "SELECT * FROM ims_lotto WHERE lotto_number = " . $lotto_number;

/*
    $my_file = fopen("sql_find.txt", "w") or die("Unable to open file!");
    fwrite($my_file, " sql_find = " . $sql_find);
    fclose($my_file);
*/

    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {
        try {
            $sql_del = "DELETE FROM ims_lotto WHERE lotto_number = " . $lotto_number;
            $query = $conn->prepare($sql);
            //$query->execute();

            $sql_up = "UPDATE ims_lotto SET reserve_status = 'N' WHERE lotto_number = " . $lotto_number;
            $query = $conn->prepare($sql);
            //$query->execute();

/*
            $my_file = fopen("sql_del.txt", "w") or die("Unable to open file!");
            fwrite($my_file, " sql_del = " . $sql_del . " | " . $sql_up);
            fclose($my_file);
*/


            $del = 1;

        } catch (Exception $e) {
            $del = 3;
        }
    } else {
        $del = 2;
    }

/*
    $my_file = fopen("sql_del_res.txt", "w") or die("Unable to open file!");
    fwrite($my_file, " sql_del_res = " . $del);
    fclose($my_file);
*/

    if ($del === 1) {
        echo 1;
    } else {
        echo 3;
    }

}


if ($_POST["action"] === 'DELETE') {

    if ($_POST["lotto_number"] !== '') {
        $lotto_number = $_POST["lotto_number"];
        $sql_find = "SELECT * FROM ims_lotto WHERE lotto_number = '" . $lotto_number . "'";

/*
        $my_file = fopen("sql_find.txt", "w") or die("Unable to open file!");
        fwrite($my_file, " sql_find = " . $sql_find);
        fclose($my_file);
*/

        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {

            $sql_del = "DELETE FROM ims_lotto WHERE lotto_number = " . $lotto_number;
            $query = $conn->prepare($sql_del);
            $query->execute();

            $sql_up = "UPDATE ims_number_reserve SET reserve_status = 'N' WHERE lotto_number = " . $lotto_number;
            $query = $conn->prepare($sql_up);
            $query->execute();
/*
            $my_file = fopen("sql_del.txt", "w") or die("Unable to open file!");
            fwrite($my_file, " sql_del = " . $sql_del . " | " . $sql_up);
            fclose($my_file);
*/

            $del = 1;
        } else {
            $del = 3;
        }

    }

/*
    $my_file = fopen("sql_search.txt", "w") or die("Unable to open file!");
    fwrite($my_file, " sql_search = " . $del);
    fclose($my_file);
*/

}

if ($_POST["action"] === 'GET_SHOW_LOTTO') {

    ## Read value
    $draw = $_POST['draw'];
    $row = $_POST['start'];
    $rowperpage = $_POST['length']; // Rows display per page
    $columnIndex = $_POST['order'][0]['column']; // Column index
    $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
    $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
    $searchValue = $_POST['search']['value']; // Search value

    $searchArray = array();

## Search
    $searchQuery = " ";

    if ($searchValue != '') {
        $searchQuery = " AND (lotto_name LIKE :lotto_name or
        lotto_phone LIKE :lotto_phone) ";
        $searchArray = array(
            'lotto_name' => "%$searchValue%",
            'lotto_phone' => "%$searchValue%",
        );
    }

## Total number of records without filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_lotto where 1 = 1 ");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_lotto WHERE 1 = 1 " . $searchQuery);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

## Fetch records

    $columnName = " id,lotto_number,lotto_name ";
    /*
        $sql_getdata = "SELECT * FROM ims_lotto WHERE 1 = 1 " . $searchQuery
            . " ORDER BY " . $columnName . " " . $columnSortOrder . " LIMIT :limit,:offset";
    */

    $sql_getdata = "SELECT * FROM ims_lotto WHERE 1 = 1 " . $searchQuery
        . " ORDER BY " . $columnName . " " . $columnSortOrder . " LIMIT " . $row . "," . $rowperpage;

    $stmt = $conn->prepare($sql_getdata);

    /*
    $my_file = fopen("sql_getdata.txt", "w") or die("Unable to open file!");
    fwrite($my_file, " sql_getdata = " . $sql_getdata);
    fclose($my_file);
    */

    $stmt->execute();
    $empRecords = $stmt->fetchAll();
    $data = array();

    foreach ($empRecords as $row) {

        if ($_POST['sub_action'] === "GET_MASTER") {
            $data[] = array(
                "id" => $row['id'],
                "lotto_name" => $row['lotto_name'],
                "lotto_phone" => $row['lotto_phone'],
                "lotto_province" => $row['lotto_province'],
                "sale_name" => $row['sale_name'],
                "lotto_number" => $row['lotto_number'],
                "edit" => $row['lotto_number']
            );
        } else {
            $data[] = array(
                "id" => $row['id'],
                "lotto_name" => $row['lotto_name'],
                "lotto_number" => $row['lotto_number'],
                "edit" => "<button type='button' name='detail' id='" . $row['id'] . "' class='btn btn-info btn-xs detail' data-toggle='tooltip' title='Edit'>Edit</button>"
            );
        }
    }

    //file_put_contents('sql_data.txt', print_r($data, true));

/*
    $my_file = fopen("sql_data.txt", "w") or die("Unable to open file!");
    fwrite($my_file, " sql_data = " . implode(" ",$data));
    fclose($my_file);
*/

## Response Return Value
    $response = array(
        "draw" => intval($draw),
        "iTotalRecords" => $totalRecords,
        "iTotalDisplayRecords" => $totalRecordwithFilter,
        "aaData" => $data
    );

    //file_put_contents("data_res.txt", print_r($response, true), FILE_APPEND);

    echo json_encode($response);


}

