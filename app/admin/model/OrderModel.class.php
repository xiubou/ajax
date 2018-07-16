<?php
//订单模型
class OrderModel extends Model{
	//根据用户ID查询订单
	public function getData($page=1){
		//获取符合条件的商品总数
		$total = $this->fetchColumn('select count(*) from __ORDER__ group by user_id');
		$pagesize = 4; //每页显示商品数
		//准备分页查询
		$Page = new Page($total,$pagesize,$page);
		$limit = $Page->getLimit();
		//查询数据
		$data = $this->fetchAll('select `id`,`user_id`,`goods`,`address`,`price`,`payment` from  __ORDER__ group by user_id limit '.$limit);
		//返回结果
		return array(
			'data' => $data, //订单列表数组
			'pagelist' => $Page->show(),  //分页链接HTML
		);
	}
	//获取指定用户信息
	public function getDataById($id){
		return $this->data(array('id'=>$id))->fetchRow('select * from __USER__ where `id`=:id');
	}
	//获取指定用户订单总数
	public function getCountById($id){
		return $this->data(array('id'=>$id))->exec('select * from __ORDER__ where `user_id`=:id');
	}
	//获取重组后数据库订单
	public function geNewData(){
		//获取符合条件的商品总数
		$total = $this->fetchColumn('select count(*) from __ORDER__ group by user_id');
		$pagesize = 4; //每页显示商品数
		//准备分页查询
		$Page = new Page($total,$pagesize,$page);
		$limit = $Page->getLimit();
	}
}