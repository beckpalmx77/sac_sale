<?php
include('config/connect_lotto_db.php');
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
                                    <div><img src="img/logo/logo text-01.png" width="200" height="79"/></div>
                                    <h6 style="color: blue"><b>SAC LOTTO LIST</b></h6>
                                    <input type="hidden" class="form-control" id="action" name="action" value="SAVE_DATA">
                                    <input type="hidden" class="form-control" id="table_name" name="table_name" value="ims_lotto">
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <div class="form-group">
                                        <label for="lotto_name" class="control-label">ชื่อร้านค้า</label>
                                        <input type="text" class="form-control" id="lotto_name" name="lotto_name"
                                               required="true"
                                               value=""
                                               placeholder="">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="form-group">
                                        <label for="lotto_phone" class="control-label">หมายเลขโทรศัพท์</label>
                                        <input type="text" class="form-control" id="lotto_phone" name="lotto_phone"
                                               required="true"
                                               value=""
                                               placeholder="">
                                    </div>
                                </div>

                                <!--div class="form-group">
                                    <div class="form-group">
                                        <label for="lotto_province" class="control-label">จังหวัด</label>
                                        <input type="text" class="form-control" id="lotto_province" name="lotto_province"
                                               required="true"
                                               value=""
                                               placeholder="">
                                    </div>
                                </div-->

                                <div class="form-group has-success">
                                    <label class="control-label" for="lotto_province">จังหวัด</label>
                                    <div class=”form-group”>
                                        <select id="lotto_province" name="lotto_province"
                                                class="form-control" data-live-search="true"
                                                title="Please select">
                                            <option
                                                    value="<?php echo htmlentities($result1->province_name); ?>"
                                                    selected><?php echo htmlentities($result1->province_name); ?></option>
                                            <?php $sql1 = "SELECT * FROM ims_provinces WHERE 1 =1";
                                            $query1 = $conn->prepare($sql1);
                                            $query1->execute();
                                            $results1 = $query1->fetchAll(PDO::FETCH_OBJ);
                                            if ($query1->rowCount() > 0) {
                                                foreach ($results1 as $result1) { ?>
                                                    <option
                                                            value="<?php echo htmlentities($result1->province_name); ?>"><?php echo htmlentities($result1->province_name); ?></option>
                                                <?php }
                                            } ?>
                                        </select>

                                    </div>
                                    <span class="help-block"></span>
                                </div>

                                <div class="form-group has-success">
                                    <label class="control-label" for="sale_name">ชื่อ Sale</label>
                                    <div class=”form-group”>
                                        <select id="sale_name" name="sale_name"
                                                class="form-control" data-live-search="true"
                                                title="Please select">
                                            <option
                                                    value=""
                                                    selected></option>
                                            <?php $sql2 = "SELECT * FROM ims_sale_team WHERE 1 =1";
                                            $query2 = $conn->prepare($sql2);
                                            $query2->execute();
                                            $results2 = $query2->fetchAll(PDO::FETCH_OBJ);
                                            if ($query2->rowCount() > 0) {
                                                foreach ($results2 as $result2) { ?>
                                                    <option
                                                            value="<?php echo htmlentities($result2->sale_name); ?>"><?php echo htmlentities($result2->sale_name); ?></option>
                                                <?php }
                                            } ?>
                                        </select>

                                    </div>
                                    <span class="help-block"></span>
                                </div>

                                <div class="form-group">
                                    <div class="form-group">
                                        <label for="lotto_number" class="control-label">หมายเลขที่เลือก (001-999)</label>
                                        <input type="number" class="form-control" id="lotto_number" name="lotto_number"
                                               min="1" max="999" required="true"
                                               value=""
                                               placeholder="">
                                    </div>
                                </div>

                                <!--div class="form-group has-success">
                                    <label class="control-label" for="lotto_number">หมายเลขที่เลือก (1-900)</label>
                                    <div class=”form-group”>
                                        <select id="lotto_number" name="lotto_number"
                                                class="form-control" data-live-search="true"
                                                title="Please select">
                                            <option
                                                    value="<?php echo htmlentities($result->lotto_number); ?>"
                                                    selected><?php echo htmlentities($result->lotto_number); ?></option>
                                            <?php $sql1 = "SELECT * FROM ims_number_reserve WHERE reserve_status = 'N' ";
                                            $query1 = $conn->prepare($sql1);
                                            $query1->execute();
                                            $results1 = $query1->fetchAll(PDO::FETCH_OBJ);
                                            if ($query1->rowCount() > 0) {
                                                foreach ($results1 as $result1) { ?>
                                                    <option
                                                            value="<?php echo htmlentities($result1->lotto_number); ?>"><?php echo htmlentities($result1->lotto_number); ?></option>
                                                <?php }
                                            } ?>
                                        </select>

                                    </div>
                                    <span class="help-block"></span>
                                </div-->

                            </div>

                            <div class="form-group">
                                <button type="button" name="saveBtn" id="saveBtn" tabindex="4"
                                        class="form-control btn btn-primary">
                                            <span>
                                                <i class="fa fa-save" aria-hidden="true"></i>
                                                บันทึก
                                            </span>
                            </div>


                            <!--div class="form-group">
                                <button type="button" name="saveBtn1" id="saveBtn1" tabindex="4"
                                        class="form-control btn btn-primary">
                                            <span>
                                                <i class="fa fa-save" aria-hidden="true"></i>
                                                บันทึก 1
                                            </span>
                            </div-->

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

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

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

    $('#lotto_number').blur(function () {

        if ($('#lotto_number').val() >=1 && $('#lotto_number').val() <=999) {

        let action = "CHECK_NUMBER_DATA";
        let table_name = "ims_lotto";
        let cond = " WHERE lotto_number = " + $('#lotto_number').val();
        let formData = {action: action, table_name: table_name, cond: cond};
        $.ajax({
            type: "POST",
            url: 'model/lotto_process.php',
            data: formData,
            success: function (response) {
                if (response > 0) {
                    alertify.error("มีการจองหมายเลขนี้ในระบบแล้ว");
                    $('#lotto_number').val("");
                }
            },
            error: function (response) {
                alertify.error("error : " + response);
            }
        });

    } else {
            alertify.error("ป้อนเลข 001 - 999 เท่านั้น");
            $('#lotto_number').val('');
        }

    });

</script>

<script>

    $('#lotto_phone').blur(function () {

        let action = "CHECK_NUMBER_DATA";
        let table_name = "ims_lotto";
        let cond = " WHERE lotto_phone = '" + $('#lotto_phone').val() + "'" ;
        let formData = {action: action, table_name: table_name, cond: cond};
        $.ajax({
            type: "POST",
            url: 'model/lotto_process.php',
            data: formData,
            success: function (response) {
                if (response > 0) {
                    alertify.error("มีการจองโดยหมายเลขโทรศัพท์นี้ในระบบแล้ว");
                    $('#lotto_phone').val("");
                }
            },
            error: function (response) {
                alertify.error("error : " + response);
            }
        });
    });

</script>

<script>

    $('#lotto_name').blur(function () {

        let action = "CHECK_NUMBER_DATA";
        let table_name = "ims_lotto";
        let cond = " WHERE lotto_name = '" + $('#lotto_name').val() + "'" ;
        let formData = {action: action, table_name: table_name, cond: cond};
        $.ajax({
            type: "POST",
            url: 'model/lotto_process.php',
            data: formData,
            success: function (response) {
                if (response > 0) {
                    alertify.error("มีการจองโดยชื่อร้านค้านี้ในระบบแล้ว");
                    $('#lotto_name').val("");
                }
            },
            error: function (response) {
                alertify.error("error : " + response);
            }
        });
    });

</script>

<script>

    $('#saveBtn').click(function () {

        let action = "SAVE_DATA";
        let table_name = "ims_lotto";
        let lotto_name = $('#lotto_name').val();
        let lotto_phone = $('#lotto_phone').val();
        let lotto_province = $('#lotto_province').val();
        let lotto_number = $('#lotto_number').val();
        let sale_name = $('#sale_name').val();

        if (lotto_name !== "" && lotto_phone !== "" && lotto_number !== "") {

            let formData = {
                action: action,
                table_name: table_name,
                lotto_name: lotto_name,
                lotto_phone: lotto_phone,
                lotto_province: lotto_province,
                lotto_number: lotto_number,
                sale_name: sale_name
            };
            $.ajax({
                type: "POST",
                url: 'model/lotto_process.php',
                data: formData,
                success: function (response) {
                    //alertify.error("response = " + response);
                    if (response > 1) {
                        alertify.error("ไม่สารถบันทึกข้อมูลได้ กรุณาตรวจสอบข้อมูล");
                        $('#lotto_number').val("");
                    } else {
                        alertify.success("บันทึกสำเร็จ");
                    }
                },
                error: function (response) {
                    alertify.error("error : " + response);
                }
            });
        } else {
            alertify.error("error : ป้อนข้อมูลให้ครบถ้วน");
        }

    });

</script>

<script>
    $(document).ready(function(){
        $('#saveBtn1').click(function () {

            let formData = $("form").serialize();
            alert(formData);

            $.ajax({
                url: 'model/lotto_process.php',
                method: "POST",
                data: formData,
                success: function (data) {
                    alertify.success(data);
                    //$('#recordForm')[0].reset();
                    //$('#recordModal').modal('hide');
                    //$('#save').attr('disabled', false);
                    //dataRecords.ajax.reload();
                }
            })


        });
    });
</script>


</body>
</html>

