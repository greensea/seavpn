<?php
function cache_get($name) {
	return xcache_get(VPNNS . $name);
}

function cache_set($name, $value, $ttl = 0) {
	return xcache_set(VPNNS . $name, $value, $ttl);
}

function cache_unset($name) {
	return xcache_unset(VPNNS . $name);
}

function cache_isset($name) {
	return xcache_isset(VPNNS . $name);
}

function vpn_log($msg) {
	$bc = debug_backtrace();
	$syslog_msg = sprintf('SeaVPN: %s (%s:%s)', $msg, basename($bc[0]['file']), $bc[0]['line']);
	
	syslog(LOG_WARNING, $syslog_msg);
}

/**
 * URL 请求字串转换成数组，如 a=1&b=2 将会转换成 (a => 1, b => 2)
 */
function getstr2array($str) {
	$ret = array();
	
	$nv = split('&', $str);
	
	foreach ($nv as $k => $v) {
		$t = split('=', $v);
		
		if (count($t) <= 1) {
			$ret[$t[0]] = '';
		}
		else {
			$ret[$t[0]] = urldecode($t[1]);
		}
	}
	
	return $ret;
}


function size2readable($size) {
    $fmt = '';

    if ($size < 1000) {
        $fmt = sprintf('%d B', $size);
    }
    else if ($size < 1000 * 1000) {
        $fmt = sprintf('%0.2f KiB', $size / 1024);
    }
    else if ($size < 1000 * 1000 * 1000) {
        $fmt = sprintf('%0.2f MiB', $size / 1024 / 1024);
    }
    else if ($size < 1000 * 1000 * 1000 * 1000) {
        $fmt = sprintf('%0.2f GiB', $size / 1024 / 1024 / 1024);
    }
    else if ($size < 1000 * 1000 * 1000 * 1000 * 1000) {
        $fmt = sprintf('%0.2f TiB', $size / 1024 / 1024 / 1024 / 1024);
    }
    else {
        $fmt = sprintf('%0.2f PiB', $size / 1024 / 1024 / 1024 / 1024 / 1024);
    }

    return $fmt;
}


/**
 * @param	时间，单位（秒）
 * @param	显示深度，比如深度为 1 则只显示到第一个单位，即 2 天；如果深度为 2 则现实到第二个单位，即 2 天 10 小时。
 */
function time2readable($ts, $depth = 999) {
    $ret = '';

    if ($ts >= 86400) {
        $ret .= sprintf(_('%d days'), floor($ts / 86400));
        $ts -= floor($ts / 86400) * 86400;
	$depth--;
    }

    if ($depth <= 0) return $ret;

    if ($ts >= 3600 || $ret != '') {
        $ret .= sprintf(_(' %d hours'), floor($ts / 3600));
        $ts -= floor($ts / 3600) * 3600;
	$depth--;
    }
    
    if ($depth <= 0) return $ret;

    if ($ts >= 60 || $ret != '') {
        $ret .= sprintf(_(' %d mins'), floor($ts / 60));
        $ts -= floor($ts / 60) * 60;
	$depth--;
    }
    
    if ($depth <= 0) return $ret;

    $ret .= sprintf(_(' %d secs'), $ts);

    return $ret;
}

function bps2readable($bps) {
    $fmt = '';

    if ($bps < 1000) {
        $fmt = sprintf('%d bps', $bps);
    }
    else if ($bps < 1000 * 1000) {
        $fmt = sprintf('%0.2f Kbps', $bps / 1024);
    }
    else if ($bps < 1000 * 1000 * 1000) {
        $fmt = sprintf('%0.2f Mbps', $bps / 1024 / 1024);
    }
    else if ($bps < 1000 * 1000 * 1000 * 1000) {
        $fmt = sprintf('%0.2f Gbps', $bps / 1024 / 1024 / 1024);
    }
    else if ($bps < 1000 * 1000 * 1000 * 1000 * 1000) {
        $fmt = sprintf('%0.2f TBps', $bps / 1024 / 1024 / 1024 / 1024);
    }
    else {
        $fmt = sprintf('%0.2f Pbps', $bps / 1024 / 1024 / 1024 / 1024 / 1024);
    }

    return $fmt;
}

/**
 * 自动化脚本的日志功能
 */
function tool_log($msg) {
    echo $msg . "\n";
    vpn_log("Autotool: $msg");
}


/**
 * 发送电子邮件
 * 
 * @return	成功返回 true，失败返回错误信息
 */
function sendmail($to, $from, $subject, $content) {
    ini_set('SMTP', SMTP_HOST);
    ini_set('smtp_port', SMTP_PORT);
    
    if ($to == '' || $from == '') {
	vpn_log(_('Invalid sendmail argument'));
	return _("Must specify an email from and email to address");
    }
    
    if(strstr($to, ':') !== false) {
	return _("No `:' is allow in email address");
    }
    
    mail($to, $subject, $content, "From: $from\nContent-Type: text/html");
    
    return true;
}

function recaptcha_verify() {
    $captcha = recaptcha_check_answer(RECAPTCHA_PRIVATE_KEY, $_SERVER['REMOTE_ADDR'], $_POST['recaptcha_challenge_field'], $_POST['recaptcha_response_field']);
    
    if ($captcha->is_valid) {
	return true;
    }
    else {
	return false;
    }
}

/**
 * 为 $uid 用户生成一个邀请码
 * 
 * 从 INVITECODE_MINLEN 长度开始生成邀请码，如果生成的邀请码已经存在了，则生成一个更长的邀请码，直到得到在数据库中不存在的邀请码为止
 * 
 * @return 成功返回验证码，失败返回 false
 */
function invite_generate($uid) {
	$uid = (int)$uid;
	$code = '';
	
	if ($uid == 0) {
		vpn_log('Invalid argument: $uid == 0');
		return false;
	}
	
	/// 没人会使用 99 位长度的验证码吧
	for ($len = INVITECODE_MINLEN; $len < 99; $len++) {
		$code = invite_randstr($len);
	
		$res = db_quick_fetch('invite', "WHERE code='$code'");
		if (count($res) == 0) {
			break;
		}
	}
	
	if ($len >= 99) {
		vpn_log("Invite code out of length: $len");
		return false;
	}
	
	
	/// FIXME: 要不要检查用户是否存在捏？
	
	$ts = time();
	$qcode = addslashes($code);
	$sql = "INSERT INTO invite (code, uid, ctime, utime) VALUES ('$qcode', $uid, $ts, NULL)";
	db_query($sql);
	
	return $code;
}

/**
 * 生成一个随机字符串作为邀请码
 * 
 * @param len	字符串长度
 */
function invite_randstr($len) {
    $ret = '';
    
    for ($i = 0; $i < $len; $i++) {
	$r = rand(1, 62);
	$c = '';
	
	if ($r >= 1 && $r <= 26) {
	    $c = chr(ord('a') + ($r - 1));
	}
	else if ($r >= 27 && $r <= 52) {
	    $c = chr(ord('A') + ($r - 27));
	    
	}
	else {
	    $c = chr(ord('0') + ($r - 53));
	}
	
	$ret .= $c;
    }
    
    return $ret;
}

/**
 * 将邀请码设置为已经使用过了
 */
function invite_use($code, $by) {
	$qcode = addslashes($code);
	$by = (int)$by;
	
	db_quick_update('invite', "WHERE code='$qcode'", array('utime' => time(),
															'usedby' => $by)
					);
}

?>
