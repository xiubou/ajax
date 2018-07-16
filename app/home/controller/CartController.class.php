<?php
//购物车
class CartController extends CommonController{
	//构造方法检查用户登录
	public function __construct(){
		parent::__construct();
		//检查登录
		if(!IS_LOGIN){
			if(substr(ACTION,-4)=='exec'){
				$this->error('请先登录！');
			}else{
				$this->redirect('?c=user&a=login');
			}
		}
		//设置标题
		$this->title = 'Carts';
	}
	//购物车列表
	public function indexAction(){
		$data = D('shopcart')->getData($this->user['id']);
		require ACTION_VIEW;
	}
	//添加到购物车
	public function addExecAction(){
		$id = I('id','post','id');   //要购买的商品
		$num = I('num','post','id'); //购买的数量
		$num = max($num,1); //购买数量最小为1
		//添加到购物车
		D('shopcart')->addCart($id,$this->user['id'],$num);
		$this->success('添加购物车成功');
	}
	//从购物车删除
	public function delExecAction(){
		$id = I('id','post','id');
		D('shopcart')->delete($id,$this->user['id']);
		$this->success();
	}
	//测试
	public function myindexAction(){
		$this->title = 'myCarts';
		require ACTION_VIEW;
	}
	public function contactAction(){
		$this->title = 'Contact';
		require ACTION_VIEW;
	}
}