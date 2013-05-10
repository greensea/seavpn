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
	
	$user = user_isonline();
	
	/// 如果账户余额足够，则直接扣款并继续操作；如果余额不足则显示付款页面，并在付款后继续操作
	$amt = vpn_afford($serviceid, $user['email']);
	
	$services = db_quick_fetch('service', "WHERE id=$serviceid");
	if (count($services) <= 0) {
		vpn_log("Error: No such service id: $serviceid");
	}
	$service = $services[0];
	

	/// 创建订单
	$order = null;
	if ($amt < 0) {
		$order = order_new($serviceid, abs($amt));
	}
	else {
		$order = order_new($serviceid);
	}
	
	if ($order === false) {
		vpn_log("Can not create order($serviceid, $amt)");
		$smarty->assign('tip_title', _('An error occur'));
		$smarty->assign('tip_msg', _('Can not create order, please contact us for help'));
		$smarty->display('tip.html');
		die();
	}
	
	/// 向 order 表中增加 VPN 帐号信息
	$qname = addslashes($name);
	
	$vpns = db_quick_fetch('vpnaccount', "WHERE username='$qname'");
	if (count($vpns) <= 0) {
		vpn_log("No VPN username `$name' in vpnaccount table");
	}
	
	db_quick_update('order', "WHERE id={$order['orderid']}", array('vpnid' => $vpns[0]['id']));
	
	
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
	
	
	/// 3. 账户余额足够，开通帐号
	//print_r($name);
	//print_r($service);
	vpn_renew($name, $service['duration'], $service['radiusgroup']);
	
	/// 4. 发货（扣款）
	order_delivery($order['orderid']);
	
	$smarty->assign('tip_title', _('Success'));
	$smarty->assign('tip_msg', _('Thank you for purchase, now you can go to My Account page to view you VPN account'));
	$smarty->assign('redirect_url', 'account.php');
	$smarty->display('tip.html');
}

?>
