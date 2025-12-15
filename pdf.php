<?php 

require('./public/tcpdf/tcpdf.php');
require('./app/init.php');
if (!authenticate()) {
    header("Location: ".baseUri()."/login ");
    exit; // Important to exit to prevent further execution
}
 
if(isset($_GET['print'])) {
	if($_GET['print'] == 'lease') {
		require('prints/print_lease.php');
	}




} else {header("Location: /");}

?>