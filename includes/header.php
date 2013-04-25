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

$language = 'zh_CN';
putenv("LANG=$language");
setlocale(LC_ALL, $language);

$path = BASEPATH . 'locales';
$domain = 'messages';
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


?>


