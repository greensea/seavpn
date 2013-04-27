<?php
require_once('includes/header.php');
require_once('includes/order.lib.php');
require_once('includes/vpn.php');

$aid = @$_GET['vpnid'];
$aid = (int)$aid;

$user = user_isonline();
if ($user === false) {
	renew_error(_('You have to login before renew your VPN account'));
	die();
}

$accounts = db_quick_fetch('vpnaccount', "WHERE id=$aid AND uid={$user['id']}");
if (count($accounts) <= 0) {
	renew_error(_('VPN account not exists'));
	die();
}
$account = $accounts[0];

$services = db_quick_fetch('service', "WHERE id IN (SELECT serviceid FROM (SELECT DISTINCT serviceid FROM `order` WHERE NOT ISNULL(paidtime) AND vpnid={$account['id']} ORDER BY id DESC LIMIT 1) AS t)");
if (count($services) <= 0) {
	vpn_log("Could not find correlate service id for vpnaccount id {$account['id']}");
	renew_error(_('Can not renew, please contact us for help'));
	die();
}
$service = $services[0];

/// 开始支付过程
$order = order_new($service['id']);
if ($order === false) {
	vpn_log("Can not get order via order_new('{$service['id']}')");
	renew_error(_('Can not renew, please contact us for help'));
	die();
}

$amt = vpn_afford($service['id'], $user['email']);

if ($amt < 0) {
	/// 余额不足时，显示付款页面，并在付款成功后继续开通帐号操作
	//$smarty->assign('amount', abs($amt));
	//$smarty->assign('service', $service);
	$url = "order_preview.php?id={$order['orderid']}";
	header("Location: $url");
	
	$smarty->assign('redirect_url', $url);
	$smarty->assign('tip_title', _('Redirect'));
	$smarty->assign('tip_msg', _('Redirecting...'));
	$smarty->display('tip.html');
	die();
}


/// 3. 账户余额足够，续费
//print_r($name);
//print_r($service);
vpn_renew($name, $service['duration']);

/// 4. 发货（扣款）
order_delivery($order['orderid']);

$smarty->assign('tip_title', _('Renew Success'));
$smarty->assign('tip_msg', _('Thank you for purchase, now you can go to VPN Account page to view you VPN account'));
$smarty->assign('redirect_url', 'account.php');
$smarty->display('tip.html');

die();

function renew_error($msg) {
	global $smarty;
	
	$smarty->assign('tip_title', _('ERROR'));
	$smarty->assign('tip_msg', $msg);
	
	$smarty->display('tip.html');
}


?>
