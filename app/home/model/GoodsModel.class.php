<?php
//商品模型
class GoodsModel extends Model{
	/**
	 * 获取商品列表
	 * @param array|int $cids 分类ID数组
	 * @param int $page 当前页码
	 * @return array 查询结果
	 */
	public function getData($cids,$page){
		//准备查询条件
		$where = "`recycle`='no' and `on_sale`='yes'";
		//查找分类ID数组
		if(is_array($cids)){
			$where .= ' and `category_id` in ('.implode(',',$cids).')';
		}
		$price_max = $this->fetchColumn("select max(`price`) from __GOODS__ where $where"); //获取最大价格
		$recommend = $this->getRecommend($where); //获取推荐商品
		//处理排序条件
		$order = 'id desc';
		$allow_order = array(
			'price-desc' => 'price desc',
			'price-asc' => 'price asc',
		);
		$input_order = I('order','get','string');
		if(isset($allow_order[$input_order])){
			$order = $allow_order[$input_order];
		}
		//处理价格条件
		$price = explode('-',I('price','get','string'));
		if(isset($price[1])){
			//价格区间查询
			$where .= ' and `price`>='.(int)$price[0].' and `price`<='.(int)$price[1];
		}
		//准备分页查询
		$pagesize = 12;        //每页显示商品数
		$total = $this->fetchColumn("select count(*) from __GOODS__ where $where"); //获取符合条件的商品总数
		$Page = new Page($total,$pagesize,$page); //实例化分页类
		$limit = $Page->getLimit();
		//查询商品数据
		$data = $this->fetchAll("select `category_id`,`id`,`name`,`price`,`thumb` from __GOODS__ where $where order by $order limit $limit");
		//返回结果
		return array(
			'data' => $data,              //商品列表数组
			'price' => $this->_getPriceDist($price_max), //计算商品价格
			'recommend' => $recommend,    //被推荐的商品
			'pagelist' => $Page->show(),  //分页链接HTML
		);
	}
	//查询前台首页推荐的商品
	public function getBest(){
		return $this->getRecommend("`on_sale`='yes' and `recycle`='no'");
	}
	//根据分类获取推荐商品
	public function getRecommendByCids($cids){
		$where = "`recycle`='no' and `on_sale`='yes' and `category_id` in (".implode(',',$cids).")";
		return $this->getRecommend($where);
	}
	//根据where条件取出推荐商品
	public function getRecommend($where='1=1'){
		//查询被推荐的商品
		$where .= " and `recommend`='yes'";
		//取出商品id，商品名，商品价格，商品预览图
		return $this->fetchAll("select `id`,`name`,`price`,`thumb` from __GOODS__ where $where limit 0,5");
	}
	//查询指定商品数据
	public function getGoods($id){
		return $this->data(array('id'=>$id))->fetchRow("select `id`,`category_id`,`sn`,`name`,`price`,`thumb`,`album`,`stock`,`desc` from __GOODS__ where `recycle`='no' and `on_sale`='yes' and `id`=:id");
	}
	//动态计算价格
	//（max最大价格，count分配个数）
	private function _getPriceDist($max, $count=5){
		if($max<=0){ //当 $max低于1 时返回false
			return false;
		}
		$end = $size = ceil($max / $count);
		$start = 0;
		$rst = array();
		for ($i = 0; $i < $count; $i++) {
			$rst[] = "$start-$end";
			$start = $end + 1;
			$end += $size;
		}
		return $rst;
	}
}