<?php
require_once('includes/header.php');

user_offline_bysid(@$_COOKIE[VPNNS . 'sid']);
header('Location: index.php');
?>
