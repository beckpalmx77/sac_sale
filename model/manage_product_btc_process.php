<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/connect_sqlserver.php');
include('../config/lang.php');
include('../cond_file/query-product-price-main.php');

if ($_POST["action"] === 'GET_DATA') {

    $id = $_POST["id"];

    $return_arr = array();

    $sql_get = "SELECT * FROM ims_product WHERE id = " . $id;
    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $product_id = $result['product_id'];
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

    $draw = $_POST['draw'];
    $row = $_POST['start'];
    $rowperpage = $_POST['length'];
    $columnSortOrder = $_POST['order'][0]['dir'];
    $searchValue = $_POST['search']['value'];

    $searchArray = array();

    $searchQuery = " AND price_code LIKE :price_code ";
    $searchArray['price_code'] = $price_code . "%";

    if ($searchValue != '') {
        $searchQuery .= " AND (product_id LIKE :product_id OR name_t LIKE :name_t) ";
        $searchArray['product_id'] = "%" . $searchValue . "%";
        $searchArray['name_t'] = "%" . $searchValue . "%";
    }

    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_product WHERE price_code LIKE :pc ");
    $stmt->execute(['pc' => $price_code . "%"]);
    $totalRecords = $stmt->fetch()['allcount'];

    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_product WHERE 1=1 " . $searchQuery);
    $stmt->execute($searchArray);
    $totalRecordwithFilter = $stmt->fetch()['allcount'];

    $sql_getdata = "SELECT id, product_id, name_t, price_code, price FROM ims_product WHERE 1=1 " . $searchQuery
        . " ORDER BY product_id " . $columnSortOrder . " LIMIT :limit,:offset";

    $stmt = $conn->prepare($sql_getdata);
    foreach ($searchArray as $key => $search) {
        $stmt->bindValue(':' . $key, $search, PDO::PARAM_STR);
    }
    $stmt->bindValue(':limit', (int)$row, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$rowperpage, PDO::PARAM_INT);
    $stmt->execute();
    $empRecords = $stmt->fetchAll();
    
    $data = array();
    foreach ($empRecords as $row) {
        $data[] = array(
            "product_id" => $row['product_id'],
            "name_t" => $row['name_t'],
            "price_code" => $row['price_code'],
            "price" => number_format($row['price'], 2),
            "detail" => "<button type='button' name='detail' id='" . $row['id'] . "' class='btn btn-info btn-xs detail' data-toggle='tooltip' title='Detail'>Detail</button>"
        );
    }

    $response = array(
        "draw" => intval($draw),
        "iTotalRecords" => $totalRecords,
        "iTotalDisplayRecords" => $totalRecordwithFilter,
        "aaData" => $data
    );

    echo json_encode($response);
}

if ($_POST["action"] === 'GET_ALL_PRODUCTS') {

    $price_code = $_POST["price_code"];
    
    $searchValue = $_POST['search']['value'] ?? '';
    
    $searchQuery = " price_code LIKE :price_code ";
    $params = ['price_code' => $price_code . "%"];
    
    if ($searchValue != '') {
        $searchQuery .= " AND (product_id LIKE :product_id OR name_t LIKE :name_t) ";
        $params['product_id'] = "%" . $searchValue . "%";
        $params['name_t'] = "%" . $searchValue . "%";
    }
    
    $stmt = $conn->prepare("SELECT id, product_id, name_t, price_code, price FROM ims_product WHERE " . $searchQuery . " ORDER BY product_id");
    $stmt->execute($params);
    $empRecords = $stmt->fetchAll();
    
    $data = array();
    foreach ($empRecords as $row) {
        $data[] = array(
            "product_id" => $row['product_id'],
            "name_t" => $row['name_t'],
            "price_code" => $row['price_code'],
            "price" => number_format($row['price'], 2),
            "detail" => "<button type='button' name='detail' id='" . $row['id'] . "' class='btn btn-info btn-xs detail' data-toggle='tooltip' title='Detail'>Detail</button>"
        );
    }
    
    echo json_encode($data);
}
