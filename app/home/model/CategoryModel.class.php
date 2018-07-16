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
	//查询多维数组
	private function _childList($arr,$pid=0){
		$list = array();
		foreach ($arr as $v){
			if ($v['pid'] == $pid){
				$child = $this->_childList($arr,$v['id']);
				$v['child'] = $child;
				$list[] = $v;
			}
		}
		return $list;
	}
	//查找分类的家谱
	public function getFamily($id){
		$data = $this->getData(); //获取数据
		$rst = $this->getParent($id);
		foreach(array_reverse($rst['pids']) as $v){
			foreach($data as $vv){
				($vv['pid']==$v) && $rst['parent'][$v][] = $vv;
			}
		}
		return $rst;
	}
	//根据任意分类ID查找父分类（包括自己）
	public function getParent($id=0){
		$data = $this->getData(); //获取数据
		$rst = array('pcat'=>array(),'pids'=>array($id));
		for($i=0;$id && $i<5;++$i){  //限制最多取出的层级
			foreach($data as $v){
				if($v['id']==$id){
					$rst['pcat'][] = $v;  //父分类
					$rst['pids'][] = $id = (int)$v['pid']; //父分类ID
				}
			}
		}
		return $rst;
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
	//查询树状数据
	private function _tree($arr,$pid=0,$level=0){
		static $tree = array();
		foreach($arr as $v){
			if($v['pid'] == $pid){
				$v['level'] = $level;
				$tree[$v['id']] = $v;
				$this->_tree($arr,$v['id'],$level+1);
			}
		}
		return $tree;
	}
	//查找分类面包屑导航
	public function getPath($id){
		$data = $this->getParent($id);
		return array_reverse($data['pcat']);
	}
}
