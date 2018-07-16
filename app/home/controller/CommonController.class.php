<?php
//前台公共控制器
class CommonController extends Controller{
	protected $user = array(); //保存用户信息
	protected $title = 'Lww&Cyc';
	public function __construct() {
		parent::__construct();
		$this->_checkLogin();
	}
	private function _checkLogin(){
		if(session('user','','isset')){
			$this->user = session('user');
			define('IS_LOGIN',true);
		}else{
			define('IS_LOGIN',false);
		}
	}
}