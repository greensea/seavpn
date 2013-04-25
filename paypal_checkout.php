<?php
require_once('includes/header.php');
require_once('includes/order.lib.php');

payment_main();

function payment_main() {
	$user = user_isonline();
	if ($user === false) {
		payment_die(_('Please login before checkout'));
	}
	
	$sid = @$_GET['serviceid'];
	$sid = (int)$sid;
	
	$sql = "SELECT * FROM service WHERE id=$sid";
	
	$res = db_query($sql);
	if ($res == false || db_num_rows($res) == 0) {
		payment_die(_('We have no this service'));
	}
	
	$arr = db_fetch_array($res);
	
	$orderarr = order_new($sid);
	if ($orderarr == false) {
		payment_die(_('Checkout fail, please contact us for help'));
	}
	
	/// 使用 PayPal 进行支付
	$ret = paypal_new_payment($orderarr['orderid'], $amount);
	if ($ret == false) {
		payment_die(_('Checkout fail, please contact us for help'));
	}
	
	payment_redirect(PAYPAL_REDIRECTURL . '?token=' . $ret['token']);
}

function payment_die($msg) {
	global $smarty;
	
	$smarty->assign('error_msg', $msg);
	$smarty->display('error.html');
	
	die();
}

function payment_redirect($url) {
	global $smarty;
	
	$url = urlencode($url);
	
	header("Location: $url");
	
	$smarty->assign('redirect_url', $url);
	$smarty->display('paypal_redirect.html');
	
	die();
}
?>
