<?php
require_once('header.php');
require_once('order.lib.php');

confirm_main();

function confirm_main() {
	$oid = @$_GET['orderid'];
	
	if (order_dopayment($oid) == false) {
		confirm_error(_('Payment fail, please contact us for help'));
		return false;
	}
	else {
		$smarty->assign('url', "order_delivery.php?orderid=$oid");
		$smarty->display('paypal_confirm.html');
		return true;
	}
}

function confirm_error($msg) {
	global $smarty;
	
	$smarty->assign('error_msg', $msg);
	$smarty->display('error.html');
	
	die();
}

?>
