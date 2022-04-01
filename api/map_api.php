<?php
header('Content-Type: application/json');
$objConnect = mysqli_connect("localhost","root","root");
$objDB = mysqli_select_db("mydatabase");
mysqli_query("SET NAMES UTF8");

$strSQL = "SELECT * FROM location  ";

$objQuery = mysqli_query($strSQL);
$resultArray = array();
while($obResult = mysqli_fetch_array($objQuery))
{
    array_push($resultArray,$obResult);
}

mysqli_close($objConnect);

echo json_encode($resultArray);
?>