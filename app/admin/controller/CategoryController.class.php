<?php
//后台商品分类
class CategoryController extends CommonController{
	
	//查看所有的分类
	public function indexAction(){
		//查询分类数据
		$data = D('category')->getData('_tree');
		//显示视图
		require ACTION_VIEW;
	}
	public function myindexAction(){
		//查询分类数据
		$data = D('category')->getData('_tree');
		//显示视图
		require ACTION_VIEW;
	}
	
	//添加分类
	public function addAction(){
		$id = I('id','get','id'); //默认选中的ID
		$tip = I('tip','get','bool'); //是否显示提示
		//查询分类数据
		$data = D('category')->getData('_tree');
		//显示视图
		require ACTION_VIEW;
	}
	public function addExecAction(){
		//接收变量
		$pid = I('pid','post','id');
		$name = I('name','post','text');
		//表单验证
		$this->_checkForm('name',$name,'添加');
		//添加数据
		$id = M('category')->data(array('pid'=>$pid,'name'=>$name))->add();
		if(isset($_POST['return'])){
			$this->success('','/?p=admin&c=category');
		}else{
			$this->success('',"/?p=admin&c=category&a=add&id=$pid&tip=1");
		}
	}
	
	//修改分类
	public function editAction(){
		$id = I('id','get','id'); //待修改的分类ID
		$tip = I('tip','get','bool'); //是否显示提示
		//查询分类数据
		$data = D('category')->getData('_tree');
		isset($data[$id]) || E('您修改的分类不存在');
		//显示视图
		require ACTION_VIEW;
	}
	public function editExecAction(){
		//接收变量
		$id = I('id','post','id'); //接收待修改的分类ID
		$pid = I('pid','post','id'); //接收修改后的上级分类ID
		$name = I('name','post','text'); //接收修改后的分类名称
		//表单验证
		$this->_checkForm('name',$name,'修改');
		//创建分类模型
		$Category = D('category');
		//验证上级分类ID是否合法
		if(in_array($pid,$Category->getSubIds($id))){
			$this->error('修改失败：不允许将父分类修改为自身或子级分类');
		}
		//修改数据
		$Category->data(array('id'=>$id,'name'=>$name,'pid'=>$pid))->save('id');
		if(isset($_POST['return'])){
			$this->success('','/?p=admin&c=category');
		}else{
			$this->success('',"/?p=admin&c=category&a=edit&id=$id&tip=1");
		}
	}
	
	//删除分类
	public function delExecAction(){
		$id = I('id','post','id');
		$Category = D('category');
		//只允许删除最底层分类
		if($Category->exists('pid',$id)){
			$this->error('删除失败，只允许删除最底层分类。');
		}
		//将该分类下的商品设为未分类
		M('goods')->data(array('id'=>$id,'category_id'=>0))->save();
		//删除分类
		$Category->delById($id);
		$this->success();
	}
	
	//表单验证
	protected function _checkForm($name,$value,$type){
		//判断name字段是否为空
		if($name=='name' && $value==""){
			$this->error("{$type}失败：分类名称不能为空。");
		}
	}
}
