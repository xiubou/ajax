<?php
//会员管理控制器
class UserController extends CommonController{
	//会员列表
	public function indexAction(){
		$page = I('page','get','id',1);
		$data = D('user')->getData($page);
		require ACTION_VIEW;
	}
	public function myindexAction(){
		$page = I('page','get','id',1);
		$data = D('user')->getData($page);
		require ACTION_VIEW;
	}
}