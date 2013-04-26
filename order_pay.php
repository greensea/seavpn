<?php
require_once('includes/header.php');
require_once('includes/order.lib.php');

$oid = (int)@$_POST['id'];

/// 先检查这个订单是不是已经支付过了
$orders = db_quick_fetch('order', "WHERE id=$oid");
if (count($orders) <= 0) {
	pay_error('No such order');
	die();
}
$order = $orders[0];

if ($order['paidtime'] != null) {
	$smarty->assign('tip_title', _('Error'));
	$smarty->assign('tip_msg', _('This order is already paid'));
	$smarty->assign('redirect_url', $url);
	$smarty->assign('redirect_delay', 1);
	$smarty->display('tip.html');
	die();
}
	

/// 生成订单并跳转到支付页面

$order = order_request($oid);

if ($order === false) {
	pay_error(_('An error occured, please contact us for help'));
	die();
}

order_redirect($oid);



function pay_error($msg) {
	global $smarty;
	
	$smarty->assign(array('tip_title' => _('ERROR'),
							'tip_msg' => _($msg)
					));
	$smarty->display('tip.html');
}

?>
