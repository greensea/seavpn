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


function time2readable($ts) {
    $ret = '';

    if ($ts >= 86400) {
        $ret .= sprintf(_('%d days'), floor($ts / 86400));
        $ts -= floor($ts / 86400) * 86400;
    }

    if ($ts >= 3600 || $ret != '') {
        $ret .= sprintf(_('%d hours'), floor($ts / 3600));
        $ts -= floor($ts / 3600) * 3600;
    }

    if ($ts >= 60 || $ret != '') {
        $ret .= sprintf(_('%d mins'), floor($ts / 60));
        $ts -= floor($ts / 60) * 60;
    }

    $ret .= sprintf(_('%d secs'), $ts);

    return $ret;
}



?>
