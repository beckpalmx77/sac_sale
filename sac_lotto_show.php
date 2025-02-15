<?php
include('includes/Header.php');
require_once 'config/connect_lotto_db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-F3w7mX95PdgyTmZZMECAngseQB83DfGTowi0iMjiWaeVhAn4FJkqJByhZMI3AhiU" crossorigin="anonymous">
    <!-- datatable -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.css">
    <script type="text/javascript" charset="utf8" src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script type="text/javascript" charset="utf8"
            src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.js"></script>
    <title>SAC LOTTO LIST</title>
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-md-12"><br>
            <div><img src="img/logo/logo text-01.png" width="200" height="79"/></div>
            <h6 style="color: blue"><b>SAC LOTTO LIST</b></h6>
            <div class="form-group">
                <button type="button" name="backBtn" id="backBtn" tabindex="4" class="form-control btn btn-danger">
                    <span>
                        <i class="fa fa-reply" aria-hidden="true"></i>
                        กลับหน้าแรก
                    </span>
                </button>
            </div>
            <table id="DataTable" class="display table table-striped table-hover table-responsive table-bordered">
                <thead>
                <tr>
                    <th width="5%">ลำดับ</th>
                    <th width="25%">ชื่อร้าน</th>
                    <th width="10%">หมายเลขโทรศัพท์</th>
                    <th width="15%">จังหวัด</th>
                    <th width="15%">หมายเลขที่เลือก</th>
                    <th width="15%">ชื่อ Sale</th>
                    <th width="15%">การอนุมัติ</th>
                    <th width="15%">วันที่บันทึก</th>
                    <th width="15%">รูปภาพป้ายไวนิล</th> <!-- คอลัมน์ใหม่ -->
                    <th width="15%">รูปภาพเลขหลังป้ายไวนิล</th> <!-- คอลัมน์ใหม่ -->
                    <th width="10%">Action</th> <!-- ปุ่ม Update -->
                </tr>
                </thead>
                <tbody>
                <?php
                $stmt = $conn->prepare("SELECT * FROM ims_lotto ORDER BY id DESC ");
                $stmt->execute();
                $result = $stmt->fetchAll();
                $line_no = 0;
                foreach ($result as $rows) {
                    $line_no++;
                    ?>
                    <tr>
                        <td><?= $line_no; ?></td>
                        <td><?= $rows['lotto_name']; ?></td>
                        <td><?= $rows['lotto_phone']; ?></td>
                        <td><?= $rows['lotto_province']; ?></td>
                        <td><?= $rows['lotto_number']; ?></td>
                        <td><?= $rows['sale_name']; ?></td>
                        <td style="color: <?= $rows['approve_status'] == 'Y' ? 'green' : ($rows['approve_status'] == 'N' ? 'gray' : 'black'); ?>; text-align: center;">
                            <?= $rows['approve_status'] == 'Y' ? 'อนุมัติ' : ($rows['approve_status'] == 'N' ? 'ยังไม่อนุมัติ' : ''); ?>
                        </td>
                        <td><?= $rows['create_date']; ?></td>
                        <td>
                            <?php
                            // ตรวจสอบว่ามีไฟล์ภาพหรือไม่ และแสดงลิงก์
                            if (!empty($rows['lotto_file'])) {
                                $files = explode(",", $rows['lotto_file']); // แยกไฟล์หลายไฟล์
                                $index = 1; // เริ่มต้นที่ลำดับ 1
                                foreach ($files as $file) {
                                    echo '<a href="uploads/' . $file . '" target="_blank">รูปที่ ' . $index . '</a><br>';
                                    $index++; // เพิ่มลำดับทุกครั้ง
                                }
                            } else {
                                echo "ไม่มีรูปภาพ";
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            // ตรวจสอบว่ามีไฟล์ภาพหรือไม่ และแสดงลิงก์
                            if (!empty($rows['lotto_file2'])) {
                                $files2 = explode(",", $rows['lotto_file2']); // แยกไฟล์หลายไฟล์
                                $index2 = 1; // เริ่มต้นที่ลำดับ 1
                                foreach ($files2 as $file2) {
                                    echo '<a href="uploads/' . $file2 . '" target="_blank">รูปที่ ' . $index2 . '</a><br>';
                                    $index2++; // เพิ่มลำดับทุกครั้ง
                                }
                            } else {
                                echo "ไม่มีรูปภาพ";
                            }
                            ?>
                        </td>
                        <td>
                            <!-- ปุ่ม Update -->
                            <button class="btn btn-outline-success" onclick="openPopup(<?= $rows['id']; ?>)">Update
                                Approve
                                Status
                            </button>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal for Update Approve Status -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approveModalLabel">Update Approve Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <input type="hidden" id="lotto_id">
                    <div class="mb-3">
                        <label for="approve_status" class="form-label">Approve Status</label>
                        <select class="form-select" id="approve_status">
                            <option value="N">ไม่อนุมัติ</option>
                            <option value="Y">อนุมัติ</option>
                        </select>
                    </div>
                    <button type="button" class="btn btn-primary" onclick="saveApproveStatus()">บันทึก (Save)</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $('#DataTable').DataTable();
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
    // Function to open the popup and update approve status
    function openPopup(id) {
        // Fetch current approve status using AJAX
        $.ajax({
            url: 'model/get_approve_status.php',
            type: 'GET',
            data: {id: id},
            success: function (data) {
                // Set current approve status in the input field
                $('#approve_status').val(data);
                $('#lotto_id').val(id);
                $('#approveModal').modal('show');
            }
        });
    }
</script>

<script>
    // Function to save the updated approve status
    function saveApproveStatus() {
        let approveStatus = $('#approve_status').val();
        let lottoId = $('#lotto_id').val();

        $.ajax({
            url: 'model/update_approve_status.php',
            type: 'POST',
            data: {approve_status: approveStatus, id: lottoId},
            success: function (response) {
                if (response == 'success') {
                    //alert('Approve status updated successfully');
                    location.reload(); // Reload the page to reflect changes
                } else {
                    alert('Error updating approve status');
                }
            }
        });
    }

</script>

</body>
</html>
