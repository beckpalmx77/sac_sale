<?php
include('includes/Header.php');
?>
    <!DOCTYPE html>
    <html lang="th">

    <style>
        body {
            font-family: 'Kanit', sans-serif;
        }
    </style>


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
                                        <button type="button" name="LottoSelBtn" id="LottoSelBtn"
                                                tabindex="4"
                                                class="form-control btn btn-primary">
                                            <span>
                                                <i class="fa fa-tags" aria-hidden="true"></i>
                                                เลือก Lotto
                                            </span>
                                    </div>

                                    <div class="form-group">
                                        <button type="button" name="LottoShoBtn" id="LottoShoBtn"
                                                tabindex="4"
                                                class="form-control btn btn-success">
                                            <span>
                                                <i class="fa fa-tags" aria-hidden="true"></i>
                                                หมายเลขเลือก Lotto
                                            </span>
                                    </div>

                                    <div class="form-group">
                                        <button type="button" name="LottoExpBtn" id="LottoExpBtn"
                                                tabindex="4"
                                                class="form-control btn btn-info">
                                            <span>
                                                <i class="fa fa-tags" aria-hidden="true"></i>
                                                Export หมายเลขเลือก Lotto
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

    <script>
        $(document).ready(function () {
            $("#LottoSelBtn").click(function () {
                window.location.href = "sac_lotto_select";
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            $("#LottoShoBtn").click(function () {
                window.location.href = "sac_lotto_show";
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            $("#LottoExpBtn").click(function () {
                window.location.href = "export_process/export_lotto_list";
            });
        });
    </script>

    </body>
    </html>


