<?php
require_once('includes/header.php');

$ack = @$_GET['action'];
switch ($ack) {
	case 'save':
		reg_save();
		break;
	
	default:
		reg_main();
		break;
}
die();
	

function reg_main($error_msg = '') {
	global $smarty;
	
	foreach ($_POST as $key => $value) {
		$key = strtolower($key);
		$smarty->assign($key, $value);
	}
	$smarty->assign('error_msg', $error_msg);
	
	$smarty->display('reg.html');
}

function reg_save() {
	global $smarty;
	
	$email = @$_POST['email'];
	$pass = @$_POST['loginpass'];
	$pass2 = @$_POST['loginpass2'];
	
	if ($pass == '' || $email == '') {
		reg_main(_('Please enter email and password'));
		return false;
	}

	if (strpos($email, '@') == false || strpos($email, '.') == false) {
		reg_main(_('Invalid email address'));
		return false;
	}
	
	if ($pass != $pass2) {
		reg_main(_('Password does not match'));
		return false;
	}
	
	$ret = user_add($email, $pass);
	if ($ret !== true) {
		reg_main("<p>$ret</p>" . _('<p>Register fail, please contact us for help if you need.</p>'));
		return false;
	}
	
	user_online($email);
	
	$smarty->assign('tip_title', _('Register successed'));
	$smarty->assign('tip_msg', _('You have registerd successfully'));
	$smarty->assign('redirect_url', 'account.php');
	$smarty->display('tip.html');
}

?>
