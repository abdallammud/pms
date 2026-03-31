<?php 

require('./public/tcpdf/tcpdf.php');
require('./app/init.php');
if (!authenticate()) {
    header("Location: ".baseUri()."/login ");
    exit; // Important to exit to prevent further execution
}
 
if (isset($_GET['print'])) {
    $print = $_GET['print'];

    if ($print == 'lease') {
        require('prints/print_lease.php');
    } elseif (in_array($print, ['rent_collection','unit_occupancy','tenant_report','outstanding_balance','income_expense','maintenance_report','maintenance_expense'])) {
        require('prints/report_pdf.php');
    } elseif ($print == 'invoice') {
        require('prints/print_invoice.php');
    } else {
        header("Location: /");
    }
} else {
    header("Location: /");
}

?>