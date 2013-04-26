<?php
require_once('includes/header.php');
require_once('includes/vpn.php');
require_once('includes/order.lib.php');

switch (@$_GET['action']) {
	case 'save':
		account_save();
		break;
	default:
		account_main();
		break;
}
die();

function account_main($msg = '') {
	global $smarty;
	
	foreach ($_POST as $key => $value) {
		$smarty->assign($key, $value);
	}
	$smarty->assign('error_msg', $msg);
	
	$services = db_quick_fetch('service', 'WHERE enabled<>0');
	$smarty->assign('services', $services);

	$smarty->display('account_new.html');
	die();
}

function account_save() {
	$name = $_POST['username'];
	$pass = $_POST['password'];
	$serviceid = (int)$_POST['serviceid'];

	$user = user_isonline();

	if ($user === false) {
		account_main('You have to login before creating a new VPN account');
		die();
	}
	
	if ($name == '' || $pass == '') {
		account_main('Please enter VPN username and password');
		die();
	}
	
	$service = db_quick_fetch('service', "WHERE id=$serviceid");
	if (count($service) <= 0) {
		account_main('Please select a service');
		die();
	}
	
	$ret = vpn_add($name, $pass, $user['id'], $serviceid);
	if ($ret !== true) {
		account_main($ret);
		die();
	}
	
	account_pay($name, $pass, $serviceid);
}

/// 支付流程（创建订单、付款、扣账、开通）
function account_pay($name, $pass, $serviceid) {
	global $smarty;
	
	/// 如果账户余额足够，则直接扣款并继续操作；如果余额不足则显示付款页面，并在付款后继续操作
	$amt = vpn_afford($serviceid, $user['email']);
	
	$services = db_quick_fetch('service', "WHERE id=$serviceid");
	if (count($services) <= 0) {
		vpn_log("Error: No such service id: $serviceid");
	}
	$service = $services[0];
	
	if ($amt < 0) {
		/// 显示付款页面
		$smarty->assign('amount', abs($amt));
		$smarty->assign('service', $service);
		$smaryt->display('order_preview.html');
		die();
	}
	
	/// 3. 账户余额足够，开通帐号，并扣账
	vpn_renew($name, $service['validtime']);
	order_delivery($oid);
	
	$smarty->assign('tip_title', _('Success'));
	$smarty->assign('tip_msg', _('Thank you for purchase, now you can go to My VPN page to view you VPN account'));
	$smarty->assign('redirect_url', 'account.php');
	$smarty->display('tip.html');
}

?>
