<?php

include('config/connect_lotto_db.php');
$id = $_GET['id'];

$stmt = $conn->prepare("SELECT * FROM ims_lotto WHERE id = :id");
$stmt->bindParam(':id', $id);
$stmt->execute();
$data = $stmt->fetch(PDO::FETCH_ASSOC);

// แยกรูปภาพ
$images = explode(',', $data['lotto_file']);
$images2 = explode(',', $data['lotto_file2']);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ผลการลงทะเบียน</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>
<body>
<div class="container py-4">
    <div class="card shadow-lg">
        <div class="card-body">
            <h3 class="card-title text-center mb-4">🎯 ผลการลงทะเบียน</h3>
            <div class="mb-3">
                <strong>🏪 ชื่อร้านค้า:</strong> <?= htmlspecialchars($data['lotto_name']) ?>
            </div>
            <div class="mb-3">
                <strong>📞 โทรศัพท์:</strong> <?= htmlspecialchars($data['lotto_phone']) ?>
            </div>
            <div class="mb-3">
                <strong>🗺️ จังหวัด:</strong> <?= htmlspecialchars($data['lotto_province']) ?>
            </div>
            <div class="mb-3">
                <strong>🧑‍💼 ชื่อ Sale:</strong> <?= htmlspecialchars($data['sale_name']) ?>
            </div>
            <div class="mb-4">
                <strong>🎟️ หมายเลข:</strong> <?= htmlspecialchars($data['lotto_number']) ?>
            </div>

            <h5 class="text-center mb-3">🖼️ รูปภาพป้ายไวนิลที่บันทึก</h5>
            <div class="row text-center">
                <?php foreach ($images as $image): ?>
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <img src="uploads/<?= htmlspecialchars(trim($image)) ?>" class="card-img-top img-fluid" alt="รูปภาพ">
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <h5 class="text-center mb-3">🖼️ รูปภาพเลขหลังป้ายไวนิลที่บันทึก</h5>
            <div class="row text-center">
                <?php foreach ($images2 as $image2): ?>
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <img src="uploads/<?= htmlspecialchars(trim($image2)) ?>" class="card-img-top img-fluid" alt="รูปภาพ">
                        </div>
                    </div>
                <?php endforeach; ?>
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

<script>
    $(document).ready(function () {
        $("#backBtn").click(function () {
            window.location.href = "sac_lotto";
        });
    });
</script>

</body>
</html>
