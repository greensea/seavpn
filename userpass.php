<?php
require_once('includes/header.php');

$user = user_isonline();
if ($user === false) {
	pass_main(_('You have to login before change your login password'));
	return false;
}

$ack = @$_GET['action'];
switch ($ack) {
	case 'save':
		pass_save($user);
		break;
	
	default:
		pass_main();
		break;
}
die();
	

function pass_main($error_msg = '') {
	global $smarty;
	
	foreach ($_POST as $key => $value) {
		$key = strtolower($key);
		$smarty->assign($key, $value);
	}
	$smarty->assign('error_msg', $error_msg);
	
	$smarty->display('userpass.html');
}

function pass_save() {
	global $smarty;
	
	$user = user_isonline();
	
	$oldpass = @$_POST['oldpass'];
	$pass = @$_POST['loginpass'];
	$pass2 = @$_POST['loginpass2'];
	
	
	if ($pass == '') {
		pass_main(_('Please enter new password'));
		return false;
	}

	if ($pass != $pass2) {
		pass_main(_('New password does not match'));
		return false;
	}
	
	if (user_encrypt($oldpass) != $user['loginpass']) {
		pass_main(_('Current password is not correct'));
		return false;
	}
	
	$ret = user_passwd($user['id'], $pass);
	if ($ret !== true) {
		vpn_log($ret);
		pass_main(_("<p>$ret</p>" . '<p>There is an error occur, please contact us for help if you need.</p>'));
		return false;
	}
	
	
	$smarty->assign('tip_title', _('Successed'));
	$smarty->assign('tip_msg', _('Login password successfully changed'));
	$smarty->assign('redirect_url', 'account.php');
	$smarty->display('tip.html');
}

?>
