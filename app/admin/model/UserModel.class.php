<?php
class UserModel extends Model{
	//获取数据
	public function getData($page){
		//获取符合条件的总数
		$total = $this->fetchColumn('select count(*) from __USER__');
		$pagesize = 10; //每页显示数据条数
		//准备分页查询
		$Page = new Page($total,$pagesize,$page);
		//查询数据
		$data = $this->fetchAll('select `id`,`username`,`phone`,`email` from __USER__ limit '.$Page->getLimit());
		//返回结果
		return array(
			'data' => $data, //会员列表数组
			'pagelist' => $Page->show(),  //分页链接HTML
		);
	}
}