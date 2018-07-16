<?php
class GoodsModel extends Model{
	//获取数据
	public function getData($type='goods',$cids=0,$page=1){
		//准备查询条件
		if($type=='goods'){          //商品列表页取数据时
			$where = "g.recycle='no'";
		}elseif($type=='recycle'){   //商品回收站取数据时
			$where = "g.recycle='yes'";
		}
		//cids=0查找未分类商品，cid<0查找全部商品
		if($cids == 0){      //查找未分类的商品
			$where .= ' and g.category_id = 0';
		}elseif(is_array($cids)){  //查找分类ID数组
			$where .= ' and g.category_id in('.implode(',',$cids).')';
		}
		//获取符合条件的商品总数
		$total = $this->fetchColumn('select count(*) from __GOODS__ as g where '.$where);
		$pagesize = 4; //每页显示商品数
		//准备分页查询
		$Page = new Page($total,$pagesize,$page);
		$limit = $Page->getLimit();
		//查询数据
		$data = $this->fetchAll('select c.name as category_name,g.category_id,g.id,g.name,g.on_sale,g.stock,g.recommend,g.add_time,g.price,g.desc'.
		" from __GOODS__ as g left join __CATEGORY__ as c on c.id=g.category_id where $where order by g.id desc limit $limit");
		//返回结果
		return array(
			'data' => $data,              //商品列表数组
			'pagelist' => $Page->show(),  //分页链接HTML
		);
	}
	//获取指定商品信息
	public function getDataById($id){
		return $this->data(array('id'=>$id))->fetchRow('select `category_id`,`name`,`sn`,`price`,`stock`,`on_sale`,`recommend`,`desc`,`album`,`thumb` from __GOODS__ where `id`=:id');
	}
	
	//修改指定字段
	public function change($id,$name,$value='yes'){
		$value=='yes' || $value = 'no'; //排除非法值
		$this->data(array('id'=>$id,$name=>$value))->exec("update __GOODS__ set `$name`=:$name where `id`=:id");
	}
	//彻底删除商品
	public function delete($id){
		$this->data(array('id'=>$id))->exec("delete from __GOODS__ where `id`=:id and `recycle`='yes'");
	}
}
