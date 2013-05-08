<?php
require_once('includes/header.php');

/**
 * 接口格式：
 * 
 * server_ping.php?address={服务器地址}&password={认证密码}&txrate={出站速率}&rxrate={入站速率}&uptime={已运行时间}
 * 
 * 所有字段都是必填字段，其中出站速率和入站速率单位为 bps，已运行时间单位为 UNIX 时间戳。
 */
 
$fields = array('txrate', 'rxrate', 'uptime');

$ts = time();

$user = @$_GET['address'];	/// 服务器地址作为用户名
$pass = @$_GET['password'];

$quser = addslashes($user);

if (ping_validate($user, $pass) !== true) {
	ping_error('authentication fail');
	die();
}

/// 检查参数完备性
foreach ($fields as $i) {
	if (!isset($_GET[$i])) {
		ping_error("missing $i field");
		die();
	}
}

/// 检查服务器是否存在
$server = db_quick_fetch('server', "WHERE address='$quser'");
if (count($server) <= 0) {
	ping_error('No such server');
	die();
}

/// 更新数据库
$data = array();

$data['heartbeat'] = $ts;

foreach ($fields as $i) {
	$data[$i] = $_GET[$i];
}

db_quick_update('server', "WHERE address='$quser'", $data);


/// 记录到 heartbeat 表中
foreach ($fields as $i) {
	$data[$i] = addslashes($data[$i]);
}
$ts = time();
$sql = "INSERT INTO heartbeat (address, rxrate, txrate, uptime, heartbeat) VALUES ('$quser', {$data['rxrate']}, {$data['txrate']}, {$data['uptime']}, $ts)";
db_query($sql);


ping_success('successed');
die();




/**
 * 密码 = md5sum(username + salt)
 */
function ping_validate($user, $pass) {
	if ($user == '' || $pass == '') {
		vpn_log('Empty username or password');
		return false;
	}
	
	$pass2 = md5($user . SERVER_PING_SALT);
	if ($pass2 != $pass) {
		vpn_log("Validate server ping fail: expect pass=`$pass2' but got `$pass'");
		return false;
	}
	
	return true;
}


function ping_success($msg) {
	echo json_encode(array('success' => $msg));
}

function ping_error($msg) {
	echo json_encode(array('error' => $msg));
}

?>
