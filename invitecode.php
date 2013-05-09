<?php
require_once('includes/header.php');

$user = user_isonline();

if ($user === false) {
	$smarty->assign(array('tip_title' => _('Login Require'),
						'tip_msg' => _('You have to login before access this page')
					));
	$smarty->display('tip.html');
	die();
}


/// 为用户生成足够的验证码，并读取之
$res = db_quick_fetch('invite', "WHERE uid={$user['id']} ORDER BY utime ASC");
for ($i = count($res); $i < INVITECODE_MAXINUM; $i++) {
	invite_generate($user['id']);
}

if (count($res) < INVITECODE_MAXINUM) {
	$res = db_quick_fetch('invite', "WHERE uid={$user['id']} ORDER BY utime ASC");
}

for ($i = 0; $i < count($res); $i++) {
	if ($res[$i]['utime'] == null) {
		$res[$i]['used'] = 0;
	}
	else {
		$res[$i]['used'] = 1;
	}
}

$smarty->assign('codes', $res);
$smarty->display('invitecode.html');
?>
