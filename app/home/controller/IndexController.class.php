<?php
//前台首页
class IndexController extends CommonController{
	//首页
	public function indexAction(){
		$data = array();
		//获得分类列表
		$data['category'] = D('category')->getData('_childList');  //CategoryModel 中的getdata()
		//查询推荐商品
		$data['best'] = D('goods')->getBest();
		//视图
		$this->title = '传智商城首页';
		require ACTION_VIEW;
	}
	//查找商品
	public function findAction(){
		//获取参数
		$page = I('page','get','id',1); //当前页码
		$cid = I('find','get','id', I('cid','get','id',-1)); //分类ID
		//实例化模型
		$Goods = D('goods');
		$Category = D('category');
		//如果分类ID大于0，则取出所有子分类ID
		$cids = ($cid>0) ? $Category->getSubIds($cid) : $cid;
		//获取商品列表
		$data['goods'] = $Goods->getData($cids,$page);
		//防止空页被访问
		if(empty($data['goods']['data']) && $page > 1){
			$this->redirect("/?a=find&cid=$cid");
		}
		//查询分类列表
		$data['category'] = $Category->getFamily($cid);
		$this->title = '商品列表 - 传智商城';
		require ACTION_VIEW;
	}
	//查看商品
	public function goodsAction(){
		$id = I('goods','get','id', I('id','get','id')); //要查看的商品ID
		$Goods = D('goods');
		$Category = D('category');
		//查找当前商品
		$data['goods'] = $Goods->getGoods($id);
		if(empty($data['goods'])){
			exit('您访问的商品不存在，已下架或删除！');
		}
		//查找推荐商品
		$cids = $Category->getSubIds($data['goods']['category_id']);
		$data['recommend'] = $Goods->getRecommendByCids($cids);
		//查找分类导航
		$data['path'] = $Category->getPath($data['goods']['category_id']);
		$this->title = $data['goods']['name'].' - 传智商城';
		require ACTION_VIEW;
	}
	//测试
	public function myindexAction(){
		$data = array();
		//获得分类列表
		$data['category'] = D('category')->getData('_childList');
		//查询推荐商品
		$data['best'] = D('goods')->getBest();
		$this->title = '主页';
		require ACTION_VIEW;	
	}
	public function mygoodsAction(){
		$id = I('goods','get','id', I('id','get','id')); //要查看的商品ID
		$Goods = D('goods');
		$Category = D('category');
		//查找当前商品
		$data['goods'] = $Goods->getGoods($id);
		if(empty($data['goods'])){
			exit('您访问的商品不存在，已下架或删除！');
		}
		//查找推荐商品
		$cids = $Category->getSubIds($data['goods']['category_id']);
		$data['recommend'] = $Goods->getRecommendByCids($cids);
		//查找分类导航
		$data['path'] = $Category->getPath($data['goods']['category_id']);
		$this->title = $data['goods']['name'].' - LWW&CYC';
		require ACTION_VIEW;
	}
	public function myfindAction(){
		$page = I('page','get','id',1); //当前页码
		$cid = I('myfind','get','id', I('cid','get','id',-1)); //分类ID
		//实例化模型
		$Goods = D('goods');
		$Category = D('category');
		//如果分类ID大于0，则取出所有子分类ID
		$cids = ($cid>0) ? $Category->getSubIds($cid) : $cid;
		//获取商品列表
		$data['goods'] = $Goods->getData($cids,$page);
		//防止空页被访问
		if(empty($data['goods']['data']) && $page > 1){
			$this->redirect("/?a=myfind&cid=$cid");
		}
		//查询分类列表
		$data['category'] = $Category->getFamily($cid);
		$data['category'] = $Category->getData('_childList');
		$this->title = 'Prolist';
		require ACTION_VIEW;
	}
}