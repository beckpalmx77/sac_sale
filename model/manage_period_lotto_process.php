<?php
session_start();
error_reporting(0);

include('../config/connect_lotto_db.php');
include('../config/lang.php');
include('../util/record_util.php');
include('../util/month_util.php');

if ($_POST["action"] === 'GET_DATA') {

    $id = $_POST["id"];

    $return_arr = array();

    $sql_get = "SELECT * FROM ims_lotto_period WHERE id = " . $id;
    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "period_no" => $result['period_no'],
            "period_month" => $result['period_month'],
            "period_year" => $result['period_year'],
            "lotto_type" => $result['lotto_type'],
            "lotto_number_result" => $result['lotto_number_result']);
    }

    echo json_encode($return_arr);

}

if ($_POST["action"] === 'SEARCH') {

    if ($_POST["period_month"] !== '') {

        $period_month = $_POST["period_month"];
        $sql_find = "SELECT * FROM ims_lotto_period WHERE period_month = '" . $period_month . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo 2;
        } else {
            echo 1;
        }
    }
}

if ($_POST["action"] === 'ADD') {
    if ($_POST["lotto_number_result"] !== '') {

        $period_no = $_POST["period_no"];
        $period_month = $_POST["period_month"];
        $period_year = $_POST["period_year"];
        $lotto_type = $_POST["lotto_type"];
        $lotto_number_result = $_POST["lotto_number_result"];

        $stmt = $conn->prepare("SELECT COUNT(*) FROM ims_lotto_period 
        WHERE period_no = :period_no AND period_month = :period_month AND period_year = :period_year AND lotto_type = :lotto_type");
        $stmt->bindParam(':period_no', $period_no, PDO::PARAM_INT);
        $stmt->bindParam(':period_month', $period_month, PDO::PARAM_INT);
        $stmt->bindParam(':period_year', $period_year, PDO::PARAM_INT);
        $stmt->bindParam(':lotto_type', $lotto_type, PDO::PARAM_STR);
        $stmt->execute();

        $rowCount = $stmt->fetchColumn();

        if ($rowCount > 0) {
            echo $dup;
        } else {
            $sql = "INSERT INTO ims_lotto_period(period_no, period_month, period_year, lotto_type, lotto_number_result) 
                    VALUES (:period_no, :period_month, :period_year, :lotto_type, :lotto_number_result)";
            $query = $conn->prepare($sql);
            $query->bindParam(':period_no', $period_no, PDO::PARAM_INT);
            $query->bindParam(':period_month', $period_month, PDO::PARAM_INT);
            $query->bindParam(':period_year', $period_year, PDO::PARAM_INT);
            $query->bindParam(':lotto_type', $lotto_type, PDO::PARAM_STR);
            $query->bindParam(':lotto_number_result', $lotto_number_result, PDO::PARAM_STR);
            $query->execute();
            $lastInsertId = $conn->lastInsertId();

            if ($lastInsertId) {
                echo $save_success;
            } else {
                echo $error;
            }
        }
    }
}


if ($_POST["action"] === 'UPDATE') {

    if ($_POST["lotto_number_result"] != '') {

        $id = $_POST["id"];
        $period_no = $_POST["period_no"];
        $period_month = $_POST["period_month"];
        $period_year = $_POST["period_year"];
        $lotto_type = $_POST["lotto_type"];
        $lotto_number_result = $_POST["lotto_number_result"];

        // Prepare the query to check if a record exists, parameterized for security
        $sql_find = "SELECT COUNT(*) FROM ims_lotto_period WHERE period_no = :period_no";
        $stmt = $conn->prepare($sql_find);
        $stmt->bindParam(':period_no', $period_no, PDO::PARAM_STR);
        $stmt->execute();
        $nRows = $stmt->fetchColumn();

        if ($nRows > 0) {
            // Prepare the UPDATE statement
            $sql_update = "UPDATE ims_lotto_period SET lotto_number_result = :lotto_number_result
                           WHERE id = :id";
            $query = $conn->prepare($sql_update);
            $query->bindParam(':lotto_number_result', $lotto_number_result, PDO::PARAM_STR);
            $query->bindParam(':id', $id, PDO::PARAM_INT);
            $query->execute();
            echo $save_success;
        } else {
            // If no record found, you may want to return an error or handle it
            echo $error;
        }
    }
}


if ($_POST["action"] === 'DELETE') {

    $id = $_POST["id"];

    $sql_find = "SELECT * FROM ims_lotto_period WHERE id = " . $id;
    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {
        try {
            $sql = "DELETE FROM ims_lotto_period WHERE id = " . $id;
            $query = $conn->prepare($sql);
            $query->execute();
            echo $del_success;
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if ($_POST["action"] === 'GET_LOTTO_PERIOD_RESULT') {

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
        $searchQuery = " AND (period_no LIKE :period_no or
        period_month LIKE :period_month or period_year LIKE :period_year) ";
        $searchArray = array(
            'period_no' => "%$searchValue%",
            'period_month' => "%$searchValue%",
            'period_year' => "%$searchValue%",
        );
    }

## Total number of records without filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_lotto_period ");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_lotto_period WHERE 1 " . $searchQuery);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

## Fetch records
    $str_qry = "SELECT * FROM ims_lotto_period WHERE 1 " . $searchQuery
        . " ORDER BY period_year DESC , period_month DESC , period_no DESC , lotto_type LIMIT :limit,:offset";
    $stmt = $conn->prepare($str_qry);

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

            $month_name = $month_arr[$row['period_month']];
            $lotto_type = $row['lotto_type'] === 2 ? "เลขท้าย 3 ตัว" : "เลขท้าย 2 ตัว";

            $data[] = array(
                "id" => $row['id'],
                "period_no" => $row['period_no'],
                "period_month" => $month_name,
                "period_year" => $row['period_year'],
                "lotto_type" => $lotto_type,
                "lotto_number_result" => $row['lotto_number_result'],
                "update" => "<button type='button' name='update' id='" . $row['id'] . "' class='btn btn-info btn-xs update' data-toggle='tooltip' title='Update'>Update</button>",
                "delete" => "<button type='button' name='delete' id='" . $row['id'] . "' class='btn btn-danger btn-xs delete' data-toggle='tooltip' title='Delete'>Delete</button>",
                "status" => $row['status'] === 'Active' ? "<div class='text-success'>" . $row['status'] . "</div>" : "<div class='text-muted'> " . $row['status'] . "</div>"
            );
        } else {
            $data[] = array(
                "id" => $row['id'],
                "period_no" => $row['period_no'],
                "period_month" => $row['period_month'],
                "select" => "<button type='button' name='select' id='" . $row['period_no'] . "@" . $row['period_month'] . "' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='select'>select <i class='fa fa-check' aria-hidden='true'></i>
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
