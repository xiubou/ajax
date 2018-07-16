-- phpMyAdmin SQL Dump
-- version phpStudy 2014
-- http://www.phpmyadmin.net
--
-- 主机: localhost
-- 生成日期: 2017 年 07 月 01 日 16:14
-- 服务器版本: 5.5.47
-- PHP 版本: 5.3.29

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- 数据库: `itcast_shop`
--
CREATE DATABASE `itcast_shop` DEFAULT CHARACTER SET gbk COLLATE gbk_chinese_ci;
USE `itcast_shop`;

-- --------------------------------------------------------

--
-- 表的结构 `shop_admin`
--
-- 创建时间: 2017 年 07 月 01 日 05:35
-- 最后更新: 2017 年 07 月 01 日 07:03
--

DROP TABLE IF EXISTS `shop_admin`;
CREATE TABLE IF NOT EXISTS `shop_admin` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(10) NOT NULL COMMENT '用户名',
  `password` char(32) NOT NULL COMMENT '密码',
  `salt` char(6) NOT NULL COMMENT '密钥',
  `logtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '上次密码错误时间',
  `logcount` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '登录失败次数',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- 转存表中的数据 `shop_admin`
--

INSERT INTO `shop_admin` (`id`, `username`, `password`, `salt`, `logtime`, `logcount`) VALUES
(2, 'admin', '814ee58a3814264cdad68ebe22adfa1d', '2dea28', '2017-07-01 06:19:02', 0);

-- --------------------------------------------------------

--
-- 表的结构 `shop_category`
--
-- 创建时间: 2017 年 07 月 01 日 05:35
-- 最后更新: 2017 年 07 月 01 日 08:08
--

DROP TABLE IF EXISTS `shop_category`;
CREATE TABLE IF NOT EXISTS `shop_category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL COMMENT '分类名',
  `pid` int(10) unsigned NOT NULL COMMENT '父分类ID',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

--
-- 转存表中的数据 `shop_category`
--

INSERT INTO `shop_category` (`id`, `name`, `pid`) VALUES
(1, '1', 0),
(2, '11', 1),
(3, '2', 0),
(4, '22', 3);

-- --------------------------------------------------------

--
-- 表的结构 `shop_goods`
--
-- 创建时间: 2017 年 07 月 01 日 05:35
-- 最后更新: 2017 年 07 月 01 日 08:11
--

DROP TABLE IF EXISTS `shop_goods`;
CREATE TABLE IF NOT EXISTS `shop_goods` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int(10) unsigned NOT NULL COMMENT '所属分类ID',
  `sn` varchar(10) NOT NULL COMMENT '商品编号',
  `name` varchar(60) NOT NULL COMMENT '商品名',
  `price` decimal(10,2) NOT NULL COMMENT '价格',
  `stock` int(10) unsigned NOT NULL COMMENT '库存量',
  `thumb` varchar(150) NOT NULL COMMENT '预览图',
  `album` text NOT NULL COMMENT '商品相册',
  `on_sale` enum('yes','no') NOT NULL DEFAULT 'yes' COMMENT '是否上架',
  `recommend` enum('yes','no') NOT NULL DEFAULT 'no' COMMENT '是否推荐',
  `add_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '添加时间',
  `desc` text NOT NULL COMMENT '商品描述',
  `recycle` enum('yes','no') NOT NULL DEFAULT 'no' COMMENT '是否删除',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- 转存表中的数据 `shop_goods`
--

INSERT INTO `shop_goods` (`id`, `category_id`, `sn`, `name`, `price`, `stock`, `thumb`, `album`, `on_sale`, `recommend`, `add_time`, `desc`, `recycle`) VALUES
(1, 2, '11', 'sadf', '2.00', 277, '', '', 'yes', 'yes', '2017-07-01 08:08:27', '<p>请在此处输入商品详情。</p><p>asf</p><p><br /></p><p>sadf</p><p>sad</p><p>f</p><p>sadf</p>', 'no');

-- --------------------------------------------------------

--
-- 表的结构 `shop_order`
--
-- 创建时间: 2017 年 07 月 01 日 05:35
--

DROP TABLE IF EXISTS `shop_order`;
CREATE TABLE IF NOT EXISTS `shop_order` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL COMMENT '购买者用户ID',
  `goods` text NOT NULL COMMENT '商品信息',
  `address` text NOT NULL COMMENT '收件人信息',
  `price` decimal(10,2) NOT NULL COMMENT '订单价格',
  `add_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '下单时间',
  `cancel` enum('yes','no') NOT NULL COMMENT '是否取消',
  `payment` enum('yes','no') NOT NULL COMMENT '是否支付',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=13 ;

--
-- 转存表中的数据 `shop_order`
--

INSERT INTO `shop_order` (`id`, `user_id`, `goods`, `address`, `price`, `add_time`, `cancel`, `payment`) VALUES
(11, 1, 'a:1:{i:0;a:4:{s:2:"id";i:1;s:3:"num";i:1;s:4:"name";s:4:"sadf";s:5:"price";s:4:"2.00";}}', 'a:3:{s:9:"consignee";s:3:"aaa";s:7:"address";s:21:"北京,西城区,,bbb";s:5:"phone";s:11:"13099998888";}', '2.00', '2017-07-01 08:09:49', 'no', 'no'),
(12, 1, 'a:1:{i:0;a:4:{s:2:"id";i:1;s:3:"num";i:11;s:4:"name";s:4:"sadf";s:5:"price";s:4:"2.00";}}', 'a:3:{s:9:"consignee";s:3:"aaa";s:7:"address";s:21:"北京,西城区,,bbb";s:5:"phone";s:11:"13099998888";}', '22.00', '2017-07-01 08:11:15', 'no', 'no');

-- --------------------------------------------------------

--
-- 表的结构 `shop_session`
--
-- 创建时间: 2017 年 07 月 01 日 05:35
-- 最后更新: 2017 年 07 月 01 日 08:12
--

DROP TABLE IF EXISTS `shop_session`;
CREATE TABLE IF NOT EXISTS `shop_session` (
  `id` varchar(255) NOT NULL,
  `expire` int(10) unsigned NOT NULL,
  `data` blob NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=gbk;

--
-- 转存表中的数据 `shop_session`
--

INSERT INTO `shop_session` (`id`, `expire`, `data`) VALUES
('oida2s3mfeoiailoot0r4m0b55', 1498898162, 0x73686f707c613a333a7b733a353a22746f6b656e223b733a33323a223362396665613965306333333662613833396361663938313532613962626135223b733a353a2261646d696e223b613a323a7b733a343a226e616d65223b733a353a2261646d696e223b733a323a226964223b733a313a2232223b7d733a343a2275736572223b613a323a7b733a323a226964223b733a313a2231223b733a343a226e616d65223b733a343a22726f6f74223b7d7d);

-- --------------------------------------------------------

--
-- 表的结构 `shop_shopcart`
--
-- 创建时间: 2017 年 07 月 01 日 05:35
-- 最后更新: 2017 年 07 月 01 日 08:11
--

DROP TABLE IF EXISTS `shop_shopcart`;
CREATE TABLE IF NOT EXISTS `shop_shopcart` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL COMMENT '购买者ID',
  `add_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '加入购物车时间',
  `goods_id` int(10) unsigned NOT NULL COMMENT '购买商品ID',
  `num` tinyint(3) unsigned NOT NULL COMMENT '购买商品数量',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- 表的结构 `shop_user`
--
-- 创建时间: 2017 年 07 月 01 日 05:35
-- 最后更新: 2017 年 07 月 01 日 06:37
--

DROP TABLE IF EXISTS `shop_user`;
CREATE TABLE IF NOT EXISTS `shop_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(20) NOT NULL COMMENT '用户名',
  `password` char(32) NOT NULL COMMENT '登录密码',
  `salt` char(6) NOT NULL COMMENT '密钥',
  `reg_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '注册时间',
  `phone` char(11) NOT NULL DEFAULT '' COMMENT '联系电话',
  `email` varchar(30) NOT NULL DEFAULT '' COMMENT '邮箱',
  `consignee` varchar(20) NOT NULL DEFAULT '' COMMENT '收件人',
  `address` varchar(255) NOT NULL DEFAULT '' COMMENT '收货地址',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- 转存表中的数据 `shop_user`
--

INSERT INTO `shop_user` (`id`, `username`, `password`, `salt`, `reg_time`, `phone`, `email`, `consignee`, `address`) VALUES
(1, 'root', '814ee58a3814264cdad68ebe22adfa1d', '2dea28', '2017-07-01 05:38:10', '13099998888', 'a@a.com', 'aaa', '北京,西城区,,bbb');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
