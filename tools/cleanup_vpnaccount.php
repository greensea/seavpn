<?php
require_once('../includes/header.php');
require_once('../includes/vpn.php');

define('VPNACCOUNT_RESERVE_TIME', (86400 * 32));	/// vpnaccount 表中已经超时的帐号最长能够保留多少时间，单位（秒）

tool_log(_('SeaVPN VPN Account Cleanup Autotool'));
tool_log(_('This script will check vpnaccount table, filter out the expired accounts, and remove them'));

/// 第 1 步，从 vpnaccount 表中找出所有已经过期的帐号，将其从 RADIUS 表中删除

$ts = time();

$vpns = db_quick_fetch('vpnaccount', "WHERE validto<$ts");
$vpns_count = count($vpns);
tool_log(_("There is $vpns_count accounts expired in table vpnaccount"));

for ($i = 0; $i < $vpns_count; $i++) {
	$ret = vpn_del($vpns[$i]['username']);
	
	if ($ret === true) {
		tool_log(_("Delete user `{$vpns[$i]['username']}' from RADIUS check table successed"));
	}
	else {
		tool_log(_("Delete user `{$vpns[$i]['username']}' from RADIUS check table FAILED ($ret)"));
	}
}


/// 第 2 步，从 vpnaccount 表中彻底删除帐号
$ts -= VPNACCOUNT_RESERVE_TIME;
$expiredays = VPNACCOUNT_RESERVE_TIME / 86400;

$vpns = db_quick_fetch('vpnaccount', "WHERE validto<$ts");
$vpns_count = count($vpns);
tool_log(_("There is $vpns_count accounts expired more than $expiredays days in table vpnaccount"));

for ($i = 0; $i < $vpns_count; $i++) {
	$ret = vpn_purge($vpns[$i]['username']);

	if ($ret === true) {
		tool_log(_("Delete user `{$vpns[$i]['username']}' from vpnaccount table successed"));
	}
	else {
		tool_log(_("Delete user `{$vpns[$i]['username']}' from vpnaccount table FAILED ($ret)"));
	}
}

tool_log(_('VPN Account Cleanup Autotool finished'));
?>
