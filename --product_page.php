<!DOCTYPE html>
<html lang="th">

<?php
include('includes/Header.php');
?>

<style>
    body {
        font-family: 'Kanit', sans-serif;
    }
</style>


<body class="bg-gradient-login">
<!-- Login Content -->
<div class="container-login">
    <div class="row justify-content-center">
        <div class="col-md-9 col-lg-9 col-md-9">
            <div class="card shadow-sm my-5">
                <div class="card-body p-0">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="product-form">
                                <div class="text-center">
                                    <div><img src="img/logo/logo text-01.png" width="400" height="158"/></div>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <table id='TableRecordList' class='display dataTable'>
                                        <thead>
                                        <tr>
                                            <th>รหัสสินค้า/วัสดุ</th>
                                            <th>ชื่อสินค้า/วัสดุ</th>
                                            <th>ยอดคงเหลือ</th>
                                            <th>ราคา</th>
                                        </tr>
                                        </thead>
                                        <tfoot>
                                        <tr>
                                            <th>รหัสสินค้า/วัสดุ</th>
                                            <th>ชื่อสินค้า/วัสดุ</th>
                                            <th>ยอดคงเหลือ</th>
                                            <th>ราคา</th>

                                        </tr>
                                        </tfoot>
                                    </table>

                                </div>


                                <div class="form-group">
                                    <button type="button" name="backBtn" id="backBtn" tabindex="4"
                                            class="form-control btn btn-danger">
                                            <span>
                                                <i class="fa fa-reply" aria-hidden="true"></i>
                                                กลับหน้าแรก
                                            </span>
                                </div>
                            </div>


                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>

<script src="vendor/datatables/v11/bootbox.min.js"></script>
<script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
<link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>

<script>
    $(function ($) {
            let formData = {action: "GET_PRODUCT", sub_action: "GET_MASTER"};
            let dataRecords = $('#TableRecordList').DataTable({
                'lengthMenu': [[5, 10, 20, 50, 100], [5, 10, 20, 50, 100]],
                'language': {
                    search: 'ค้นหา', lengthMenu: 'แสดง _MENU_ รายการ',
                    info: 'หน้าที่ _PAGE_ จาก _PAGES_',
                    infoEmpty: 'ไม่มีข้อมูล',
                    zeroRecords: "ไม่มีข้อมูลตามเงื่อนไข",
                    infoFiltered: '(กรองข้อมูลจากทั้งหมด _MAX_ รายการ)',
                    paginate: {
                        previous: 'ก่อนหน้า',
                        last: 'สุดท้าย',
                        next: 'ต่อไป'
                    }
                },
                'processing': true,
                'serverSide': true,
                'serverMethod': 'post',
                'ajax': {
                    'url': 'model/manage_product_process.php',
                    'data': formData
                },
                'columns': [
                    {data: 'product_id'},
                    {data: 'name_t'},
                    {data: 'quantity', className: 'text-right'},
                    {data: 'unit_name'},
                    {data: 'status'},
                    {data: 'update'},
                    {data: 'delete'}
                ]
            });

    });
</script>


<script>
    $(document).ready(function () {
        $("#backBtn").click(function () {
            window.location.href = "dashboard.php";
        });
    });
</script>

</body>
</html>


