<?php
//订单模型
class OrderModel extends Model{
	//根据用户ID查询订单
	public function getData($user_id){
		$this->data = array('user_id'=>$user_id);
		$data = $this->fetchAll("select * from __ORDER__ where `user_id`=:user_id and `cancel`='no' order by `id` desc");
		// echo("方法中的data <pre>");
		// var_dump($data);
		foreach($data as $k=>$v){
			$data[$k]['goods'] = unserialize($data[$k]['goods']);  
			//理解： userialize()将goods的值转换为数组形式
			$data[$k]['address'] = unserialize($data[$k]['address']);
		}
		
		return $data;
	}
}