<?php
//购物车模型
class ShopcartModel extends Model{
	//从购物车获得商品信息
	public function getData($user_id){
		$this->data['user_id'] = $user_id;
		return $this->fetchAll("select g.name,g.price,c.id,c.add_time,c.goods_id,c.num from __GOODS__ AS g left join __SHOPCART__ as c ON g.id=c.goods_id where `user_id`=:user_id");
	}
	//添加到购物车
	public function addCart($gid,$uid,$num){
		$rst = $this->data(array('goods_id'=>$gid,'user_id'=>$uid))->fetchRow('select `id`,`goods_id`,`num` from __SHOPCART__ where `goods_id`=:goods_id and `user_id`=:user_id');
		if(empty($rst)){ //不存在时添加到购物车
			$this->data(array('user_id'=>$uid,'goods_id'=>$gid,'num'=>$num))->add();
		}else{  //存在商品时，增加购买数量
			$num += $rst['num'];
			$this->data(array('id'=>$rst['id'],'num'=>$num))->save();
		}
	}
	//删除购物车中的商品
	public function delete($id,$user_id){
		$this->data['id'] = $id;
		$this->data['user_id'] = $user_id;
		return $this->exec('delete from __SHOPCART__ where `id`=:id and `user_id`=:user_id');
	}
}