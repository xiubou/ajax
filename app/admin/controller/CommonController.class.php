<?php
//后台公共控制器
class CommonController extends Controller{
	protected $user = array(); //保存用户信息
	protected $title = "传智商城 - 后台管理系统"; //网页标题
	public function __construct(){
		parent::__construct();
		//检查用户是否登录
		$this->_mycheckLogin();
	}
	private function _checkLogin(){
		//判断session中是否有管理员信息
		if(session('admin','','isset')){
			$this->user = session('admin');
		}else{
			$this->redirect('/?p=admin&c=login&a=index');  //$this->redirect('/?p=home&c=user&a=go');
		}
	}
	private function _mycheckLogin(){
		//判断session中是否有管理员信息
		if(session('admin','','isset')){
			$this->user = session('admin');
		}else{
			$this->redirect('/?p=home&c=user&a=go');  //$this->redirect('/?p=home&c=user&a=go');
		}
	}
}