<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {
    ?>

    <!DOCTYPE html>
    <html lang="th">
    <body class="bg-gradient-login" id="page-top">
    <!-- Login Content -->
    <div class="container-login">
        <div class="row justify-content-center">
            <div class="col-md-9 col-lg-6 col-md-9">
                <div class="card shadow-sm my-5">
                    <div class="card-body p-0">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="product-form">
                                    <div class="text-center">
                                        <div><img src="img/logo/logo text-01.png" width="400" height="158"/></div>
                                        <div><img src="img/logo/cockpit_logo.jpg" width="200" height="50"/></div>
                                    </div>
                                </div>
                                <div class="col-lg-2">
                                    <div class="form-group">
                                        <input type="hidden" class="form-control" id="price_code" name="price_code"
                                               readonly="true" value="CP1">
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <table id='TableRecordList' class='display dataTable'>
                                            <thead>
                                            <tr>
                                                <th>รหัสสินค้า</th>
                                                <th>ชื่อสินค้า</th>
                                                <th>ราคา</th>
                                                <th>Action</th>
                                            </tr>
                                            </thead>
                                            <tfoot>
                                            <tr>
                                                <th>รหัสสินค้า</th>
                                                <th>ชื่อสินค้า</th>
                                                <th>ราคา</th>
                                                <th>Action</th>
                                            </tr>
                                            </tfoot>
                                        </table>

                                        <div id="result"></div>

                                    </div>

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

    <div class="modal fade" id="recordModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Modal title</h4>
                    <button type="button" class="close" data-dismiss="modal"
                            aria-hidden="true">×
                    </button>
                </div>
                <form method="post" id="recordForm">
                    <div class="modal-body">
                        <div class="modal-body">

                            <div class="form-group">
                                <label for="product_id" class="control-label">รหัสสินค้า</label>
                                <input type="product_id" class="form-control"
                                       id="product_id" name="product_id"
                                       readonly="true"
                                       placeholder="รหัสสินค้า">
                            </div>

                            <div class="form-group">
                                <label for="product_name"
                                       class="control-label">ชื่อสินค้า</label>
                                <input type="text" class="form-control"
                                       id="product_name"
                                       name="product_name"
                                       required="required"
                                       placeholder="ชื่อสินค้า">
                            </div>

                            <div class="form-group">
                                <label for="price"
                                       class="control-label">ราคาสินค้า</label>
                                <input type="text" class="form-control"
                                       id="price"
                                       name="price"
                                       required="required"
                                       placeholder="ราคาสินค้า">
                            </div>

                            <div class="col-sm-10">
                                <!--a data-toggle="modal" href="#StockModal"
                                   class="btn btn-primary">
                                    ยอดคงเหลือ Click <i class="fa fa-info-circle" aria-hidden="true"></i>

                                </a-->
                                <button type="button" id="BtnStock" name="BtnStock" class="btn btn-info"
                                        data-dismiss="modal">ยอดคงเหลือ <i
                                            class="fa fa-info-circle"></i>
                                </button>
                            </div>


                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="id" id="id"/>
                        <input type="hidden" name="action" id="action" value=""/>
                        <button type="button" class="btn btn-danger"
                                data-dismiss="modal">Close <i
                                    class="fa fa-window-close"></i>
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <div class="modal fade" id="StockModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Modal title</h4>
                    <button type="button" class="close" data-dismiss="modal"
                            aria-hidden="true">×
                    </button>
                </div>

                <div class="container"></div>
                <div class="modal-body">

                    <div class="modal-body">
                        <label for="product_name"
                               class="control-label">รหัสสินค้า</label>
                        <input type="text" class="form-control"
                               id="product_id_detail"
                               name="product_id_detail"
                               required="required"
                               readonly="true"
                               placeholder="สินค้า">
                        <label for="product_name"
                               class="control-label">ชื่อสินค้า</label>
                        <input type="text" class="form-control"
                               id="product_name_detail"
                               name="product_name_detail"
                               required="required"
                               readonly="true"
                               placeholder="ชื่อสินค้า">
                        <br>

                        <table cellpadding="0" cellspacing="0" border="0"
                               class="display"
                               id="TableStockList"
                               width="100%">
                            <thead>
                            <tr>
                                <th>SKU</th>
                                <th>คลัง</th>
                                <th>จำนวน</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Scroll to top -->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>


    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/myadmin.min.js"></script>

    <!-- Page level plugins -->

    <!--script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/5.5.2/bootbox.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.0/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.0/css/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.0.0/css/buttons.dataTables.min.css"/-->

    <script src="vendor/datatables/v11/bootbox.min.js"></script>
    <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>

    <style>

        .icon-input-btn {
            display: inline-block;
            position: relative;
        }

        .icon-input-btn input[type="submit"] {
            padding-left: 2em;
        }

        .icon-input-btn .fa {
            display: inline-block;
            position: absolute;
            left: 0.65em;
            top: 30%;
        }
    </style>

    <script>
        $(document).ready(function () {
            $(".icon-input-btn").each(function () {
                let btnFont = $(this).find(".btn").css("font-size");
                let btnColor = $(this).find(".btn").css("color");
                $(this).find(".fa").css({'font-size': btnFont, 'color': btnColor});
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            let price_code = $('#price_code').val();
            let formData = {action: "GET_PRODUCT", sub_action: "GET_MASTER", price_code: price_code};
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
                    'url': 'model/manage_product_cockpit_process.php',
                    'data': formData
                },
                'columns': [
                    {data: 'product_id'},
                    {data: 'name_t'},
                    {data: 'price', className: 'text-right'},
                    {data: 'detail'}
                ]
            });
        });
    </script>


    <script>

        $("#TableRecordList").on('click', '.detail', function () {
            let id = $(this).attr("id");
            //alert(id);
            let formData = {action: "GET_DATA", id: id};
            $.ajax({
                type: "POST",
                url: 'model/manage_product_cockpit_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let id = response[i].id;
                        let product_id = response[i].product_id;
                        let product_name = response[i].product_name;
                        //let price_code = response[i].price_code;
                        let price = response[i].price;

                        $('#recordModal').modal('show');
                        $('#id').val(id);
                        $('#product_id').val(product_id);
                        $('#product_name').val(product_name);
                        //$('#price_code').val(price_code);
                        $('#price').val(price);
                        $('.modal-title').html("<i class='fa fa-plus'></i> Detail Record");
                        $('#action').val('UPDATE');
                        $('#save').val('Save');
                        load_stock(product_id);
                    }
                },
                error: function (response) {
                    alertify.error("error : " + response);
                }
            });
        });

    </script>


    <script>
        $(document).ready(function () {
            $("#backBtn").click(function () {
                window.location.href = "dashboard";
            });
        });
    </script>

    <script>

        $("#BtnStock").click(function () {

            $('#StockModal').modal('show');
            $('#product_id_detail').val($('#product_id').val());
            $('#product_name_detail').val($('#product_name').val());
            let product_id_detail = $('#product_id').val();

            $('#TableStockList').DataTable().clear().destroy();

            let formData = {action: "GET_STOCK", sub_action: "GET_MASTER", product_id_detail: product_id_detail};
            let dataRecords = $('#TableStockList').DataTable({
                'searching': false,
                'paging': false,
                'info': false,
                'processing': true,
                'serverSide': true,
                'serverMethod': 'post',
                'ajax': {
                    'url': 'process/load_stock_balance_data.php',
                    'data': formData
                },
                'columns': [
                    {data: 'SKU_CODE'},
                    {data: 'WH_WL_CODE'},
                    {data: 'QTY'}
                ]
            });

        });

    </script>

    <script>
        function load_stock(product_id) {
            let formData = {action: "GET_DATA", product_id: product_id};
            $.ajax({
                url: "process/process_stock_balance.php",
                type: "post",
                data: formData,
                success: function (response) {
                    //get_stock_balance(product_id);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log(textStatus, errorThrown);
                }
            });

        }
    </script>

    </body>
    </html>

<?php } ?>