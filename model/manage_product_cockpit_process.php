<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../cond_file/query-product-price-main.php');


if ($_POST["action"] === 'GET_DATA') {

    $id = $_POST["id"];

    $return_arr = array();

    $sql_get = "SELECT * FROM ims_product WHERE id = " . $id;
    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "product_id" => $result['product_id'],
            "product_name" => $result['name_t'],
            "price_code" => $result['price_code'],
            "price" => number_format($result['price'], 2));
    }

    echo json_encode($return_arr);

}

if ($_POST["action"] === 'GET_PRODUCT') {

    $price_code = $_POST["price_code"];

    $my_file = fopen("price_code.txt", "w") or die("Unable to open file!");
    fwrite($my_file, " price_code = " . $price_code);
    fclose($my_file);

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
    $searchQuery = " AND price_code like '" . $price_code . "%' ";

    if ($searchValue != '') {
        $searchQuery = " AND (product_id LIKE :product_id or
        name_t LIKE :name_t) ";
        $searchArray = array(
            'product_id' => "%$searchValue%",
            'name_t' => "%$searchValue%",
        );
    }

    $my_file = fopen("wd_file2.txt", "w") or die("Unable to open file!");
    fwrite($my_file, " Condition = " . $searchQuery . " | " . $price_code);
    fclose($my_file);

## Total number of records without filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_product where price_code like '" . $price_code . "%' ");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_product WHERE price_code like '" . $price_code . "%' " . $searchQuery);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

## Fetch records

    $sql_getdata = "SELECT * FROM ims_product WHERE price_code like '" . $price_code . "%' " . $searchQuery
        . " ORDER BY " . $columnName . " " . $columnSortOrder . " LIMIT :limit,:offset";

    $my_file = fopen("sql_getdata.txt", "w") or die("Unable to open file!");
    fwrite($my_file, " sql_getdata = " . $sql_getdata);
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

    foreach ($empRecords as $row) {

        if ($_POST['sub_action'] === "GET_MASTER") {
            $data[] = array(
                "product_id" => $row['product_id'],
                "name_t" => $row['name_t'],
                "price" => number_format($row['price'], 2),
                "detail" => "<button type='button' name='detail' id='" . $row['id'] . "' class='btn btn-info btn-xs detail' data-toggle='tooltip' title='Detail'>Detail</button>"
            );
        } else {
            $data[] = array(
                "id" => $row['id'],
                "name_t" => $row['name_t'],
                "price" => $row['price'],
                "select" => "<button type='button' name='select' id='" . $row['id'] . "@" . $row['name_t'] . "' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='select'>select <i class='fa fa-check' aria-hidden='true'></i>
</button>",
            );
        }

    }

## Response Return Value
    $response = array(
        "draw" => intval($draw),
        "iTotalRecords" => $totalRecords,
        "iTotalDisplayRecords" => $totalRecordwithFilter,
        "aaData" => $data
    );

    echo json_encode($response);


}
