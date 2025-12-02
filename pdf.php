<?php 

require('./asset_config.php');
require('./assets/tcpdf/tcpdf.php');
require('./app/init.php');
if (!authenticate()) {
    header("Location: ".baseUri()."/login ");
    exit; // Important to exit to prevent further execution
}
 
if(isset($_GET['print'])) {
	if($_GET['print'] == 'payslip') {
		require('prints/payslip.php');
	} else if($_GET['print'] == 'employees') {
		require('prints/allEmployees.php');
	} else if($_GET['print'] == 'absence') {
		require('prints/absence.php');
	} else if($_GET['print'] == 'payroll') {
		require('prints/payroll_report.php');
	} else if($_GET['print'] == 'componsation') {
		require('prints/componsation.php');
	} else if($_GET['print'] == 'deductions') {
		require('prints/deductions.php');
	} else if($_GET['print'] == 'taxation') {
		require('prints/tax_report.php');
	} 




} else {header("Location: /");}

?>