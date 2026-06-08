<?php
include('config/connect_db.php');
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {
    ?>
    <!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <title>ระบบวิเคราะห์ระดับสต็อกสินค้า (STOCK / MAX / MIN / AVG)</title>
        <!-- Google Fonts Inter & Outfit -->
        <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
        
        <style>
            body {
                font-family: 'Kanit', 'Outfit', sans-serif;
                background-color: #f4f6f9;
            }
            .card-header-premium {
                background: linear-gradient(135deg, #1f3c88 0%, #102a43 100%);
                color: #ffffff;
            }
            .text-title-premium {
                font-family: 'Outfit', 'Kanit', sans-serif;
                font-weight: 600;
                letter-spacing: 0.5px;
            }
            .badge-danger-custom {
                background-color: #ffe8ec;
                color: #d9534f;
                font-weight: 600;
                border: 1px solid #f5c2c2;
                padding: 5px 10px;
                border-radius: 4px;
            }
            .badge-success-custom {
                background-color: #e6f9ed;
                color: #2b8a3e;
                font-weight: 600;
                border: 1px solid #c3e6cb;
                padding: 5px 10px;
                border-radius: 4px;
            }
            .table-premium th {
                background-color: #f8f9fa;
                color: #486581;
                font-weight: 600;
                border-bottom: 2px solid #cbd5e0;
            }
            .table-premium td {
                vertical-align: middle;
            }
            .modal-header-premium {
                background: linear-gradient(135deg, #102b43 0%, #1f3a60 100%);
                color: white;
            }
            .control-card {
                background-color: #ffffff;
                border-radius: 8px;
                box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
            }
        </style>
    </head>
    
    <body class="bg-gradient-login" id="page-top">
    
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-12">
                
                <!-- Page Title Header -->
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800 text-title-premium">
                        <i class="fa fa-calculator text-primary"></i> 
                        ระบบวิเคราะห์ระดับสต็อกสินค้า (STOCK / MAX / MIN / AVG)
                    </h1>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard">หน้าหลัก</a></li>
                        <li class="breadcrumb-item active" aria-current="page">วิเคราะห์สต็อกสินค้า</li>
                    </</ol>
                </div>

                <!-- Selection Filters Card -->
                <div class="card control-card mb-4">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-3 mb-3 mb-md-0">
                                <label for="filter_year" class="form-label text-dark font-weight-bold">
                                    <i class="fa fa-calendar text-primary"></i> เลือกปีในการวิเคราะห์
                                </label>
                                <select class="form-control" id="filter_year" name="filter_year">
                                    <option value="2026" selected>2026 (ปีปัจจุบัน)</option>
                                    <option value="2025">2025</option>
                                    <option value="2024">2024</option>
                                    <option value="2023">2023</option>
                                    <option value="2022">2022</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3 mb-md-0">
                                <label for="filter_channel" class="form-label text-dark font-weight-bold">
                                    <i class="fa fa-shopping-bag text-primary"></i> ช่องทางการขาย
                                </label>
                                <select class="form-control" id="filter_channel" name="filter_channel">
                                    <?php if ($_SESSION['account_type'] === 'admin' || $_SESSION['account_type'] === '') { ?>
                                        <option value="cockpit" selected>Cockpit (ค้าปลีก)</option>
                                        <option value="sac">SAC (ค้าส่ง)</option>
                                        <option value="btc">BTC</option>
                                    <?php } elseif ($_SESSION['account_type'] === 'cockpit') { ?>
                                        <option value="cockpit" selected>Cockpit (ค้าปลีก)</option>
                                    <?php } elseif ($_SESSION['account_type'] === 'btc') { ?>
                                        <option value="btc" selected>BTC</option>
                                    <?php } elseif ($_SESSION['account_type'] === 'sac') { ?>
                                        <option value="sac" selected>SAC (ค้าส่ง)</option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3 mb-md-0">
                                <label for="filter_category" class="form-label text-dark font-weight-bold">
                                    <i class="fa fa-tags text-primary"></i> หมวดหมู่สินค้า (ICCAT_NAME)
                                </label>
                                <select class="form-control" id="filter_category" name="filter_category">
                                    <option value="">--- ทั้งหมด ---</option>
                                    <?php
                                    try {
                                        $stmt_cats = $conn->query("SELECT ICCAT_NAME FROM ims_product_stock_balance WHERE ICCAT_NAME IS NOT NULL AND ICCAT_NAME != '' GROUP BY ICCAT_NAME ORDER BY ICCAT_NAME ASC");
                                        $categories = $stmt_cats->fetchAll(PDO::FETCH_COLUMN);
                                        foreach ($categories as $cat) {
                                            echo '<option value="' . htmlspecialchars($cat, ENT_QUOTES) . '">' . htmlspecialchars($cat) . '</option>';
                                        }
                                    } catch (Exception $e) {}
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-3 text-md-right mt-3 mt-md-0">
                                <button type="button" id="btn_refresh" class="btn btn-primary btn-block" style="margin-top: 32px;">
                                    <i class="fa fa-refresh"></i> ประมวลผลข้อมูลใหม่
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Data Table Card -->
                <div class="card shadow mb-4">
                    <div class="card-header card-header-premium py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-title-premium">
                            <i class="fa fa-table"></i> ตารางวิเคราะห์ระดับสต็อกสินค้าคงเหลือเทียบอัตราขาย
                        </h6>
                    </div>
                    
                    <div class="card-body">
                        <!-- Helper Info alert -->
                        <div class="alert alert-info py-2" role="alert">
                            <i class="fa fa-info-circle"></i> 
                            <b>การคำนวณสถิติ:</b> ค่า <b>MAX, MIN, AVG</b> จะคำนวณจากประวัติการขายรายเดือนของปีที่เลือก เฉพาะเดือน <b>มกราคม - พฤษภาคม</b> (5 เดือนแรก) เพื่อให้สอดคล้องกับมาตรฐานการวิเคราะห์สต็อกสินค้า
                        </div>
                        
                        <div class="table-responsive">
                            <table id="TableAnalysisList" class="table table-bordered table-hover table-premium display dataTable text-nowrap" width="100%" style="font-size: 0.85rem;">
                                <thead>
                                <tr>
                                    <th colspan="2" class="text-center bg-primary text-white">ข้อมูลสินค้า</th>
                                    <th colspan="6" class="text-center bg-secondary text-white">ยอดขายรายเดือน</th>
                                    <th class="text-center bg-dark text-white">ผลรวม</th>
                                    <th colspan="5" class="text-center bg-info text-white">สถิติ & สต็อกรวม</th>
                                    <th colspan="4" class="text-center bg-warning text-dark">ยอดขายรายสาขา</th>
                                    <th colspan="4" class="text-center bg-primary text-white">STOCK รายสาขา</th>
                                    <th colspan="4" class="text-center bg-danger text-white">ความต้องการเพิ่มรายสาขา</th>
                                    <th rowspan="2" class="text-center bg-dark text-white" style="vertical-align: middle;">การดำเนินการ</th>
                                </tr>
                                <tr>
                                    <th>รหัสสินค้า</th>
                                    <th>ชื่อรายละเอียดสินค้า</th>
                                    <th class="text-right">ม.ค.</th>
                                    <th class="text-right">ก.พ.</th>
                                    <th class="text-right">มี.ค.</th>
                                    <th class="text-right">เม.ย.</th>
                                    <th class="text-right">พ.ค.</th>
                                    <th class="text-right">มิ.ย.</th>
                                    <th class="text-right font-weight-bold">ผลรวมทั้งหมด</th>
                                    <th class="text-right">STOCK</th>
                                    <th class="text-right">MAX</th>
                                    <th class="text-right">MIN</th>
                                    <th class="text-right">AVG</th>
                                    <th class="text-right">ส่งไปเพิ่ม</th>
                                    <th class="text-right">340</th>
                                    <th class="text-right">ราชพฤกษ์</th>
                                    <th class="text-right">บางใหญ่</th>
                                    <th class="text-right">บางบอน</th>
                                    <th class="text-right">340</th>
                                    <th class="text-right">ราชพฤกษ์</th>
                                    <th class="text-right">บางใหญ่</th>
                                    <th class="text-right">บางบอน</th>
                                    <th class="text-right">340</th>
                                    <th class="text-right">ราชพฤกษ์</th>
                                    <th class="text-right">บางใหญ่</th>
                                    <th class="text-right">บางบอน</th>
                                </tr>
                                <!--
                                <tr id="table_sum_row" style="background-color: #eef2f7; font-weight: bold; border-bottom: 2px solid #cbd5e0;">
                                    <th colspan="2" class="text-center text-primary font-weight-bold" style="vertical-align: middle;">ผลรวมทั้งหมด (กรองแล้ว)</th>
                                    <th id="sum_m1" class="text-right text-dark">0.00</th>
                                    <th id="sum_m2" class="text-right text-dark">0.00</th>
                                    <th id="sum_m3" class="text-right text-dark">0.00</th>
                                    <th id="sum_m4" class="text-right text-dark">0.00</th>
                                    <th id="sum_m5" class="text-right text-dark">0.00</th>
                                    <th id="sum_m6" class="text-right text-dark">0.00</th>
                                    <th id="sum_total_sales" class="text-right text-primary font-weight-bold">0.00</th>
                                    <th id="sum_stock" class="text-right text-dark">0.00</th>
                                    <th id="sum_max" class="text-right text-dark">0.00</th>
                                    <th id="sum_min" class="text-right text-dark">0.00</th>
                                    <th id="sum_avg" class="text-right text-dark">0.00</th>
                                    <th id="sum_needed" class="text-right text-dark">0.00</th>
                                    
                                    <th id="sum_sales_340" class="text-right text-dark">0.00</th>
                                    <th id="sum_sales_ratchaphruek" class="text-right text-dark">0.00</th>
                                    <th id="sum_sales_bangyai" class="text-right text-dark">0.00</th>
                                    <th id="sum_sales_bangbon" class="text-right text-dark">0.00</th>
                                    
                                    <th id="sum_stock_340" class="text-right text-dark">0.00</th>
                                    <th id="sum_stock_ratchaphruek" class="text-right text-dark">0.00</th>
                                    <th id="sum_stock_bangyai" class="text-right text-dark">0.00</th>
                                    <th id="sum_stock_bangbon" class="text-right text-dark">0.00</th>
                                    
                                    <th id="sum_needed_340" class="text-right text-dark">0.00</th>
                                    <th id="sum_needed_ratchaphruek" class="text-right text-dark">0.00</th>
                                    <th id="sum_needed_bangyai" class="text-right text-dark">0.00</th>
                                    <th id="sum_needed_bangbon" class="text-right text-dark">0.00</th>
                                    <th></th>
                                </tr>
                                -->
                                </thead>
                                <tfoot>
                                <tr>
                                    <th>รหัสสินค้า</th>
                                    <th>ชื่อรายละเอียดสินค้า</th>
                                    <th class="text-right">ม.ค.</th>
                                    <th class="text-right">ก.พ.</th>
                                    <th class="text-right">มี.ค.</th>
                                    <th class="text-right">เม.ย.</th>
                                    <th class="text-right">พ.ค.</th>
                                    <th class="text-right">มิ.ย.</th>
                                    <th class="text-right font-weight-bold">ผลรวมทั้งหมด</th>
                                    <th class="text-right">STOCK</th>
                                    <th class="text-right">MAX</th>
                                    <th class="text-right">MIN</th>
                                    <th class="text-right">AVG</th>
                                    <th class="text-right">ส่งไปเพิ่ม</th>
                                    <th class="text-right">340</th>
                                    <th class="text-right">ราชพฤกษ์</th>
                                    <th class="text-right">บางใหญ่</th>
                                    <th class="text-right">บางบอน</th>
                                    <th class="text-right">340</th>
                                    <th class="text-right">ราชพฤกษ์</th>
                                    <th class="text-right">บางใหญ่</th>
                                    <th class="text-right">บางบอน</th>
                                    <th class="text-right">340</th>
                                    <th class="text-right">ราชพฤกษ์</th>
                                    <th class="text-right">บางใหญ่</th>
                                    <th class="text-right">บางบอน</th>
                                    <th class="text-center">การดำเนินการ</th>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Back to Main Page Button -->
                <div class="form-group mb-5">
                    <button type="button" name="backBtn" id="backBtn" class="btn btn-danger">
                        <span><i class="fa fa-reply" aria-hidden="true"></i> กลับหน้าหลักแดชบอร์ด</span>
                    </button>
                </div>

            </div>
        </div>
    </div>

    <!-- Branch Stock & Sales Breakdown Modal -->
    <div class="modal fade" id="BranchDetailModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header modal-header-premium">
                    <h5 class="modal-title font-weight-bold" id="modal_product_title">
                        <i class="fa fa-home"></i> รายละเอียดระดับสต็อกและยอดขายรายสาขา
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                
                <div class="modal-body">
                    <div class="card bg-light mb-3">
                        <div class="card-body py-2">
                            <h6 class="font-weight-bold text-primary mb-1">ข้อมูลสินค้า:</h6>
                            <p class="mb-0 text-dark font-weight-normal" id="modal_product_info"></p>
                        </div>
                    </div>
                    
                    <h6 class="font-weight-bold text-dark mb-2">ตารางสรุปรายสาขา (4 สาขาหลัก):</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="TableBranchBreakdown" width="100%">
                            <thead class="bg-primary text-white">
                            <tr>
                                <th>สาขา</th>
                                <th class="text-right">STOCK สาขา</th>
                                <th class="text-right">ยอดขายรวมสะสม (ม.ค. - ธ.ค.)</th>
                                <th class="text-right">ยอดขายเฉลี่ย (ม.ค. - พ.ค.)</th>
                                <th class="text-right" style="width: 220px;">ความต้องการเพิ่ม (ปรับปรุงได้)</th>
                            </tr>
                            </thead>
                            <tbody id="branch_breakdown_body">
                                <!-- Data populated via Ajax -->
                            </tbody>
                        </table>
                    </div>

                    <h6 class="font-weight-bold text-dark mt-4 mb-2"><i class="fa fa-bar-chart"></i> ตารางประวัติยอดขายรายเดือนแยกตามสาขา (ม.ค. - ธ.ค.):</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover text-nowrap" id="TableBranchMonthlySales" width="100%" style="font-size: 0.9rem;">
                            <thead class="bg-success text-white">
                            <tr>
                                <th>สาขา</th>
                                <th class="text-right">ม.ค.</th>
                                <th class="text-right">ก.พ.</th>
                                <th class="text-right">มี.ค.</th>
                                <th class="text-right">เม.ย.</th>
                                <th class="text-right">พ.ค.</th>
                                <th class="text-right">มิ.ย.</th>
                                <th class="text-right">ก.ค.</th>
                                <th class="text-right">ส.ค.</th>
                                <th class="text-right">ก.ย.</th>
                                <th class="text-right">ต.ค.</th>
                                <th class="text-right">พ.ย.</th>
                                <th class="text-right">ธ.ค.</th>
                                <th class="text-right font-weight-bold bg-dark">รวมทั้งปี</th>
                            </tr>
                            </thead>
                            <tbody id="branch_monthly_sales_body">
                                <!-- Populated via Ajax -->
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-success" id="btn_save_replenishment">
                        <i class="fa fa-save"></i> บันทึกข้อมูล
                    </button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fa fa-times"></i> ปิดหน้าต่าง
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/myadmin.min.js"></script>

    <!-- DataTables & Bootbox Scripts -->
    <script src="vendor/datatables/v11/bootbox.min.js"></script>
    <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>

    <script>
        $(document).ready(function () {
            // Back Button navigation
            $("#backBtn").click(function () {
                window.location.href = "dashboard";
            });

            // Initialize DataTable
            function load_analysis_table() {
                let selected_year = $('#filter_year').val();
                let selected_channel = $('#filter_channel').val();
                let selected_category = $('#filter_category').val();
                
                $('#TableAnalysisList').DataTable().clear().destroy();
                
                let formData = {
                    action: "GET_PRODUCT_ANALYSIS", 
                    year: selected_year, 
                    channel: selected_channel,
                    category: selected_category
                };
                
                $('#TableAnalysisList').DataTable({
                    'lengthMenu': [[10, 20, 50, 100], [10, 20, 50, 100]],
                    'language': {
                        search: 'ค้นหาด่วน (รหัส/ชื่อ):', 
                        lengthMenu: 'แสดง _MENU_ รายการต่อหน้า',
                        info: 'กำลังแสดงรายการที่ _START_ ถึง _END_ จากทั้งหมด _TOTAL_ รายการ',
                        infoEmpty: 'ไม่มีข้อมูลแสดง',
                        zeroRecords: "ไม่พบสินค้าตามที่ค้นหา",
                        infoFiltered: '(กรองข้อมูลจากทั้งหมด _MAX_ รายการ)',
                        paginate: {
                            previous: 'ก่อนหน้า',
                            last: 'สุดท้าย',
                            next: 'ถัดไป'
                        }
                    },
                    'processing': true,
                    'serverSide': true,
                    'serverMethod': 'post',
                    'scrollX': true,
                    'ajax': {
                        'url': 'model/manage_stock_analysis_process.php',
                        'data': formData,
                        'dataSrc': function (json) {
                            /*
                            if (json.searchSums) {
                                let sums = json.searchSums;
                                $('#sum_m1').text(sums.sum_m1);
                                $('#sum_m2').text(sums.sum_m2);
                                $('#sum_m3').text(sums.sum_m3);
                                $('#sum_m4').text(sums.sum_m4);
                                $('#sum_m5').text(sums.sum_m5);
                                $('#sum_m6').text(sums.sum_m6);
                                $('#sum_total_sales').text(sums.sum_total_sales);
                                $('#sum_stock').text(sums.sum_stock);
                                $('#sum_max').text(sums.sum_max);
                                $('#sum_min').text(sums.sum_min);
                                $('#sum_avg').text(sums.sum_avg);
                                
                                let needed_val = parseFloat(sums.sum_needed.replace(/,/g, ''));
                                if (needed_val > 0) {
                                    $('#sum_needed').html('<span class="badge-danger-custom">+' + sums.sum_needed + '</span>');
                                } else {
                                    $('#sum_needed').html('<span class="badge-success-custom">' + sums.sum_needed + '</span>');
                                }
                                
                                $('#sum_sales_340').text(sums.sum_sales_340);
                                $('#sum_sales_ratchaphruek').text(sums.sum_sales_ratchaphruek);
                                $('#sum_sales_bangyai').text(sums.sum_sales_bangyai);
                                $('#sum_sales_bangbon').text(sums.sum_sales_bangbon);
                                
                                $('#sum_stock_340').text(sums.sum_stock_340);
                                $('#sum_stock_ratchaphruek').text(sums.sum_stock_ratchaphruek);
                                $('#sum_stock_bangyai').text(sums.sum_stock_bangyai);
                                $('#sum_stock_bangbon').text(sums.sum_stock_bangbon);
                                
                                function update_badge(selector, value_str) {
                                    let val = parseFloat(value_str.replace(/,/g, ''));
                                    if (val > 0) {
                                        $(selector).html('<span class="badge-danger-custom">+' + value_str + '</span>');
                                    } else {
                                        $(selector).html('<span class="badge-success-custom">' + value_str + '</span>');
                                    }
                                }
                                update_badge('#sum_needed_340', sums.sum_needed_340);
                                update_badge('#sum_needed_ratchaphruek', sums.sum_needed_ratchaphruek);
                                update_badge('#sum_needed_bangyai', sums.sum_needed_bangyai);
                                update_badge('#sum_needed_bangbon', sums.sum_needed_bangbon);
                            }
                            */
                            return json.aaData;
                        }
                    },
                    'columns': [
                        {data: 'product_id'},
                        {data: 'name_t'},
                        {data: 'm1', className: 'text-right'},
                        {data: 'm2', className: 'text-right'},
                        {data: 'm3', className: 'text-right'},
                        {data: 'm4', className: 'text-right'},
                        {data: 'm5', className: 'text-right'},
                        {data: 'm6', className: 'text-right'},
                        {data: 'total_sales', className: 'text-right font-weight-bold'},
                        {data: 'stock', className: 'text-right'},
                        {data: 'max', className: 'text-right'},
                        {data: 'min', className: 'text-right'},
                        {data: 'avg', className: 'text-right'},
                        {
                            data: 'needed', 
                            className: 'text-right',
                            render: function(data, type, row) {
                                let needed_val = parseFloat(row.needed_raw);
                                if (needed_val > 0) {
                                    return '<span class="badge-danger-custom">+' + data + '</span>';
                                } else {
                                    return '<span class="badge-success-custom">' + data + '</span>';
                                }
                            }
                        },
                        // Branch sales
                        {data: 'sales_340', className: 'text-right'},
                        {data: 'sales_ratchaphruek', className: 'text-right'},
                        {data: 'sales_bangyai', className: 'text-right'},
                        {data: 'sales_bangbon', className: 'text-right'},
                        // Branch stocks
                        {data: 'stock_340', className: 'text-right'},
                        {data: 'stock_ratchaphruek', className: 'text-right'},
                        {data: 'stock_bangyai', className: 'text-right'},
                        {data: 'stock_bangbon', className: 'text-right'},
                        // Branch needed
                        {
                            data: 'needed_340',
                            className: 'text-right',
                            render: function(data, type, row) {
                                let val = parseFloat(row.needed_340_raw);
                                return val > 0 ? '<span class="badge-danger-custom">+' + data + '</span>' : '<span class="badge-success-custom">' + data + '</span>';
                            }
                        },
                        {
                            data: 'needed_ratchaphruek',
                            className: 'text-right',
                            render: function(data, type, row) {
                                let val = parseFloat(row.needed_ratchaphruek_raw);
                                return val > 0 ? '<span class="badge-danger-custom">+' + data + '</span>' : '<span class="badge-success-custom">' + data + '</span>';
                            }
                        },
                        {
                            data: 'needed_bangyai',
                            className: 'text-right',
                            render: function(data, type, row) {
                                let val = parseFloat(row.needed_bangyai_raw);
                                return val > 0 ? '<span class="badge-danger-custom">+' + data + '</span>' : '<span class="badge-success-custom">' + data + '</span>';
                            }
                        },
                        {
                            data: 'needed_bangbon',
                            className: 'text-right',
                            render: function(data, type, row) {
                                let val = parseFloat(row.needed_bangbon_raw);
                                return val > 0 ? '<span class="badge-danger-custom">+' + data + '</span>' : '<span class="badge-success-custom">' + data + '</span>';
                            }
                        },
                        {data: 'detail', className: 'text-center'}
                    ]
                });
            }

            // Load table on page load
            load_analysis_table();

            // Refresh button & Filter change triggers reloading
            $("#btn_refresh, #filter_year, #filter_channel, #filter_category").change(function () {
                load_analysis_table();
            });
            $("#btn_refresh").click(function () {
                load_analysis_table();
            });

            // Branch breakdown modal trigger
            $("#TableAnalysisList").on('click', '.show-branch-detail', function () {
                let sku = $(this).attr("data-sku");
                let name = $(this).attr("data-name");
                let selected_year = $('#filter_year').val();
                let selected_channel = $('#filter_channel').val();
                
                $('#modal_product_info').html('<b>รหัสสินค้า:</b> ' + sku + '<br><b>ชื่อสินค้า:</b> ' + name);
                $('#btn_save_replenishment').attr('data-sku', sku);
                $('#branch_breakdown_body').html('<tr><td colspan="5" class="text-center"><i class="fa fa-spinner fa-spin"></i> กำลังโหลดรายละเอียดข้อมูลรายสาขา...</td></tr>');
                $('#branch_monthly_sales_body').html('<tr><td colspan="14" class="text-center"><i class="fa fa-spinner fa-spin"></i> กำลังโหลดประวัติยอดขายรายเดือน...</td></tr>');
                
                $('#BranchDetailModal').modal('show');
                
                let formData = {
                    action: "GET_BRANCH_DETAILS",
                    sku: sku,
                    year: selected_year,
                    channel: selected_channel
                };
                
                $.ajax({
                    type: "POST",
                    url: 'model/manage_stock_analysis_process.php',
                    dataType: "json",
                    data: formData,
                    success: function (response) {
                        let html_summary = '';
                        let html_monthly = '';
                        let len = response.length;
                        for (let i = 0; i < len; i++) {
                            let b_name = response[i].branch_name;
                            let sales = response[i].sales;
                            let stock = response[i].stock;
                            let avg = response[i].avg;
                            let needed = response[i].needed;
                            let needed_raw = parseFloat(response[i].needed_raw);
                            let calc_needed = parseFloat(response[i].calculated_needed_raw);
                            
                            // Monthly breakdown
                            let m = response[i].months;
                            
                            let input_style = response[i].is_overridden ? 'background-color: #fff9db; border-color: #fab005; font-weight: 600;' : '';
                            let input_el = '<div class="input-group input-group-sm justify-content-end">' +
                                '<input type="number" step="0.01" class="form-control text-right branch-needed-input" data-branch="' + b_name + '" data-calc="' + calc_needed + '" value="' + needed_raw.toFixed(2) + '" style="max-width: 110px; ' + input_style + '">' +
                                '<div class="input-group-append">' +
                                '<button class="btn btn-outline-secondary btn-reset-calc" type="button" title="ใช้ค่าแนะนำตามการคำนวณ (' + calc_needed.toFixed(2) + ')" data-calc="' + calc_needed + '"><i class="fa fa-undo"></i></button>' +
                                '</div>' +
                                '</div>';
                            
                            html_summary += '<tr>' +
                                '<td><b>สาขา ' + b_name + '</b></td>' +
                                '<td class="text-right">' + stock + '</td>' +
                                '<td class="text-right">' + sales + '</td>' +
                                '<td class="text-right">' + avg + '</td>' +
                                '<td class="text-right">' + input_el + '</td>' +
                                '</tr>';
                                
                            html_monthly += '<tr>' +
                                '<td><b>สาขา ' + b_name + '</b></td>' +
                                '<td class="text-right">' + m.m1 + '</td>' +
                                '<td class="text-right">' + m.m2 + '</td>' +
                                '<td class="text-right">' + m.m3 + '</td>' +
                                '<td class="text-right">' + m.m4 + '</td>' +
                                '<td class="text-right">' + m.m5 + '</td>' +
                                '<td class="text-right">' + m.m6 + '</td>' +
                                '<td class="text-right">' + m.m7 + '</td>' +
                                '<td class="text-right">' + m.m8 + '</td>' +
                                '<td class="text-right">' + m.m9 + '</td>' +
                                '<td class="text-right">' + m.m10 + '</td>' +
                                '<td class="text-right">' + m.m11 + '</td>' +
                                '<td class="text-right">' + m.m12 + '</td>' +
                                '<td class="text-right font-weight-bold bg-light">' + sales + '</td>' +
                                '</tr>';
                        }
                        
                        if (len === 0) {
                            html_summary = '<tr><td colspan="5" class="text-center">ไม่พบข้อมูลรายสาขาของรหัสสินค้านี้</td></tr>';
                            html_monthly = '<tr><td colspan="14" class="text-center">ไม่พบข้อมูลรายสาขาของรหัสสินค้านี้</td></tr>';
                        }
                        
                        $('#branch_breakdown_body').html(html_summary);
                        $('#branch_monthly_sales_body').html(html_monthly);
                    },
                    error: function (response) {
                        $('#branch_breakdown_body').html('<tr><td colspan="5" class="text-center text-danger"><i class="fa fa-exclamation-triangle"></i> เกิดข้อผิดพลาดในการโหลดข้อมูล</td></tr>');
                        $('#branch_monthly_sales_body').html('<tr><td colspan="14" class="text-center text-danger"><i class="fa fa-exclamation-triangle"></i> เกิดข้อผิดพลาดในการโหลดข้อมูล</td></tr>');
                    }
                });
            });

            // Listen to input changes for dynamic styling
            $(document).on('input', '.branch-needed-input', function () {
                let current_val = parseFloat($(this).val());
                let calc_val = parseFloat($(this).attr('data-calc'));
                
                if (isNaN(current_val)) {
                    current_val = 0;
                }
                
                if (Math.abs(current_val - calc_val) > 0.001) {
                    $(this).css({
                        'background-color': '#fff9db',
                        'border-color': '#fab005',
                        'font-weight': '600'
                    });
                } else {
                    $(this).css({
                        'background-color': '',
                        'border-color': '',
                        'font-weight': ''
                    });
                }
            });

            // Listen to reset button click
            $(document).on('click', '.btn-reset-calc', function () {
                let parent_group = $(this).closest('.input-group');
                let input_field = parent_group.find('.branch-needed-input');
                let calc_val = parseFloat($(this).attr('data-calc'));
                
                input_field.val(calc_val.toFixed(2));
                input_field.css({
                    'background-color': '',
                    'border-color': '',
                    'font-weight': ''
                });
            });

            // Save branch replenishment button click
            $(document).on('click', '#btn_save_replenishment', function () {
                let sku = $(this).attr("data-sku");
                let selected_year = $('#filter_year').val();
                let selected_channel = $('#filter_channel').val();
                
                let branch_values = {};
                let has_empty = false;
                
                $('.branch-needed-input').each(function() {
                    let branch = $(this).attr('data-branch');
                    let val = $(this).val();
                    if (val === '') {
                        has_empty = true;
                    }
                    branch_values[branch] = val;
                });
                
                if (has_empty) {
                    bootbox.alert({
                        message: '<i class="fa fa-exclamation-circle text-danger"></i> กรุณากรอกความต้องการเพิ่มให้ครบทุกสาขา (ใส่ 0.00 หากไม่ต้องการเพิ่ม)',
                        backdrop: true
                    });
                    return;
                }
                
                let formData = {
                    action: "SAVE_BRANCH_REPLENISHMENT",
                    sku: sku,
                    year: selected_year,
                    channel: selected_channel,
                    branch_values: branch_values
                };
                
                $.ajax({
                    type: "POST",
                    url: 'model/manage_stock_analysis_process.php',
                    dataType: "json",
                    data: formData,
                    success: function (res) {
                        if (res.status === 'success') {
                            $('#BranchDetailModal').modal('hide');
                            bootbox.alert({
                                message: '<i class="fa fa-check-circle text-success"></i> ' + res.message,
                                backdrop: true
                            });
                            // Reload data table
                            $('#TableAnalysisList').DataTable().ajax.reload(null, false);
                        } else {
                            bootbox.alert({
                                message: '<i class="fa fa-exclamation-circle text-danger"></i> ' + res.message,
                                backdrop: true
                            });
                        }
                    },
                    error: function () {
                        bootbox.alert({
                            message: '<i class="fa fa-exclamation-circle text-danger"></i> เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์',
                            backdrop: true
                        });
                    }
                });
            });
        });
    </script>
    
    </body>
    </html>
<?php } ?>
