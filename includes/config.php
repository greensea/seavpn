<?php
define('SITE_BASE', 'https://' . $_SERVER['SERVER_NAME'] . '/');

define('LOCALE_DIR', 'langs');
define('DEFAULT_LANGUAGE', 'en_US');	/// 默认语言

/// 语言优先级
$LANGUAGE_ORDER = array('zh_CN', 'en_US');

define('DB_USER', 'seavpn');
define('DB_PASS', '6zqPppHBKRGN76aw');
define('DB_HOST', 'localhost');
define('DB_NAME', 'seavpn');

define('DEFAULT_USER_CREDIT', 10.0);	/// 默认信用额度

define('VPNNS', 'seavpn_');

define('USER_SESSIONTIME', 3600);	/// 用户最长不活动时间

define('PAYPAL_APIUSER', 'gs_api1.bbxy.net');
define('PAYPAL_APIPASS', 'B22V57BMGU8P3MBD');
define('PAYPAL_APISIGN', 'AFcWxV21C7fd0v3bYYYRCpSSRl31AwoNDQLBNIosOHGDYeVRrT0enj0i');
define('PAYPAL', 'd8pUo6dqbnY_XWFpRM3uGLVoPPRkEKl_DWtNFNij8rD4UasPDJL_zXtYf2e0');
define('PAYPAL_REDIRECTURL', 'https://www.sandbox.paypal.com/cgi-bin/websrc?cmd=_express-checkout&token=%s');
define('PAYPAL_APIURL', 'https://api-3t.sandbox.paypal.com/nvp');
define('PAYPAL_RETURNURL', 'https://seavpn.com/paypal_done.php');
?>
