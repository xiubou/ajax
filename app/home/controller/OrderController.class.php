<?php
//订单控制器
class OrderController extends CommonController{
	//构造方法，要求用户必须先登录
	public function __construct() {
		parent::__construct();
		//检查登录
		if(!IS_LOGIN){
			if(substr(ACTION,-4)=='exec'){
				$this->error('请先登录！');
			}else{
				$this->redirect('?c=user&a=login');
			}
		}
		$this->title = 'Order';
	}	
	//查看订单
	public function indexAction(){
		//取出用户的订单列表
		$data = D('order')->getData($this->user['id']);
		require ACTION_VIEW;
	}
	//购买商品（单件或多件商品）
	public function addExecAction(){
		$buy = $this->_input(); //接收外部数据
		//实例化模型
		$Goods = M('goods');
		$Order = M('order');
		$uid = $this->user['id']; //当前用户ID
		//准备待写入数据库的数据
		$data = array(
			'price' => 0,       //订单总价格
			'payment' => 'no',  //订单未支付
			'cancel' => 'no',   //订单未取消
			'user_id' => $uid,  //购买者的用户ID
		);
		//获取收件人信息（收件人，收件地址，手机）
		$data['address'] = M('user')->fetchRow("select `consignee`,`address`,`phone` from __USER__ where `id`=$uid");
		//如果没有收件人，则不允许购买
		foreach(array('consignee','address','phone') as $v){
			if(empty($data['address'][$v])){
				$this->error('请先完善收货地址。');
			}
		}
		//开启事务
		$Order->startTrans();
		//处理每件商品
		foreach($buy as $id=>$num){
			//查询出商品的名称、价格
			$goods = $Goods->fetchRow("select `name`,`price` from __GOODS__ where `id`=$id");
			if(empty($goods)){
				$Order->rollBack();  //回滚
				$this->error("您购买的商品不存在，错误的商品ID：{$id}。");
			}
			//组合商品信息
			$data['goods'][] = array(
				'id' => $id,       //商品ID
				'num' => $num,     //购买数量
				'name' => $goods['name'],   //商品名
				'price' => $goods['price'], //价格
			);
			//准备库存操作的where条件
			$where = array(
				"`id` = $id",      //商品ID
				"`stock` >= $num", //库存不低于购买数量
				"`recycle` = 'no'",    //商品未在回收站中
				"`on_sale` = 'yes'",   //商品已经上架
			);
			//更新库存
			if(!$Goods->exec("update __GOODS__ set `stock`=`stock`-$num where ".implode(' and ',$where))){
				$Order->rollBack();  //回滚
				$this->error('执行失败，商品“'.$goods['name'].'”库存不足。');
			}
			//价格自增
			$data['price'] += $goods['price'] * $num;
			//单笔订单价格限额
			if($data['price'] > 99999999){
				$Order->rollBack();  //回滚
				$this->error('执行失败，超过了单笔订单的限额。');
			}
		}
		//数组序列化
		$data['address'] = serialize($data['address']);
		$data['goods'] = serialize($data['goods']);
		
		//保存订单
		if(!$Order->data($data)->add()){
			$Order->rollBack();  //回滚
			$this->error('执行失败，生成订单失败。');
		}
		
		$Order->commit();    //提交事务
		
		//生成订单成功，删除购物车中的记录
		M('shopcart')->exec('delete from __SHOPCART__ where `goods_id` in('.implode(',',array_keys($buy)).') and `user_id`='.$uid);
		
		$this->success('','/?c=order');
	}
	
	//接收外部数据
	private function _input(){
		$buy = I('buy','post','array'); //获取参数
		$data = array(); //保存关联数组结果 array(id=>num)
		//从参数中取出每件商品的ID和购买数量
		foreach($buy as $v){
			if(isset($v['id']) && isset($v['num'])){				
				$v['id'] = max((int)$v['id'],0);   //商品ID不能为负数
				$v['num'] = max((int)$v['num'],1); //购买数量最少为1
				$data[$v['id']] = $v['num'];
			}else{
				$this->error('执行失败，参数不正确。');
			}
		}
		return $data;
	}

	//取消订单
	public function cancelExecAction(){
		$id = I('id','post','id',0);
		$uid = $this->user['id'];
		$Goods = M('goods');
		$Order = M('order');
		//将订单中的商品返库存
		$data = $Order->fetchColumn("select `goods` from __ORDER__ where `id`=$id and `user_id`=$uid");
		if(empty($data)){
			$this->error('取消失败：订单不存在');
		}
		$data = unserialize($data);
		foreach($data as $v){
			$Goods->exec("update __GOODS__ set `stock`=`stock`+{$v['num']} where `id`={$v['id']}");
		}

		//取消订单（如果有订单回收站功能，则执行此步骤）
		if(false === $Order->exec("update __ORDER__ set `cancel`='yes' where `id`=$id and `user_id`=$uid")){
			$this->error('取消失败');
		}

		//删除订单（如果没有订单回收站功能，则执行此步骤）
		$Order->exec("delete from __ORDER__ where `id`=$id and `user_id`=$uid");

		$this->success('','/?c=order');
	}
	
	//测试
	public function myindexAction(){
		require ACTION_VIEW;
	}
	public function checkoutAction(){
		require ACTION_VIEW;
	}
}
