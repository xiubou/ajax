<?php
//文件上传控制器
class UploadController extends Controller{
	const TEMP_PATH = './public/upload/temp/';  //临时上传目录
	const DESC_PATH = './public/upload/desc/';  //商品描述缩略图保存目录
	const BIG_PATH  = './public/upload/big/';   //商品相册缩略图保存目录
	//重写父类构造方法
	public function __construct(){
		//解决flash上传没有携带COOKIE的问题
		if(isset($_POST['PHPSESSID'])){
			$sessid = I('PHPSESSID','post','text');
			session_id($sessid);
		}
		session_start();
		//验证用户是否登录
		if(!session('admin','','isset')){
			$this->_error('操作失败：请先登录。');	
		}
		//验证CSRF令牌
		if(!csrf_check_token()){
			$this->_error('操作失败：令牌错误，清除Cookie后重试。');
		}
	}
	//提供给“商品描述”的上传接口（umeditor）
	public function umeditorAction(){
		//上传文件
		$info = $this->_uploadFile('upfile',array(
			'subPath' => ''  //不创建子目录
		));
		//为上传图片自动创建缩略图
		if($info['state']=='SUCCESS'){
			$info['url'] = $this->_thumbFile($info['name'],array(
				'savePath' => self::DESC_PATH, //缩略图保存目录
				'filled'   => false, //不填充空白
				'width'    => 870,   //限制宽度最大870px
				'height'   => 9999   //限制高度最大9999px
			));
		}
		exit(json_encode($info));
	}
	//提供给“商品相册”的上传接口（uploadify）
	public function uploadifyAction(){
		//上传文件
		$info = $this->_uploadFile('upfile',array(
			'subPath' => ''  //不创建子目录
		));
//-----------------------------------------------------------------------------
		echo "info";
		print_r($info);		
		
		//为上传图片自动创建缩略图
		if($info['state']=='SUCCESS'){
			$info['url'] = $this->_thumbFile($info['name'],array(
				'savePath' => self::BIG_PATH, //缩略图保存目录
				'filled'   => true,  //填充空白
				'width'    => 800,   //限制宽度最大800px
				'height'   => 800    //限制高度最大800px
			));
		}
		exit(json_encode($info));
	}
	//执行文件上传
	private function _uploadFile($name, $config=array()){
		//定义默认参数
		$config = array_merge(array(
			'savePath'   => self::TEMP_PATH,     //上传目录
			'subPath'    => date('Y-m/d'),       //自动创建的子目录
			'allowFiles' => array('.png','.jpg') //允许的文件格式
		),$config);
		//实例化UMEditor配套的文件上传类
		$Upload = new Uploader($name, $config);
		
		//返回文件信息
		//格式：array('name','url','state')
		//name=自动生成的文件名 url=从子目录开始的文件路径 state=成功返回SUCCESS，失败返回错误信息
		return $Upload->getFileInfo();
	}
	//执行缩略图创建
	private function _thumbFile($name, $config=array()){
		//定义默认参数
		$config = array_merge(array(
			'savePath'   => self::TEMP_PATH,  //上传目录
			'subPath'    => date('Y-m/d'),    //自动创建的子目录
			'width'      => 800,              //缩略图宽度 
			'height'     => 800,              //缩略图高度
			'filled'     => false             //是否填充空白
		),$config);
		//拼接原图路径
		$file = self::TEMP_PATH.$name;
		//创建缩略图
		try{
			$Image = new Image($file);
		}catch(Exception $e){
			$this->_error($e->getMessage());
		}
		$target = $config['savePath'].$config['subPath'].$name;
		if($config['filled']){
			$Image->thumbFilled($config['width'],$config['height'])->save($target);
		}else{
			$Image->thumbScale($config['width'],$config['height'])->save($target);
		}
		//删除临时图片
		is_file($file) && unlink($file);
		//返回缩略图保存目录
		return $config['subPath'].$name;
	}
	//返回JSON信息
	private function _error($msg){
		exit(json_encode(array('state'=>$msg)));
	}
	
	
/*构建上传文件信息 */
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
public function uploadFiles($path="uploads",$allowExt=array("gif","jpeg","png","jpg","wbmp"),$maxSize=2097152,$imgFlag=true){
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
}

}