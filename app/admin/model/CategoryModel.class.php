<?php
//分类模型
class CategoryModel extends Model{
	
	//查询分类数据
	public function getData($callback=false){
		//缓存数据库数据
		static $data = null;
		$data || $data = $this->fetchAll('select * from __CATEGORY__'); 
		//根据回调函数处理返回的数据
		return $callback ? $this->$callback($data) : $data;
	}
	
	/**
	 * 查询树状数据
	 * @param array $arr 给定数组
	 * @param int $pid 指定从哪个节点开始找
	 * @return array 构造好的数组
	 */
	private function _tree($arr,$pid=0,$level=0){
		static $tree = array();
		foreach($arr as $v){
			if($v['pid'] == $pid){
				$v['level'] = $level; //保存递归深度
				$tree[$v['id']] = $v; //保存数据，并设置将ID设置为数组下标
				$this->_tree($arr,$v['id'],$level+1); //递归调用
			}
		}
		return $tree;
	}
	
	//删除分类数据
	public function delById($id){
		$this->data['id'] = $id;
		return $this->exec("delete from __CATEGORY__ where id=:id");
	}
	
	//查找子孙分类ID（包括自己）
	public function getSubIds($pid){
		$data = $this->_tree($this->getData(),$pid);
		$result = array($pid); //把自身放入数组
		foreach ($data as $v){
			$result[] = $v['id'];
		}
		return $result;
	}
}
