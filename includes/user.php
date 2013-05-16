<?php
require_once('header.php');

/**
 * 修改用户信息
 * 
 * @param	用户编号
 * @param array	用户信息数组（字段名 => 值）
 * @return	成功返回 true，否则返回错误信息
 */
function user_mod($uid, $param) {
	foreach ($param as $key => $value) {
		$qvalue = addslashes($value);
		$qkey = addslashes($key);
		$quid = (int)$uid;
		
		$sql = "UPDATE account SET $qkey='$qvalue' WHERE id=$quid";
		db_query($sql) or die(db_error());
	}
	
	return true;
}

/**
 * 设置用户密码
 * 
 * @param	用户编号
 * @param	密码
 * @return	成功返回 true，否则返回错误信息
 */
function user_passwd($uid, $pass) {
	return user_mod($uid, array('loginpass' => user_encrypt($pass)));
}

/**
 * 设置用户状态，1为正常，0为禁用
 * 
 * @param 成功返回 true，失败返回错误信息
 */
function user_enable($uid, $enabled) {
	if ($enabled != 1) {
		$enabled = 0;
	}
		
	$param = array('enabled' => $enabled);
	
	return user_mod($uid, $enabled);
}


/**
 * 验证用户登录信息
 * 
 * @return	成功返回 true，失败返回错误信息
 */
function user_verify($email, $pass) {
	$ret = _('Invalid usernamem or password');
	
	$qemail = addslashes($email);
	$qpass = addslashes(user_encrypt($pass));
	
	$sql = "SELECT * FROM account WHERE email='$qemail' AND loginpass='$qpass'";
	
	$res = db_query($sql);
	
	if (db_num_rows($res) <= 0) {
		return $ret;
	}
	else {
		return true;
	}
}

/**
 * 获取用户信息
 * 
 * @return	成功返回用户数组，失败返回 false
 */
function user_get($email) {
	$qemail = addslashes($email);
	
	$sql = "SELECT * FROM account WHERE email='$qemail'";
	$res = db_query($sql);
	
	if (db_num_rows($res) <= 0) {
		return false;
	}
	else {
		$ret = db_fetch_array($res);
		
		$ret['balance_dollar'] = sprintf('%0.2f', $ret['balance'] / 100);
		$ret['credit_dollar'] = sprintf('%0.2f', $ret['credit'] / 100);
		
		return $ret;
	}
}

/**
 * 设置用户在线
 */
function user_online($email) {
	$sid = md5( VPNNS . (time(NULL) + rand()) . rand());
	
	$user = user_get($email);
	if ($user === false) {
		vpn_log("Fail set user $email online");
		return false;
	}
	
	cache_set("sid_$sid", $user, USER_SESSIONTIME);
	setcookie(VPNNS . 'sid', $sid, 0);
	
	return true;
}

/**
 * 设置当前会话离线
 */
function user_offline_bysid($sid) {
	cache_unset("sid_$sid");
	
	return true;
}

/**
 * 判断用户是否在线
 * 
 * @return	在线返回用户数组，不在线返回 false
 */
function user_isonline() {
	if (!isset($_COOKIE[VPNNS . 'sid'])) {
		return false;
	}
	
	$sid = $_COOKIE[VPNNS . 'sid'];

	if (!cache_isset("sid_$sid")) {
		return false;
	}
	
	$user = cache_get("sid_$sid");
	
	/// 如果用户在线就更新用户信息
	if ($user != false) {
		$user = user_get($user['email']);
		cache_set("sid_$sid", $user, USER_SESSIONTIME);
	}
	
	return $user;
}

/**
 * 新建用户
 * 
 * @return 成功返回 true，失败返回错误信息
 */
function user_add($email, $pass) {
	$qemail = addslashes($email);
	$qpass = addslashes(user_encrypt($pass));
	$ts = time(NULL);
	
	$sql = "SELECT * FROM account WHERE email='$qemail'";
	$res = db_query($sql);
	if ($res == false) {
		return _('Can create user while querying DB');
	}
	
	if (db_num_rows($res) > 0) {
		return _('The user/email is exists');
	}
	
	$credit = DEFAULT_USER_CREDIT * 100;
	
	$sql = "INSERT INTO account (email, regtime, loginpass, credit) VALUES ('$qemail', $ts, '$qpass', $credit)";
	
	$res = db_query($sql);
	if ($res == false) {
		return _('Can create user while updating DB');
	}
	
	return true;
	
}

/**
 * 用户密码加解密函数
 * 
 * @param $pass	欲加密的明文
 * @param $key	加密密钥
 */
function user_encrypt($pass, $key='') {
	return $pass;
}
function user_decrypt($pass, $key='') {
	return $pass;
}

?>
