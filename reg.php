<?php
require_once('includes/header.php');
require_once('includes/recaptcha.php');

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
	
	if (!isset($_POST['invitecode'])) {
		$smarty->assign('invitecode', @$_GET['invitecode']);
	}
	
	$smarty->assign('error_msg', $error_msg);
	
	$smarty->assign('recaptcha_html', recaptcha_get_html(RECAPTCHA_PUBLIC_KEY));
	
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
	
	if (recaptcha_verify() !== true) {
		reg_main(_('The CAPTCHA you enter is not correct'));
		return false;
	}
	
	if (INVITECODE_ENABLED == 1 && reg_checkinvite(@$_POST['invitecode']) == false) {
		reg_main(_('The invite code is invalid or have been used'));
		return false;
	}
	
	$ret = user_add($email, $pass);
	if ($ret !== true) {
		reg_main("<p>$ret</p>" . _('<p>Register fail, please contact us for help if you need.</p>'));
		return false;
	}
	
	$user = user_get($email);
	if (INVITECODE_ENABLED == 1) {
		invite_use($_POST['invitecode'], $user['id']);
	}
	
	user_online($email);
	
	$smarty->assign('tip_title', _('Register successed'));
	$smarty->assign('tip_msg', _('You have registerd successfully'));
	$smarty->assign('redirect_url', 'account.php');
	$smarty->display('tip.html');
}

/**
 * 检查邀请码是否有效
 * 
 * @return	成功返回 true，失败返回 false
 */
function reg_checkinvite($code) {
	$qcode = addslashes($code);
	
	$res = db_quick_fetch('invite', "WHERE code='$qcode'");
	if (count($res) <= 0) {
		return false;
	}
	
	if ($res[0]['utime'] == null) {
		return true;
	}
	else {
		return false;
	}
}

?>
