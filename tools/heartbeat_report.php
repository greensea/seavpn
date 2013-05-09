<?php
define('INTERFACE_NAME', 'eth0');	/// 需要统计流量的接口

define('SERVER_ADDRESS', 'localhost');	/// 本机的服务器地址或本机的域名，应该和数据库中服务器列表的 address 字段匹配

define('SERVER_PING_SALT', '16gEXJlmlySl67v0FbfIZqJMpA');	/// 服务器 PING 接口密码噪声

define('HEARTBEAT_API', 'http://seavpn.com/server_ping.php');
define('REPORT_TIMEOUT', 60);	/// cURL 最长执行时间

$dev = INTERFACE_NAME;
$statfile = "/tmp/seavpn.$dev.stat";
$s = file_get_contents('/proc/net/dev');

$ret = array();

preg_match("/$dev: *(\d+) *(\d+) *(\d+) *(\d+) *(\d+) *(\d+) *(\d+) *(\d+) *(\d+) *(\d+) *(\d+) *(\d+) *(\d+) *(\d+)/", $s, $ret);

$net = array();
$net['rx'] = $ret[1];	/// 第 1 列是下行，2 列是包数
$net['tx'] = $ret[9];	/// 第 9 列是下行，10 列是包数
$net['ts'] = time();

if (!file_exists($statfile)) {
	file_put_contents($statfile, serialize($net));
	die("First initialize stat file on $statfile");
}


/// 来计算流量信息
$stat = array();
$oldnet = unserialize(file_get_contents($statfile));

file_put_contents($statfile, serialize($net));

$stat['ts'] = $net['ts'] - $oldnet['ts'];
$stat['rx'] = $net['rx'] - $oldnet['rx'];
$stat['tx'] = $net['tx'] - $oldnet['tx'];

if ($stat['rx'] < 0) {
	$stat['rx'] = $net['rx'];
}
if ($stat['tx'] < 0) {
	$stat['tx'] = $net['tx'];
}

if ($stat['tx'] <= 0) {
	die("Invalid time interval {$stat['tx']}");
}

$stat['tx'] /= $stat['ts'];
$stat['rx'] /= $stat['ts'];

$uptime = null;
sscanf(file_get_contents('/proc/uptime'), '%d', $uptime);

$stat['uptime'] = $uptime;

$ret = heartbeat(SERVER_ADDRESS, $stat['uptime'], $stat['rx'] * 8, $stat['tx'] * 8);

die($ret);


function heartbeat($name, $uptime, $rx, $tx) {
	$pass = md5($name . SERVER_PING_SALT);
	
	$ch = curl_init(HEARTBEAT_API . "?address=$name&uptime=$uptime&rxrate=$rx&txrate=$tx&password=$pass");
	
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, REPORT_TIMEOUT);
	
	$ret = curl_exec($ch);
	if ($ret == false) {
		$ret = curl_error($ch);
	}
	
	curl_close($ch);
	
	return $ret;
}
?>
