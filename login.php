<?php
require_once('includes/header.php');
require_once('includes/recaptcha.php');

$smarty->assign('title', _('Login - SeaVPN'));

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
	case 'chk':
		login_check();
		break;
		
	default:
		login_main();
		break;
}

function login_main() {
	global $smarty;
	
	if (login_needcaptcha()) {
		$smarty->assign('recaptcha_html', recaptcha_get_html(RECAPTCHA_PUBLIC_KEY));
	}
	
	$smarty->display('templates/login.html');
}

function login_check() {
	global $smarty;
	
	$email = @$_POST['email'];
	$pass = @$_POST['loginpass'];
	
	if (login_needcaptcha()) {
		if (recaptcha_verify() !== true) {
			login_error(_('The CAPTCHA you entered is incorrect'));
			die();
		}
	}
	
	$ret = user_verify($email, $pass);
	
	if ($ret !== true) {
		login_incfail();
		
		login_error($ret);
		
		die();
	}
	else {
		user_online($email);
		
		login_resetfail();
		
		$url = 'account.php';
		
		header("Location: $url");
		
		$smarty->assign('url', $url);
		$smarty->display('templates/redirect.html');
	}
}


/**
 * 设置当前 IP 访客的登录失败次数 +1
 */
function login_incfail() {
	$ip = $_SERVER['REMOTE_ADDR'];

	$num = cache_get("login_fails_$ip");
	if (!$num) {
		$num = 1;
	}
	else {
		$num++;
	}
	
	cache_set("login_fails_$ip", $num);
	
	if ($num >= LOGIN_MAXTRIES_WITHOUT_CAPTCHA) {
		cache_set("need_captcha_$ip", time(), LOGIN_FAIL_ANNEAL_TIME);
	}
}

/**
 * 清除当前 IP 访客的登录失败次数。
 * 如果登录失败次数是 0，不管；如果不是 0，则将其设为 0，并设置一个超时时间
 */
function login_resetfail() {
	$ip = $_SERVER['REMOTE_ADDR'];
	
	$num = cache_get("login_fails_$ip");
	if ($num != 0) {
		echo "set it to expire";
		cache_set("login_fails_$ip", 0, LOGIN_FAIL_ANNEAL_TIME);
	}
}

/**
 * 判断当前访客是否必须输入验证码
 */
function login_needcaptcha() {
	$ip = $_SERVER['REMOTE_ADDR'];
	
	if (cache_isset("need_captcha_$ip")) {
		return true;
	}
	else {
		return false;
	}
}

/**
 * 获取当前 IP 访客的登录失败次数
 */
function login_failtimes() {
	$ip = $_SERVER['REMOTE_ADDR'];
	$num = cache_get("login_fails_$ip");
	
	if (!$num) {
		return 0;
	}
	else {
		return $num;
	}
}


function login_error($msg) {
	global $smarty;
	
	$smarty->assign('error_msg', $msg);
	$smarty->assign('email', htmlspecialchars(@$_POST['email']));
	
	login_main();
}
?>
