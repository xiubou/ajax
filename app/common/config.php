<?php
//项目配置文件
return array(
	//数据库配置
	'DB_CONFIG' => array(
		'db'   => 'mysql',		//数据库类型
		'host' => 'localhost',	//服务器地址
		'port' => '3306',		//端口
		'user' => 'root',		//用户名
		'pass' => 'root',		//密码
		'charset' => 'utf8',	//字符集
		'dbname' => 'itcast_shop',		//默认数据库
	),
	'DB_PREFIX' => 'shop_',	//数据库表前缀
	//Rewrite模式 路由功能
	'URL_MAP_RULES' => array(	//array('匹配字符串' => '模块/控制器/方法')
		'admin' => 'admin/index/index',
		'login' => 'home/user/login',
		'logout' => 'home/user/logout',
		'register' => 'home/user/register',
		'goods' => 'home/index/goods',
		'find' => 'home/index/find',
		'order' => 'home/order/index',
		'cart' => 'home/cart/index',
		'user' => 'home/user/index',
		'mygoods' => 'home/index/mygoods',
		'myfind' => 'home/index/myfind'
	),
	//Rewrite模式 URL后缀
	'URL_SUFFIX' => '.html',
	//保存在Cookie中的PHPSESSID是否使用HttpOnly
	'PHPSESSID_HTTPONLY' => true,
	//配置时区
	'DEFAULT_TIMEZONE' => 'Asia/Shanghai',
	//Session配置
	'SESSION_PREFIX' => 'shop',   //Session 前缀
	'SESSION_EXPIRE' => 1440,     //Session有效期
	'SESSION_DB' => true          //Session入库开关
	
	
	//第三方登陆
	
);