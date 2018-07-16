<?php
//回收站控制器
class RecycleController extends CommonController{
	//查看回收站中的商品
	public function indexAction(){
		$page = I('page','get','id',1); //页码
		$data = array();
		//获取商品列表
		$data['goods'] = D('goods')->getData('recycle',-1,$page);
		//超出页码时自动返回第1页
		if(empty($data['goods']['data']) && $page>1){
			$this->redirect('/?p=admin&c=recycle&a=index');
		}
		require ACTION_VIEW;
	}
	//恢复商品
	public function recExecAction(){
		$id = I('id','post','id');
		D('goods')->change($id,'recycle','no');
		$this->success();
	}
	//彻底删除商品
	public function delExecAction(){
		$id = I('id','post','id');
		D('goods')->delete($id);
		$this->success();
	}
}