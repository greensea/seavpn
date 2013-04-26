<?php
require_once('includes/header.php');

$oid = (int)@$_GET['id'];
$user = user_isonline();

if ($user === false) {
	preview_error(_('You have to login to view your order'));
	die();
}

$sql = "SELECT `order`.*, service.desc, service.price FROM `order` LEFT JOIN service ON `order`.serviceid=service.id WHERE `order`.id=$oid";
$res = db_query($sql);

if ($res === false || db_num_rows($res) <= 0) {
	preview_error(_('Order does not exists'));
	die();
}

$order = db_fetch_array($res);

if ($order['uid'] != $user['id']) {
	preview_error(_('Order does not exists'));
}


$order['price'] = sprintf('%0.2f', $order['price'] / 100.0);
$order['amount'] = sprintf('%0.2f', $order['amount'] / 100.0);
$user['balance'] = sprintf('%0.2f', $user['balance'] / 100.0);
$user['credit'] = sprintf('%0.2f', $user['credit'] / 100.0);

$smarty->assign('order', $order);
$smarty->assign('user', $user);
$smarty->display('order_preview.html');



	

function preview_error($msg) {
	global $smarty;
	
	$smarty->assign('tip_title', _('ERROR'));
	$smarty->assign('tip_msg', $msg);
	$smarty->display('tip.html');
}

?>
