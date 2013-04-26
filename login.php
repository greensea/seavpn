<?php
require_once('includes/header.php');

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
	
	$smarty->display('templates/login.html');
}

function login_check() {
	global $smarty;
	
	$email = @$_POST['email'];
	$pass = @$_POST['loginpass'];
	
	$ret = user_validate($email, $pass);
	
	if ($ret !== true) {
		login_error($ret);
		die();
	}
	else {
		user_online($email);
		
		$url = 'account.php';
		
		header("Location: $url");
		
		$smarty->assign('url', $url);
		$smarty->display('templates/redirect.html');
	}
}


function login_error($msg) {
	global $smarty;
	
	$smarty->assign('error_msg', $msg);
	$smarty->assign('email', htmlspecialchars(@$_POST['email']));
	
	$smarty->display('templates/login.html');
}
?>
