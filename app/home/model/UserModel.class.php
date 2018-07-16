<?php
//会员模型
class UserModel extends Model {
	//登录
	public function checkLogin($username,$password){
		$this->data['username'] = $username;
		$data = $this->fetchRow('select `id`,`username`,`password`,`salt` from __USER__ where `username`=:username');
		if($data && $data['password']==password($password,$data['salt'])){
			return array('id'=>$data['id'],'name'=>$data['username']);
		}
		return false;
	}
	//获取收件地址
	public function getAddr($id){
		//取出数据（收件人，收件地址，邮箱，手机号码）
		$data = $this->data(array('id'=>$id))->fetchRow('select `consignee`,`address`,`email`,`phone` from __USER__ where id=:id');
		//分割“收件地址”字符串
		$data['area'] = explode(',',$data['address'],4); //最多分割4次
		return $data;
	}
}
