<?php

    include('../config/connect_lotto_db.php');

    for ($loop=1;$loop<=999;$loop++) {

        $sql = "INSERT INTO ims_number_reserve(lotto_number,reserve_status)
            VALUES (:lotto_number,:reserve_status)";
        $query = $conn->prepare($sql);
        $lt_num = sprintf("%03d", $loop);
        $reserve_status = 'N';
        $query->bindParam(':lotto_number', $lt_num, PDO::PARAM_STR);
        $query->bindParam(':reserve_status', $reserve_status, PDO::PARAM_STR);
        $query->execute();
    }



