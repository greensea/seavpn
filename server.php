<?php
require_once('includes/header.php');

$user = user_isonline();
$servers = db_quick_fetch('server', 'WHERE enabled=1');

for ($i = 0; $i < count($servers); $i++) {
	if ($user === false) {
		$servers[$i]['address'] = _("*HIDDEN*");
	}
	
	$servers[$i]['uptimestr'] = time2readable($servers[$i]['uptime'], 3);
	
	/// 处理在线信息及流量信息
	if (time() - $servers[$i]['heartbeat'] > SERVER_ALIVE_THRESHOLD) {
		$servers[$i]['isonline'] = 0;
		$servers[$i]['rtratestr'] = _('Unknown');
	}
	else {
		$servers[$i]['isonline'] = 1;
		$servers[$i]['rtrate'] = $servers[$i]['rxrate'] + $servers[$i]['txrate'];
		$servers[$i]['rtratestr'] = bps2readable($servers[$i]['rtrate']);
	}
}



if (isset($_GET['json'])) {
	die(json_encode($servers));
}
else {
	$smarty->assign('servers', $servers);
	$smarty->display('server.html');
}


?>
