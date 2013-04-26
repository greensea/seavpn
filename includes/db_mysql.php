<?php

$db_connected = false;

function db_connect() {
	global $db_connected;
	
	mysql_connect(DB_HOST, DB_USER, DB_PASS) or die(mysql_error());
	mysql_select_db(DB_NAME) or die(mysql_error());
	mysql_query('SET NAMES `utf8`');
	
	$db_connected = true;
}

function db_error() {
	return mysql_error();
}

function db_query($sql) {
	global $db_connected;
	
	if (!$db_connected) {
		db_connect();
	}

	$ret = mysql_query($sql);
	if (!$ret) {
		echo 'MySQL error: ' . mysql_error() . "<br /> SQL: $sql <br />";
		
		$bc = debug_backtrace();
		for ($i = 0; $i < count($bc); $i++) {
			echo '(' . basename($bc[$i]['file']) . ":{$bc[$i]['line']})&lt;--";
		}
		echo '{root}';
		
		die();
	}
	
	return $ret;
}

function db_fetch_array($res) {
	return mysql_fetch_array($res, MYSQL_ASSOC);
}

function db_num_rows($res) {
	return mysql_num_rows($res);
}

function db_insert_id() {
	return mysql_insert_id();
}

/**
 * 快速获取一个表的查询结果，并以数组形式返回
 */
function db_quick_fetch($table, $clause) {
	$table = addslashes($table);
	
	$sql = "SELECT * FROM `$table` $clause";
	
	$res = db_query($sql);
	if ($res == false) {
		return array();
	}
	
	$ret = array();
	while (($arr = db_fetch_array($res)) != false) {
		array_push($ret, $arr);
	}
	
	return $ret;
}

/**
 * 快速修改数据表
 * 
 * @param $table	数据表
 * @param $clause	查询条件
 * @param array()	要修改的字段和值，如 array('field1' => 'value1', 'field2' => 'value2')
 */
function db_quick_update($table, $clause, $kv) {
	$table = addslashes($table);
	
	foreach ($kv as $key => $value) {
		$key = addslashes($key);
		$value = addslashes($value);
		$sql = "UPDATE `$table` SET `$key`='$value' $clause";
		db_query($sql);
	}
}
?>
