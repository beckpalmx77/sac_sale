<?php
require_once 'vendor/mobiledetect/mobiledetectlib/Mobile_Detect.php';
$detect = new Mobile_Detect;

if ( $detect->isMobile() ) {
    //include('cockpit_sale_page_mob.php');
    include('cockpit_sale_page.php');
} else {
    include('cockpit_sale_page.php');
}

?>
