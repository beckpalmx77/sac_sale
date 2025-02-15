<?php
include('includes/Header.php');
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <style>
        body {
            font-family: 'Kanit', sans-serif;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body class="bg-gradient-login">
<!-- Login Content -->
<div class="container-login">
    <div class="row justify-content-center">
        <div class="col-xl-6 col-lg-6 col-md-9">
            <div class="card shadow-sm my-5">
                <div class="card-body p-0">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="login-form">
                                <div class="text-center">
                                    <div><img src="img/logo/logo text-01.png" width="400" height="158"/></div>
                                </div>

                                <div class="form-group">
                                    <button type="button" id="LottoSelBtn" class="form-control btn btn-primary">
                                        <span><i class="fa fa-tags" aria-hidden="true"></i> เลือก Lotto สำหรับร้านค้า</span>
                                    </button>
                                </div>

                                <div class="form-group">
                                    <button type="button" id="LottoShoBtn" class="form-control btn btn-success">
                                        <span><i class="fa fa-tags" aria-hidden="true"></i> หมายเลขที่ร้านค้าเลือก Lotto</span>
                                    </button>
                                </div>

                                <div class="form-group">
                                    <button type="button" id="LottoExpBtn" class="form-control btn btn-info">
                                        <span><i class="fa fa-tags" aria-hidden="true"></i> Export หมายเลขเลือก Lotto</span>
                                    </button>
                                </div>

                                <div class="form-group">
                                    <button type="button" id="LottoResultBtn" class="form-control btn btn-success">
                                        <span><i class="fa fa-tags" aria-hidden="true"></i> บันทึกเลขรางวัล Lotto ที่ออก</span>
                                    </button>
                                </div>

                                <div class="form-group">
                                    <button type="button" id="LottoResultCheckBtn" class="form-control btn btn-info">
                                        <span><i class="fa fa-tags" aria-hidden="true"></i> ตรวจรางวัล Lotto</span>
                                    </button>
                                </div>

                                <div class="form-group">
                                    <button type="button" id="backBtn" class="form-control btn btn-danger">
                                        <span><i class="fa fa-reply" aria-hidden="true"></i> กลับหน้าแรก</span>
                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $("#LottoSelBtn").click(function () {
            window.location.href = "sac_lotto_select";
        });

        $("#LottoShoBtn").click(function () {
            window.location.href = "sac_lotto_show";
        });

        $("#LottoResultBtn").click(function () {
           //window.location.href = "sac_lotto_result_period";
            window.location.href = "manage-period-lotto";
        });

        $("#LottoResultCheckBtn").click(function () {
            //window.location.href = "sac_lotto_result_period";
            window.location.href = "result_lotto";
        });

        $("#LottoExpBtn").click(function () {
            window.location.href = "export_process/export_lotto_list";
        });

        $("#backBtn").click(function () {
            window.location.replace("sac_lotto"); // ใช้ replace แทน href เพื่อป้องกันการย้อนกลับ
        });
    });
</script>

</body>
</html>
