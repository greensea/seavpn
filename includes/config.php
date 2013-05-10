<?php
define('SITE_BASE', 'https://' . $_SERVER['SERVER_NAME'] . '/');

/// 网站信息
define('SITE_NAME', 'SeaVPN');
define('SUPPORT_EMAIL', 'support@seavpn.com');	/// 用户服务电子邮件地址


/// 网站运行信息
define('VPNNS', 'seavpn_');
define('USER_SESSIONTIME', 3600);	/// 用户最长不活动时间

define('LOGIN_MAXTRIES_WITHOUT_CAPTCHA', 5);	/// 同一个 IP 登录失败超过多少次后，就必须使用验证码进行登录验证
define('LOGIN_FAIL_ANNEAL_TIME', 180);	/// 同一个 IP 登录失败超过阈值后，需要等待多长时间以后才可以输验证码，单位（秒）


/// 注册邀请码功能
define('INVITECODE_ENABLED', 1);	/// 注册是否需要邀请码
define('INVITECODE_MINLEN', 6);	/// 邀请码最小长度
define('INVITECODE_MAXINUM', 2);	/// 用户最多可以拥有的邀请码数量


/// SMTP 服务器信息
define('SMTP_HOST', 'localhost');
define('SMTP_PORT', 25);


/// 语言信息
define('LOCALE_DIR', 'langs');
define('DEFAULT_LANGUAGE', 'en_US');	/// 默认语言
$LANGUAGE_ORDER = array('zh_CN', 'en_US');	/// 语言优先级

/// 服务器列表及其接口信息
define('SERVER_ALIVE_THRESHOLD', 120);	/// 服务器多长没有发送心跳视为服务器宕机，单位（秒）
define('SERVER_PING_SALT', '16gEXJlmlySl67v0FbfIZqJMpA');	/// 服务器 PING 接口密码噪声


/// 数据库信息
define('DB_USER', 'seavpn');
define('DB_PASS', '6zqPppHBKRGN76aw');
define('DB_HOST', 'localhost');
define('DB_NAME', 'seavpn');


/// 用户信息以及财务信息
define('DEFAULT_USER_CREDIT', 109.0);	/// 默认信用额度

define('CURRENCY_CODE', 'USD');	/// 货币种类，可选值有 USD，RMB
define('CURRENCY_SYMBOL', '$');	/// 货币单位符号

/// PayPal API 信息，以及其他信息
define('PAYPAL_APIUSER', 'gs_api1.bbxy.net');
define('PAYPAL_APIPASS', 'B22V57BMGU8P3MBD');
define('PAYPAL_APISIGN', 'AFcWxV21C7fd0v3bYYYRCpSSRl31AwoNDQLBNIosOHGDYeVRrT0enj0i');
define('PAYPAL', 'd8pUo6dqbnY_XWFpRM3uGLVoPPRkEKl_DWtNFNij8rD4UasPDJL_zXtYf2e0');
define('PAYPAL_REDIRECTURL', 'https://www.sandbox.paypal.com/cgi-bin/websrc?cmd=_express-checkout&token=%s');
define('PAYPAL_APIURL', 'https://api-3t.sandbox.paypal.com/nvp');
define('PAYPAL_RETURNURL', 'https://seavpn.com/paypal_done.php');


/// reCAPTCHA 密钥信息
define('RECAPTCHA_PUBLIC_KEY', '6LeNCOESAAAAAAs0E_k8Jc9OAqklK6zk6xFLJ4aw'); 
define('RECAPTCHA_PRIVATE_KEY', '6LeNCOESAAAAAPAIxnmDeiYeJi5FTL85e_WcQj1p');
?>
