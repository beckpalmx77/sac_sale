<?php
include('config/connect_lotto_db.php');
include('includes/Header.php');
?>

<!DOCTYPE html>
<html lang="th">
<body class="bg-gradient-login" id="page-top">

<form method="post" id="lotto_form" name="lotto_form" enctype="multipart/form-data">
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
                                        <h6 style="color: blue"><b>SAC LOTTO SELECT</b></h6>
                                        <input type="hidden" class="form-control" id="action" name="action"
                                               value="SAVE_DATA">
                                        <input type="hidden" class="form-control" id="table_name" name="table_name"
                                               value="ims_lotto">
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="lotto_name" class="control-label">ชื่อร้านค้า</label>
                                                <input type="text" class="form-control" id="lotto_name"
                                                       name="lotto_name"
                                                       required="true"
                                                       value=""
                                                       placeholder="">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="lotto_phone" class="control-label">หมายเลขโทรศัพท์</label>
                                                <input type="number" class="form-control" id="lotto_phone"
                                                       name="lotto_phone"
                                                       required="true"
                                                       value=""
                                                       placeholder="">
                                            </div>
                                        </div>
                                    </div>


                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label" for="lotto_province">จังหวัด</label>
                                                <select id="lotto_province" name="lotto_province" required="true"
                                                        class="form-control" data-live-search="true"
                                                        title="Please select">
                                                    <option value="" selected></option>
                                                    <?php
                                                    $sql1 = "SELECT * FROM ims_provinces WHERE 1 =1";
                                                    $query1 = $conn->prepare($sql1);
                                                    $query1->execute();
                                                    $results1 = $query1->fetchAll(PDO::FETCH_OBJ);
                                                    if ($query1->rowCount() > 0) {
                                                        foreach ($results1 as $result1) { ?>
                                                            <option value="<?php echo htmlentities($result1->province_name); ?>"><?php echo htmlentities($result1->province_name); ?></option>
                                                        <?php }
                                                    } ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label" for="sale_name">ชื่อ Sale</label>
                                                <select id="sale_name" name="sale_name" required="true"
                                                        class="form-control" data-live-search="true"
                                                        title="Please select">
                                                    <option value="" selected></option>
                                                    <?php
                                                    $sql2 = "SELECT * FROM ims_sale_team WHERE 1 =1 ORDER BY id ";
                                                    $query2 = $conn->prepare($sql2);
                                                    $query2->execute();
                                                    $results2 = $query2->fetchAll(PDO::FETCH_OBJ);
                                                    if ($query2->rowCount() > 0) {
                                                        foreach ($results2 as $result2) { ?>
                                                            <option value="<?php echo htmlentities($result2->sale_name); ?>"><?php echo htmlentities($result2->sale_name); ?></option>
                                                        <?php }
                                                    } ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="form-group">
                                            <label for="lotto_number" class="control-label">หมายเลขที่เลือก
                                                (001-999)</label>
                                            <input type="number" class="form-control" id="lotto_number"
                                                   name="lotto_number"
                                                   min="1" max="999" required="true"
                                                   value=""
                                                   placeholder="">
                                        </div>
                                    </div>

                                    <!-- อัปโหลดรูปภาพ -->
                                    <div class="form-group">
                                        <label for="lotto_file" class="control-label">อัปโหลดรูปภาพ</label>
                                        <input type="file" class="form-control" id="lotto_file" name="lotto_file"
                                               accept="image/*">
                                        <div class="preview mt-2">
                                            <img id="previewImage" src="#" alt="Preview"
                                                 style="max-width: 100%; display: none;">
                                        </div>
                                    </div>

                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <button type="button" name="saveBtn" id="saveBtn" tabindex="4"
                                                class="form-control btn btn-primary">
                                            <span>
                                                <i class="fa fa-save" aria-hidden="true"></i>
                                                บันทึก
                                            </span>
                                    </div>
                                </div>
                                <div class="col-md-12">
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
    function pad(n, width, fill) {
        n = n + '';
        return n.length >= width ? n : new Array(width - n.length + 1).join(fill) + n;
    }
</script>


<script>
    $(document).ready(function () {

        $("#lotto_number").on("change blur", function () {

            let width = 3;
            let fil = 0;
            $('#lotto_number').val(pad($('#lotto_number').val(), width, fil));
            if ($('#lotto_number').val() >= 1 && $('#lotto_number').val() <= 999) {
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

    });

</script>

<script>

    $('#lotto_phone').blur(function () {

        let action = "CHECK_NUMBER_DATA";
        let table_name = "ims_lotto";
        let cond = " WHERE lotto_phone = '" + $('#lotto_phone').val() + "'";
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
        let cond = " WHERE lotto_name = '" + $('#lotto_name').val() + "'";
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
        let file_data = $('#lotto_file')[0].files[0]; // ดึงไฟล์จาก input

        if (lotto_name !== "" && lotto_phone !== "" && lotto_province !== "" && sale_name !== "" && lotto_number !== "") {
            let formData = new FormData();
            formData.append("action", action);
            formData.append("table_name", table_name);
            formData.append("lotto_name", lotto_name);
            formData.append("lotto_phone", lotto_phone);
            formData.append("lotto_province", lotto_province);
            formData.append("lotto_number", lotto_number);
            formData.append("sale_name", sale_name);
            if (file_data) {
                formData.append("lotto_file", file_data); // แนบไฟล์เข้าไป
            }
            //alert(formData);
            $.ajax({
                type: "POST",
                url: 'model/lotto_process_new.php',
                data: formData,
                contentType: false,  // ไม่ต้องกำหนด Content-Type ให้เป็น multipart/form-data
                processData: false,  // ไม่ต้องแปลงข้อมูล
                success: function (response) {
                    if (response > 1) {
                        alertify.error("ไม่สามารถบันทึกข้อมูลได้ กรุณาตรวจสอบข้อมูล");
                        $('#lotto_number').val("");
                    } else {
                        alertify.success("บันทึกสำเร็จ");
                        $('#lotto_form')[0].reset(); // ล้างฟอร์มหลังจากบันทึก
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
    $(document).ready(function () {
        $('#lotto_file').change(function (event) {
            let reader = new FileReader();
            reader.onload = function (e) {
                $('#previewImage').attr('src', e.target.result).show();
            }
            reader.readAsDataURL(event.target.files[0]);
        });
    });
</script>

</body>
</html>

