<?php
require_once('includes/header.php');
require_once('includes/order.lib.php');
require_once('includes/paypal.lib.php');
require_once('includes/vpn.php');

$token = @$_GET['token'];
$token = addslashes($token);

define(CANTFINISH, 'We can not finish you payment, please contact us for help');

$payments = db_quick_fetch('payment', "WHERE token='$token'");

if (count($payments) <= 0) {
	vpn_log("Can not find payment record which token='$token'");
	pay_error(CANTFINISH);
	die();
}

$payment = $payments[0];
$oid = $payment['orderid'];

/// 检查该笔交易是不是已经处理过了
$orders = db_quick_fetch('order', "WHERE id=$oid");
if (count($orders) <= 0) {
	pay_error("No such order with payment token $token");
	die();
}
$order = $orders[0];

if ($order['paidtime'] == null) {
	/// 处理订单
	$ret = done_transaction($oid, $order['vpnid']);
	
	if ($ret === false) {
		pay_error(CANTFINISH);
	}
}

done_show();

die();

/// 完成交易流水操作
function done_transaction($oid, $vpnid) {
	$ret = order_dopayment($oid);
	if ($ret === false) {
		pay_error(CANTFINISH);
		die();
	}

	/// 支付成功，开通服务并显示成功信息
	$sql = "SELECT * FROM service WHERE id IN (SELECT serviceid FROM `order` WHERE id={$oid})";

	$res = db_query($sql);
	if ($res === false) {
		pay_error(_("Can not find service correlate to order.id=$oid, payment token=$token"));
		die();
	}

	$service = db_fetch_array($res);

	/// 查找对应的 VPN 帐号
	$vpnid = (int)$vpnid;
	$vpns = db_quick_fetch('vpnaccount', "WHERE id=$vpnid");
	if (count($vpns) <= 0) {
		vpn_log("No vpnid $vpnid found for order $oid");
		return false;
	}
	$vpn = $vpns[0];


	/// 下面的代码和 account_new.php 中的代码一致

	/// 3. 账户余额足够，开通帐号
	vpn_renew($vpn['username'], $service['duration'], $service['radiusgroup']);
		
	/// 4. 发货（在 raidus 中设置帐号），并扣款
	order_delivery($oid);
	
	return true;
}

function done_show() {
	global $smarty;
	
	$smarty->assign('tip_title', _('Success'));
	$smarty->assign('tip_msg', _('Thank you for purchase, now you can go to My Account page to view you VPN account'));
	$smarty->assign('redirect_url', 'account.php');
	$smarty->display('tip.html');
}

function pay_error($msg) {
	global $smarty;
	
	$smarty->assign('tip_title', _('ERROR'));
	$smarty->assign('tip_msg', $msg);
	$smarty->display('tip.html');
}

?>
