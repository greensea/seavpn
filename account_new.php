<?php

$name = $_POST['username'];
$pass = $_POST['password'];
$serviceid = (int)$_POST['serviceid'];
	
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
	$smarty->assign('msg', $msg);
	
	$smarty->display('account_new.html');
	die();
}

function  account_save() {	
	$user = user_isonline();
	
	if ($user == false) {
		account_main('You have to login before creating a new VPN account');
		die();
	}
	
	$service = db_quick_fetch('service', "WHERE id=$serviceid");
	if (count($service) <= 0) {
		account_main('The service is not exists, please contact us for help');
		die();
	}
	
	$ret = vpn_add($user, $pass, $user['id'], $serviceid);
	if ($ret != true) {
		account_main($ret);
		die();
	}
	

	
	account_pay();
}

/// 支付流程（创建订单、付款、扣账、开通）
account_pay() {
	global $smarty;
	
	/// 如果账户余额足够，则直接扣款并继续操作；如果余额不足则显示付款页面，并在付款后继续操作
	$amt = vpn_afford($serviceid, $user['email']);
	
	if ($amt < 0) {
		/// 显示付款页面
		$smarty->assign('amount', abs($amt));
	}
	
	
	
	/// 3. 账户余额足够，进行扣账
	
	
	/// 4. 开通帐号
	vpn_renew($name, $service['validtime']);
}

?>
