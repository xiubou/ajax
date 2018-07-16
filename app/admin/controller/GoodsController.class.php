<?php
//后台商品控制器
class GoodsController extends CommonController{
	
	//商品添加
	public function addAction(){
		$tip = I('tip','get','bool');
		$data = array();
		//查询分类数据
		$data['category'] = D('category')->getData('_tree');
		require ACTION_VIEW;
	}
	//商品修改
	public function editAction(){
		$id = I('id','get','id');
		$cid = I('cid','get','id');
		$page = I('page','get','id',1);
		$tip = I('tip','get','bool');
		$data = array();
		//查询分类数据
		$Category = D('category');
		$data['category'] = $Category->getData('_tree');
		//商品原数据
		$data['goods'] = D('goods')->getDataById($id);
		require ACTION_VIEW;
	}
	//添加处理
	public function addExecAction(){
		//接收表单数据
		$fields = array('category_id','name','sn','price','stock','on_sale','recommend','desc','album','thumb');
//array(10) {
//[0]=>
//string(11) "category_id"
//[1]=>
//string(4) "name"
//[2]=>
//string(2) "sn"
//[3]=>
//string(5) "price"
//[4]=>
//string(5) "stock"
//[5]=>
//string(7) "on_sale"
//[6]=>
//string(9) "recommend"
//[7]=>
//string(4) "desc"
//[8]=>
//string(5) "album"
//[9]=>
//string(5) "thumb"
//}
//---------------------	print_r("goodscontroller addExec");
		
		$data = array();
		foreach($fields as $v){
			$data[$v] = $this->_input($v);
		}
		//为封面和相册生成缩略图
		$test=$this->_createThumb($data['thumb']);	
//-------------------------------------------------------------------------------
//		print_r("GoodsController addExec 中createThumb方法执行结果：");
//		var_dump($test);  //bool(false)    $data['thumb']无传输值所以创建失败
		
		//保存数据
		$Goods = D('goods');
//---------------------------------------------------------------------------
//		echo "goods   ";
//		var_dump($Goods);		
//------------------------------------------------------
		$id = $Goods->data($data)->add();
//		var_dump($id);  //string(2) "43"

		//返回
		if(isset($_POST['return'])){
//-------------------------------------------------------------
//			echo "p=admin&c=goods&a=index";
//			var_dump($fields);
//			var_dump($test);
			$this->success('','/?p=admin&c=goods&a=index');
		}else{
			$this->success('','/?p=admin&c=goods&a=add&tip=1&id='.$id);
		}
	}
	//修改处理
	public function editExecAction(){
		$id = I('id','get','id');
		$cid = I('cid','get','id');
		$page = I('page','get','id',1);
		//接收表单数据
		$fields = array('category_id','name','sn','price','stock','on_sale','recommend','desc','album','thumb');
		$data = array('id'=>$id);
		foreach($fields as $v){
			$data[$v] = $this->_input($v);
		}
		//为封面和相册生成缩略图
		$this->_createThumb($data['thumb']);
		//保存数据
		$Goods = D('goods');
		$Goods->data($data)->save('id');
		//返回
		if(isset($_POST['return'])){
			$this->success('','/?p=admin&c=goods&a=index');
		}else{
			$this->success('',"/?p=admin&c=goods&a=edit&tip=1&id=$id&cid=$cid&page=$page");
		}
	}
	
	//商品列表
	public function indexAction(){
		$cid = I('cid','get','int',-1); //分类ID
		$page = I('page','get','id',1); //页码
		$data = array();
		//查询分类数据
		$Category = D('category');
		$data['category'] = $Category->getData('_tree');
		//如果分类ID大于0，则取出所有子分类ID
		$cids = ($cid>0) ? $Category->getSubIds($cid) : $cid;
		//获取商品列表
		$Goods = D('goods');
		$data['goods'] = $Goods->getData('goods',$cids,$page);
		//超出页码时自动返回第1页
		if(empty($data['goods']['data']) && $page>1){
			$this->redirect("/?p=admin&c=goods&a=index&cid=$cid");
		}
		require ACTION_VIEW;
	}
	public function myindexAction(){
		$cid = I('cid','get','int',-1); //分类ID
		$page = I('page','get','id',1); //页码
		$data = array();
		//查询分类数据
		$Category = D('category');
		$data['category'] = $Category->getData('_tree');
		//如果分类ID大于0，则取出所有子分类ID
		$cids = ($cid>0) ? $Category->getSubIds($cid) : $cid;
		//获取商品列表
		$Goods = D('goods');
		$data['goods'] = $Goods->getData('goods',$cids,$page);
		//超出页码时自动返回第1页
		if(empty($data['goods']['data']) && $page>1){
			$this->redirect("/?p=admin&c=goods&a=myindex&cid=$cid");
		}
		require ACTION_VIEW;
	}
	
	//接收表单并进行验证
	private function _input($name){
		switch($name){
			case 'category_id'://分类ID
				$value = I('category_id','post','id');  //即$value=$_POST['category_id']
			break;
			case 'name'://商品名称
				$value = I('name','post','text');
				($value=="" || mb_strlen($value)>40) && $this->error('商品名称不合法（1-40个字符）。');
			break;
			case 'sn'://商品编号
				$value = I('sn','post','text');
				preg_match('/^[0-9A-Za-z]{1,10}$/',$value) || $this->error('商品编号不合法（字母或数字，1-10个字符）。');
			break;
			case 'price'://商品价格
				$value = I('price','post','float');
				($value<0.01 || $value>100000) && $this->error('商品价格输入不合法（0.01~100000）');
			break;
			case 'stock'://商品库存
				$value = I('stock','post','int');
				($value<0 || $value>900000) && $this->error('商品库存输入不合法（0~900000）');
			break;
			case 'on_sale'://商品上架
				$value = I('on_sale','post','text');
				in_array($value,array('yes','no')) || $this->error('商品上架字段填写错误');
			break;
			case 'recommend'://商品推荐
				$value = I('recommend','post','text');
				in_array($value,array('yes','no')) || $this->error('商品推荐字段填写错误');
			break;
			case 'desc': //商品描述
				$value = I('desc','post','string');
				$value = HTMLPurifier($value); //富文本过滤
				isset($value[65535]) && $this->error('商品描述内容过多');
			break;	
			case 'album'://商品相册
				$value = I('album','post','');
				$value = htmlspecialchars(is_array($value) ? implode('|',$value) : '');
//------------------------------------------------------------------------------------------------
//				echo "ablumvalue    ";	
//				var_dump($value);
				
				isset($value[65535]) && $this->error('商品相册内容过多');
			break;
			case 'thumb'://商品封面图
				$value = I('thumb','post','text');
				isset($value[65535]) && $this->error('商品封面图内容过多');
			break;
		}
		return $value;
	}
	
	//单个字段修改
	public function changeExecAction(){
		$id = I('id','post','id');
		$name = I('name','post','text');
		$value = I('value','post','text');
		$allow_name = array('recycle','on_sale','recommend');
		if(in_array($name,$allow_name)){
			D('goods')->change($id,$name,$value);
			$this->success();
		}else{
			$this->error('修改失败，指定字段不允许修改。');
		}
	}
	
	//为封面生成缩略图
	private function _createThumb($thumb){
		
//----------------------------------------------------------------
//		echo "thumb";
//		var_dump($thumb);
		
		if($thumb==''){
			return false;
		}
		$filePath = "./public/upload/big/$thumb";   //原图的保存目录
		$savePath = "./public/upload/small/$thumb"; //封面图的保存目录
		
//---------------------------print_r("gooscontroller _createThumb");		
//		var_dump($filePath);
//		echo "<br />";
//		var_dump($savePath);
		
		//当图片不存在时创建
		if(!is_file($savePath)){
			try{
				$Image = new Image($filePath);
			}catch(Exception $e){
				$this->error($e->getMessage());
			}
			$Image->thumbFilled(220, 220)->save($savePath);
		}
		return true;
	}
	
	
	
	
/*///////////////////////////////////////////////////////////////////////////////////////////////////////	
	public function myaddExecAction(){
		//接收表单数据
		$arr=$_POST;   //获取传入值
		var_dump($arr);
		
		$data = array();
		$fields=array_keys($arr);
		var_dump($fields);
		foreach($fields as $v){
			$data[$v] = $this->_input($v);
		}
		$path="./public/upload/big";
		$uploadFiles=uploadFiles($path);
		if(is_array($uploadFiles)&&$uploadFiles){
			foreach($uploadFiles as $key=>$uploadFile){
				//thumb(原存储位置,生成缩略图位置，w,h)
				thumb($path."/".$uploadFile['name'],"../image_320/".$uploadFile['name'],320,320);
				thumb($path."/".$uploadFile['name'],"../image_200/".$uploadFile['name'],200,200);
			}
		}
		$data['ablum']=$uploadFiles['name'];
		$data['thumb']=$uploadFiles['name'];
		$Goods = D('goods');
		$id = $Goods->data($data)->add();
		if($id){
			foreach($uploadFiles as $uploadFile){
				$arr1['pid']=$pid;
				$arr1['albumPath']=$uploadFile['name'];
				addAlbum($arr1);
			}
			$this->success('','/?p=admin&c=goods&a=add&tip=1&id='.$id);
		}else{
			foreach($uploadFiles as $uploadFile){
				if(file_exists("../image_320/".$uploadFile['name'])){
					unlink("../image_320/".$uploadFile['name']);
				}
				if(file_exists("../image_200/".$uploadFile['name'])){
					unlink("../image_200/".$uploadFile['name']);
				}
			}
			$this->success('','/?p=admin&c=goods&a=add&tip=1&id='.$id);	
		}
		
	}
	public function thumb($filename,$destination=null,$dst_w=null,$dst_h=null,$isReservedSource=true,$scale=0.5){
		list($src_w,$src_h,$imagetype)=getimagesize($filename);
		if(is_null($dst_w)||is_null($dst_h)){
			$dst_w=ceil($src_w*$scale);
			$dst_h=ceil($src_h*$scale);
		}
		$mime=image_type_to_mime_type($imagetype);
		$createFun=str_replace("/", "createfrom", $mime);
		$outFun=str_replace("/", null, $mime);
		$src_image=$createFun($filename);
		$dst_image=imagecreatetruecolor($dst_w, $dst_h);
		imagecopyresampled($dst_image, $src_image, 0,0,0,0, $dst_w, $dst_h, $src_w, $src_h);
		if($destination&&!file_exists(dirname($destination))){
			mkdir(dirname($destination),0777,true);
		}
		$dstFilename=$destination==null?getUniName().".".getExt($filename):$destination;
		$outFun($dst_image,$dstFilename);
		imagedestroy($src_image);
		imagedestroy($dst_image);
		if(!$isReservedSource){
			unlink($filename);
		}
		return $dstFilename;
	}
	// 生成唯一字符串
	function getUniName(){
		return md5(uniqid(microtime(true),true));
	}
	// 得到文件的扩展名
	function getExt($filename){
		$a=explode(".",$filename);
		return strtolower(end($a));
	}
	public function buildInfos(){
		if(!$_FILES){
			return ;
		}
		$i=0;
		foreach($_FILES as $v){
			//单文件
			if(is_string($v['name'])){
				$files[$i]=$v;
				$i++;
			}else{
				//多文件
				foreach($v['name'] as $key=>$val){
					$files[$i]['name']=$val;
					$files[$i]['size']=$v['size'][$key];
					$files[$i]['tmp_name']=$v['tmp_name'][$key];
					$files[$i]['error']=$v['error'][$key];
					$files[$i]['type']=$v['type'][$key];
					$i++;
				}
			}
		}
		return $files;
	}
	public function uploadFiles($path="./public/upload/big",$allowExt=array("gif","jpeg","png","jpg","wbmp"),$maxSize=2097152,$imgFlag=true){
		if(!file_exists($path)){
			mkdir($path,0777,true);
		}
		$i=0;
		$files=buildInfo();
		if(!($files&&is_array($files))){
			return ;
		}
		foreach($files as $file){
			if($file['error']===UPLOAD_ERR_OK){
				$ext=getExt($file['name']);
				//检测文件的扩展名
				if(!in_array($ext,$allowExt)){
					exit("非法文件类型");
				}
				//校验是否是一个真正的图片类型
				if($imgFlag){
					if(!getimagesize($file['tmp_name'])){
						exit("不是真正的图片类型");
					}
				}
				//上传文件的大小
				if($file['size']>$maxSize){
					exit("上传文件过大");
				}
				if(!is_uploaded_file($file['tmp_name'])){
					exit("不是通过HTTP POST方式上传上来的");
				}
				$filename=getUniName().".".$ext;
				$destination=$path."/".$filename;
				if(move_uploaded_file($file['tmp_name'], $destination)){
					$file['name']=$filename;
					unset($file['tmp_name'],$file['size'],$file['type']);
					$uploadedFiles[$i]=$file;
					$i++;
				}
			}else{
				switch($file['error']){
						case 1:
							$mes="超过了配置文件上传文件的大小";//UPLOAD_ERR_INI_SIZE
							break;
						case 2:
							$mes="超过了表单设置上传文件的大小";			//UPLOAD_ERR_FORM_SIZE
							break;
						case 3:
							$mes="文件部分被上传";//UPLOAD_ERR_PARTIAL
							break;
						case 4:
							$mes="没有文件被上传";//UPLOAD_ERR_NO_FILE
							break;
						case 6:
							$mes="没有找到临时目录";//UPLOAD_ERR_NO_TMP_DIR
							break;
						case 7:
							$mes="文件不可写";//UPLOAD_ERR_CANT_WRITE;
							break;
						case 8:
							$mes="由于PHP的扩展程序中断了文件上传";//UPLOAD_ERR_EXTENSION
							break;
					}
					echo $mes;
				}
		}
		return $uploadedFiles;
	}*/
}