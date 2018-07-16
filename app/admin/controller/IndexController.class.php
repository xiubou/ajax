<?php
//后台首页
class IndexController extends CommonController{
	
	//后台首页，显示服务器基本信息
	public function indexAction(){
		$serverInfo = array(
			//获取服务器信息（操作系统、Apache版本、PHP版本）
			'server_version' => $_SERVER['SERVER_SOFTWARE'],
			//获取MySQL版本信息
			'mysql_version' => $this->_getMySQLVer(),
			//获取服务器时间
			'server_time' => date('Y-m-d H:i:s', time()),
			//上传文件大小限制
			'max_upload' => ini_get('file_uploads') ? ini_get('upload_max_filesize') : '已禁用', 
			//脚本最大执行时间
            'max_ex_time' => ini_get('max_execution_time').'秒', 
		);
		
		require ACTION_VIEW;
	}
	
	public function myindexAction(){
		$serverInfo = array(
			//获取服务器信息（操作系统、Apache版本、PHP版本）
			'server_version' => $_SERVER['SERVER_SOFTWARE'],
			//获取MySQL版本信息
			'mysql_version' => $this->_getMySQLVer(),
			//获取服务器时间
			'server_time' => date('Y-m-d H:i:s', time()),
			//上传文件大小限制
			'max_upload' => ini_get('file_uploads') ? ini_get('upload_max_filesize') : '已禁用', 
			//脚本最大执行时间
            'max_ex_time' => ini_get('max_execution_time').'秒', 
		);
		
		require ACTION_VIEW;
	}
	//获取MySQL版本
	private function _getMySQLVer(){
		$rst = M()->fetchRow('select version() as ver');
		return isset($rst['ver']) ? $rst['ver'] : '未知';
	}
}