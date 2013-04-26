<?php
require_once('paypal.lib.php');

/**
 * 1. 生成订单，此时账户余额不变
 * 2. 完成支付，此时账户中将增加刚刚支付的款项
 * 3. 发货，此时账户中将减去订单中标注价格
 */

/**
 * 生成一个新的订单
 * 
 * @param $sid	服务（商品）编号
 * @param $amount	服务（商品）价格，如果不指定则使用数据库中记录的价格
 * @return	成功返回订单信息数组，包含 uid, email, orderid 等信息，失败返回 false
 */
function order_new($sid, $amount = -1) {
	$user = user_isonline();
	
	if ($user === false) {
		vpn_log('User is not online, cant create new order');
		return false;
	}
	
	$sid = (int)$sid;
	$sql = "SELECT * FROM service WHERE id=$sid";
	
	$res = db_query($sql);
	if ($res == false || db_num_rows($res) == 0) {
		vpn_log('No such service id: ' . $sid);
		return false;
	}
	
	$arr = db_fetch_array($res);
	
	$ts = time(NULL);
	$uid = $user['id'];
	if ($amount < 0) {
		$amount = $arr['price'];	/// 数据库中的金额单位是（分）
	}

	
	$sql = "INSERT INTO `order` (uid, createtime, amount, serviceid) VALUES ($uid, $ts, $amount, $sid)";
	$res = db_query($sql);

	if ($res === false) {
		return false;
	}
	
	$user['orderid'] = db_insert_id();
	
	return $user;
}

/**
 * 请求进行付款
 * 
 * @return	失败返回 false，成功返回相关信息
 */
function order_request($orderid) {
	$orderid = (int)$orderid;
	
	$sql = "SELECT * FROM `order` WHERE id=$orderid";
	
	$res = db_query($sql);
	if ($res == false || db_num_rows($res) == 0) {
		vpn_log('No such order id: ' . $orderid);
		return false;
	}
	
	$arr = db_fetch_array($res);
	$amount = $arr['amount'] / 100;
	
	$nvp = paypal_new_payment($orderid, $amount);
	if ($nvp == false) {
		return false;
	}
	
	/// 生成 PayPal 支付订单
	
	$token = $nvp['token'];
	$remark = print_r($nvp, true);
	
	$token = addslashes($token);
	$remark = addslashes($remark);
	
	$sql = "INSERT INTO payment (orderid, token, remark) VALUES ($orderid, '$token', '$remark')";
	
	$res = db_query($sql);
	if ($res == false) {
		vpn_log('Error while creating payment record');
		return false;
	}
	
	$nvp['orderid'] = $orderid;
	
	return $nvp;
}

/**
 * 完成付款
 * 
 * @return 成功返回 true，失败返回 false
 */
function order_dopayment($orderid) {	
	$orderid = (int)$orderid;
	
	$sql = "SELECT * FROM `order` LEFT JOIN payment ON order.id=payment.orderid WHERE order.id=$orderid";
	
	$res = db_query($sql);
	if ($res == false || db_num_rows($res) == 0) {
		vpn_log('No such order id: ' . $orderid);
		return false;
	}
	
	$arr = db_fetch_array($res);
	
	/// 若订单已经支付过了，则不必向 PayPal 再次请求
	if ($arr['paidtime'] != NULL) {
		vpn_log('Order ' . $orderid . ' have been paid before');
		return true;
	}
	
	$amount = $arr['amount'] / 100.0;	/// 数据库中的单位是（分）
	$token = $arr['token'];
	$uid = $arr['uid'];
	
	$ret = paypal_do_payment($token, $amount);
	
	if ($ret == false) {
		vpn_log('Do PayPal payment fail with order id ' . $orderid);
		return false;
	}
	
	/// 完成订单，更新数据库中的相关信息
	
	$ts = time(NULL);
	
	$sql = "UPDATE `order` SET paidtime=$ts WHERE id=$orderid";
	$res = db_query($sql);
	if ($res == false) {
		vpn_log('Warning: update order table fail after done payment with order id ' . $orderid);
	}
	
	$amountcent = $amount * 100;
	$sql = "UPDATE account SET balance=balance+$amountcent WHERE id=$uid";
	db_query($sql);
	if ($res == false) {
		vpn_log('Warning: update user(uid=$uid) balance fail');
	}
	
	return $ret;
}

/**
 * 发货操作，将订单标记为已发货，并从用户账户中扣除货款
 */
function order_delivery($orderid) {
	$orderid = (int)$orderid;
	
	$orders = db_quick_fetch('order', "WHERE id=$orderid");
	if (count($orders) <= 0) {
		vpn_log("No such order id $orderid");
		return false;
	}
	$order = $orders[0];
	
	$services = db_quick_fetch('service', "WHERE id IN (SELECT serviceid FROM `order` WHERE id={$order['id']})");
	if (count($services) <= 0) {
		vpn_log("No service correlate to order #{$order['id']}");
		return false;
	}
	
	/// FIXME: 这里应该增加失败回滚操作
	
	$sql1 = "UPDATE `order` SET delivered=1 WHERE id=$orderid";
	$sql2 = "UPDATE account SET balance=balance-{$services[0]['price']} WHERE id={$order['uid']}";

	db_query($sql1);
	db_query($sql2);
	
	return true;
}

/**
 * 将用户重定向到付款网站，之后的支付流程在付款网站后完成
 * 
 * @param $oid	订单编号
 */
function order_redirect($oid) {
	return paypal_redirect($oid);
}

?>
