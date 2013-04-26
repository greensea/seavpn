<?php
require_once('includes/header.php');
require_once('includes/vpn.php');

$smarty->assign('title', _('My Account'));

if (user_isonline() == false) {
	account_login();
}
else {
	account_main();
}
die();


function account_login() {
	global $smarty;
	
	$smarty->assign('tip_title', _('Please login'));
	$smarty->assign('tip_msg', _('You have to login before access My Account page'));
	$smarty->assign('redirect_url', 'login.php');
	
	$smarty->display('tip.html');
}

function account_main() {
	global $smarty;
	
	$user = user_isonline();
	$vpn = vpn_list($user['id']);
	
	foreach ($vpn as $key => $value) {
		$arr = vpn_accountstat_monthly($value['username']);
		$arr['inbandstr'] = size2readable($arr['in']);
		$arr['outbandstr'] = size2readable($arr['out']);
		$arr['usedbandstr'] = size2readable($arr['in'] + $arr['out']);
		$arr['availbandstr'] = size2readable($value['trafficquota'] - $arr['in'] - $arr['out']);
		if ($value['trafficquota'] > 0) {
			$arr['percentused'] = sprintf('%.0f', round(($arr['in'] + $arr['out']) * 100 / $value['trafficquota']));
		}
		else {
			$arr['percentused'] = '100+';
		}
		
		$arr['onlinetimestr'] = time2readable($arr['sessiontime']);
		
		$vpn[$key]['totalbandstr'] = size2readable($value['trafficquota']);
		
		$vpn[$key]['stat_monthly'] = $arr;
	}
	
	$smarty->assign('vpns', $vpn);
	
	$smarty->display('account.html');
}


?>
