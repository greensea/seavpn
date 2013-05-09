<?php
DEFINE('BASEPATH', __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR);

require_once(BASEPATH . 'includes/config.php');
require_once(BASEPATH . 'includes/functions.php');
require_once(BASEPATH . 'includes/user.php');
require_once(BASEPATH . 'includes/db_mysql.php');

require_once(BASEPATH . 'smarty/Smarty.class.php');

$smarty = new Smarty();
$smarty->setTemplateDir(BASEPATH.'templates');
$smarty->setCompileDir(BASEPATH.'compile');
$smarty->setCacheDir(BASEPATH . 'compile');
$smarty->setConfigDir(BASEPATH . 'compile');


/// 根据浏览器发送的信息判断语言
$language = DEFAULT_LANGUAGE;

$langstr = $_SERVER['LANGUAGE'] . ' ' . $_SERVER['LANG'];
foreach ($LANGUAGE_ORDER as $key => $value) {
	if (stristr($langstr, $value) !== false) {
		$language = $value;
		break;
	}
}

putenv("LANG=$language");
setlocale(LC_ALL, $language . '.utf8');	/// 使用 `locale -a` 命令来查看服务器支持的本地化语言

$path = BASEPATH . LOCALE_DIR;
$domain = "messages";

bindtextdomain($domain, $path);
textdomain($domain);
bind_textdomain_codeset($domain, 'UTF-8');

/// Assign online user variables
if ($ret = user_isonline()) {
	$smarty->assign('user', $ret);
}

/// Assign default variables
$smarty->assign('css', array());
$smarty->assign('js', array());

$smarty->assign('support_email', SUPPORT_EMAIL);
$smarty->assign('SERVER_NAME', $_SERVER['SERVER_NAME']);
$smarty->assign('SITE_NAME', SITE_NAME);
$smarty->assign('INVITECODE_ENABLED', INVITECODE_ENABLED);
$smarty->assign('CURRENCY_SYMBOL', CURRENCY_SYMBOL);
?>


