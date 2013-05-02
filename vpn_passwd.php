<?php
require_once('includes/header.php');
require_once('includes/vpn.php');

$id = (int)@$_GET['id'];
$pass = @$_GET['passwd'];

if ($pass == '') {
	json_error(_('Empty password is no acceptable'));
	die();
}

$user = user_isonline();
if ($user === false) {
	json_error(_('You have to login before change VPN account password'));
	die();
}

$vpns = db_quick_fetch('vpnaccount', "WHERE uid={$user['id']} AND id=$id");
if (count($vpns) <= 0) {
	json_error(_('VPN account is not exists'));
	die();
}

$ret = vpn_passwd($vpns[0]['username'], $pass);

if ($ret === true) {
	echo json_encode(array('success' => 1));
}
else {
	json_error($ret);
}

die();


function json_error($msg) {
	echo json_encode(array('error' => $msg));
}

?>
