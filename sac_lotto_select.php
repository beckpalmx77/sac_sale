<?php
include('includes/Header.php');
?>

<!DOCTYPE html>
<html lang="th">
<body class="bg-gradient-login" id="page-top">

<form id="form">
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
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <div class="form-group">
                                        <label for="lotto_name" class="control-label">ชื่อร้านค้า</label>
                                        <input type="text" class="form-control" id="lotto_name"
                                               value=""
                                               placeholder="">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="form-group">
                                        <label for="lotto_phone" class="control-label">หมายเลขโทรศัพท์</label>
                                        <input type="text" class="form-control" id="lotto_phone"
                                               value=""
                                               placeholder="">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="form-group">
                                        <label for="province" class="control-label">จังหวัด</label>
                                        <input type="text" class="form-control" id="province"
                                               value=""
                                               placeholder="">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="form-group">
                                        <label for="lotto_number" class="control-label">หมายเลขที่เลือก (1-900)</label>
                                        <input type="number" class="form-control" id="lotto_number"
                                               value=""
                                               placeholder="">
                                    </div>
                                </div>

                            </div>

                            <div class="form-group">
                                <button type="submit" name="saveBtn" id="saveBtn" tabindex="4"
                                        class="form-control btn btn-primary">
                                            <span>
                                                <i class="fa fa-save" aria-hidden="true"></i>
                                                บันทึก
                                            </span>
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

</form>

<!-- Scroll to top -->
<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>


<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="js/myadmin.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"
        integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p"
        crossorigin="anonymous"></script>


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
        $("#backBtn").click(function () {
            window.location.href = "sac_lotto";
        });
    });
</script>

<script>

    $('#email').blur(function () {

        let action = "GET_COUNT_RECORDS_COND";
        let table_name = "ims_lotto";
        let cond = " WHERE lotto_number = " + $('#lotto_number').val() ;
        let formData = {action: action, table_name: table_name, cond: cond};
        $.ajax({
            type: "POST",
            url: 'model/lotto_process.php',
            data: formData,
            success: function (response) {
                if (response > 0) {
                    alertify.error("มีการจองหมายเลขนี้ในระบบแล้ว");
                    $('#user_id').val("");
                }
            },
            error: function (response) {
                alertify.error("error : " + response);
            }
        });
    });

</script>






</body>
</html>

