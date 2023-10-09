<?php

include('../config/connect_lotto_db.php');

$table_name = $_POST["table_name"];

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

    $ins = 3 ;
    $sql = "";
    $lotto_name = $_POST["lotto_name"];
    $lotto_phone = $_POST["lotto_phone"];
    $lotto_province = $_POST["lotto_province"];
    $lotto_number = $_POST["lotto_number"];

    $cond = " WHERE lotto_name = '" . $lotto_name . "'" . " OR lotto_phone = '" . $lotto_phone . "'";

    $data = $lotto_name . " | " . $lotto_phone . " | " . $lotto_province . " | " . $lotto_number ;

    $return_arr = array();
    $sql_get = "SELECT count(*) as record_counts  FROM " . $table_name . $cond;

    $my_file = fopen("sql_getdata0.txt", "w") or die("Unable to open file!");
    fwrite($my_file, " sql_get = " . $sql_get . "\n\r" . $data);
    fclose($my_file);

    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);
    foreach ($results as $result) {
        $record = $result['record_counts'];
    }

    if ($record<=0) {

        $sql = "INSERT INTO ims_lotto(lotto_name,lotto_phone,lotto_province,lotto_number)
            VALUES (:lotto_name,:lotto_phone,:lotto_province,:lotto_number)";
        $query = $conn->prepare($sql);
        $query->bindParam(':lotto_name', $lotto_name, PDO::PARAM_STR);
        $query->bindParam(':lotto_phone', $lotto_phone, PDO::PARAM_STR);
        $query->bindParam(':lotto_province', $lotto_province, PDO::PARAM_STR);
        $query->bindParam(':lotto_number', $lotto_number, PDO::PARAM_STR);
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

    if ($searchValue != '') {
        $searchQuery = " AND (lotto_name LIKE :lotto_name or
        lotto_phone LIKE :lotto_phone) ";
        $searchArray = array(
            'lotto_name' => "%$searchValue%",
            'lotto_phone' => "%$searchValue%",
        );
    }

/*
    $my_file = fopen("wd_file2.txt", "w") or die("Unable to open file!");
    fwrite($my_file, " Condition = " . $searchQuery);
    fclose($my_file);
*/

## Total number of records without filtering
    $sql_get_rec1 = "SELECT COUNT(*) AS allcount FROM ims_lotto WHERE 1 = 1 ";
    $stmt = $conn->prepare($sql_get_rec1);
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $sql_get_rec2 = "SELECT COUNT(*) AS allcount FROM ims_lotto WHERE 1 = 1 " . $searchQuery ;
    $stmt = $conn->prepare($sql_get_rec2);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

## Fetch records

    $columnName = " lotto_number ";

    $sql_getdata = "SELECT * FROM ims_lotto WHERE 1 = 1 " . $searchQuery
        . " ORDER BY " . $columnName . " " . $columnSortOrder . " LIMIT :limit,:offset";


    $my_file = fopen("sql_getdata.txt", "w") or die("Unable to open file!");
    fwrite($my_file, " sql_getdata = " . $sql_getdata . "\n\r" . $sql_get_rec1 . "\n\r" . $sql_get_rec2);
    fclose($my_file);



    $stmt = $conn->prepare($sql_getdata);

// Bind values
    foreach ($searchArray as $key => $search) {
        $stmt->bindValue(':' . $key, $search, PDO::PARAM_STR);
    }

    $stmt->bindValue(':limit', (int)$row, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$rowperpage, PDO::PARAM_INT);
    $stmt->execute();
    $empRecords = $stmt->fetchAll();
    $data = array();
    $loops = 0 ;

    foreach ($empRecords as $row) {

        if ($_POST['sub_action'] === "GET_MASTER") {
            $data[] = array(
                "id" => $row['id'],
                "lotto_name" => $row['lotto_name'],
                "lotto_phone" => $row['lotto_phone'],
                "lotto_province" => $row['lotto_province'],
                "lotto_number" => $row['lotto_number']
            );
        } else {
            $data[] = array(
                "id" => $row['id'],
                "lotto_name" => $row['lotto_name'],
                "lotto_phone" => $row['lotto_phone'],
                "select" => "<button type='button' name='select' id='" . $row['id'] . "@" . $row['lotto_name'] . "' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='select'>select <i class='fa fa-check' aria-hidden='true'></i>
</button>",
            );
        }

    }

/*
    $my_file = fopen("s_get_lotto_data.txt", "w") or die("Unable to open file!");
    fwrite($my_file, " s_get_lotto_data = " . $draw . " | " . $totalRecords . " | " . $totalRecordwithFilter);
    //fwrite($my_file, " data = " . $data_load);
    fclose($my_file);
*/

## Response Return Value
    $response = array(
        "draw" => intval($draw),
        "iTotalRecords" => $totalRecords,
        "iTotalDisplayRecords" => $totalRecordwithFilter,
        "aaData" => $data
    );

    echo json_encode($response);


}

