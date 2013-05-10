-- phpMyAdmin SQL Dump
-- version 3.5.2
-- http://www.phpmyadmin.net
--
-- 主机: localhost
-- 生成日期: 2013 年 05 月 03 日 16:38
-- 服务器版本: 5.5.30-1.1-log
-- PHP 版本: 5.4.4-14

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- 数据库: `seavpn`
--

-- --------------------------------------------------------

--
-- 表的结构 `account`
--

CREATE TABLE IF NOT EXISTS `account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(64) CHARACTER SET latin1 NOT NULL,
  `loginpass` varchar(64) CHARACTER SET latin1 NOT NULL COMMENT '账户登录密码',
  `regtime` int(11) NOT NULL,
  `enabled` tinyint(4) NOT NULL DEFAULT '1' COMMENT '使能标志',
  `balance` int(11) NOT NULL DEFAULT '0' COMMENT '帐户余额，单位（分）',
  `credit` int(11) NOT NULL DEFAULT '1000' COMMENT '信用额度，单位（分）',
  PRIMARY KEY (`id`),
  KEY `email` (`email`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

-- --------------------------------------------------------

--
-- 表的结构 `order`
--

CREATE TABLE IF NOT EXISTS `order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `createtime` int(11) NOT NULL,
  `amount` int(11) NOT NULL COMMENT '金额，单位（分）',
  `paidtime` int(11) DEFAULT NULL,
  `serviceid` int(11) NOT NULL,
  `vpnid` int(11) NOT NULL COMMENT '对应的 VPN 帐号的编号',
  `delivered` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否已经发货（是否已经设置了 VPN 帐号，或者已经进行了续费操作）',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `paidtime` (`paidtime`),
  KEY `vpnid` (`vpnid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='订单表，保存生成的订单' AUTO_INCREMENT=75 ;

-- --------------------------------------------------------

--
-- 表的结构 `payment`
--

CREATE TABLE IF NOT EXISTS `payment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orderid` int(11) NOT NULL COMMENT '对应订单表中的订单编号',
  `token` varchar(1000) NOT NULL COMMENT '订单号（PayPal 的 token）',
  `remark` text NOT NULL COMMENT '额外的附加信息',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=26 ;

-- --------------------------------------------------------

--
-- 表的结构 `service`
--

CREATE TABLE IF NOT EXISTS `service` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT '唯一的服务名字',
  `desc` text NOT NULL COMMENT '服务内容说明',
  `price` int(11) NOT NULL COMMENT '价格，单位（分）',
  `trafficquota` bigint(20) NOT NULL COMMENT '月流量配额',
  `duration` int(11) NOT NULL COMMENT '包时长度，单位（秒）',
  `enabled` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='服务（商品）列表及说明' AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- 表的结构 `vpnaccount`
--

CREATE TABLE IF NOT EXISTS `vpnaccount` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '对应的用户编号',
  `username` varchar(100) NOT NULL COMMENT 'VPN 登录名',
  `password` varchar(100) NOT NULL COMMENT 'VPN 登录密码',
  `validfrom` int(11) NOT NULL COMMENT '生效时间',
  `validto` int(11) DEFAULT NULL COMMENT '有效期截止至，NULL 表示帐号无效',
  `trafficquota` bigint(20) NOT NULL DEFAULT '10737418240' COMMENT '月流量配额',
  PRIMARY KEY (`id`),
  KEY `validto` (`validto`),
  KEY `uid` (`uid`),
  KEY `username` (`username`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=62 ;



--
-- 表的结构 `heartbeat`
--

CREATE TABLE IF NOT EXISTS `heartbeat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `address` varchar(100) NOT NULL,
  `rxrate` int(11) DEFAULT NULL COMMENT '出站速率，单位（bps）',
  `txrate` int(11) DEFAULT NULL COMMENT '入站速率，单位（bps）',
  `uptime` int(11) DEFAULT NULL,
  `heartbeat` int(11) DEFAULT NULL COMMENT '心跳的时间，单位（UNIX 时间戳）',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='该表只有归档用处，仅供人工查阅。如果不需要服务器心跳记录归档，可将该表引擎类型设置为黑洞' AUTO_INCREMENT=108 ;

-- --------------------------------------------------------

--
-- 表的结构 `server`
--

CREATE TABLE IF NOT EXISTS `server` (
  `address` varchar(100) NOT NULL,
  `location` varchar(500) NOT NULL DEFAULT '' COMMENT '服务器所在地址及网络服务商信息',
  `pptp` tinyint(4) NOT NULL,
  `l2tp` tinyint(4) NOT NULL,
  `remark` varchar(1000) NOT NULL DEFAULT '',
  `enabled` tinyint(4) NOT NULL DEFAULT '1' COMMENT '使能标志',
  `rxrate` int(11) NOT NULL DEFAULT '0' COMMENT '出站速率，单位（bps）',
  `txrate` int(11) NOT NULL DEFAULT '0' COMMENT '入站速率，单位（bps）',
  `uptime` int(11) NOT NULL DEFAULT '0',
  `heartbeat` int(11) NOT NULL DEFAULT '0' COMMENT '最后一次心跳的时间，单位（UNIX 时间戳）',
  `l2tp_psk` varchar(50) NOT NULL,
  PRIMARY KEY (`address`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


--
-- 表的结构 `invite`
--

CREATE TABLE IF NOT EXISTS `invite` (
  `code` char(9) NOT NULL COMMENT '邀请码。9位长度可以容纳超过 10^16 个邀请码',
  `uid` int(11) NOT NULL COMMENT '邀请码所属的用户编号',
  `usedby` int(11) NOT NULL DEFAULT '0' COMMENT '邀请码被谁使用了，对应使用这个邀请码注册的用户编号',
  `ctime` int(11) NOT NULL COMMENT '邀请码创建时间',
  `utime` int(11) DEFAULT NULL COMMENT '邀请码使用时间，为 NULL 说明邀请码还没有使用',
  PRIMARY KEY (`code`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='邀请码表';

