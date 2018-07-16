<?php
//后台商品订单
class OrderController extends CommonController{
	
	//查看所有的订单
	public function indexAction(){
		//查询分类数据
		$page = I('page','get','id',1);  //以get方式获取page
		$data = D('order')->getData($page);
		// $newdata = D('order')->getNewdata($page);
		//显示视图
		require ACTION_VIEW;
	}
}
