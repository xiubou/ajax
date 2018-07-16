<?php
//后台管理员登录
class LoginController extends Controller{
	protected $title = "传智商城 - 后台管理系统"; //网页标题
	//显示登录页面
	public function indexAction(){
		require ACTION_VIEW;
	}
	public function myindexAction(){
		$title="LWW&CYC后台管理系统";
		require ACTION_VIEW;
	}
	//验证登录表单（限制登录失败次数）
	public function loginExecAction(){
//		$this->_input('captcha'); //检查验证码
		$username = $this->_input('username'); //获取用户名
		$password = $this->_input('password'); //获取密码
		//定义登录限制条件
		$limitCount = 3; //限制密码错误次数
		$limitTime = 30; //到达限制时冻结分钟数
		//实例化模型
		$Model = M();
		//根据用户名查询数据
		$result = $Model->data(array('username'=>$username))
				->fetchRow('select `id`,`username`,`password`,`salt`,`logtime`,`logcount` from __ADMIN__ where `username`=:username');
		//验证1 - 用户名不存在
		if(!$result){
			$this->error('登录失败，用户名或密码错误');
		}
		//验证2 - 达到错误次数，将被冻结
		elseif(($result['logcount'] >= $limitCount) && (strtotime($result['logtime']) >= (time()-($limitTime*60)))){
			$this->error("您的账户密码错误达到 $limitCount 次，已被冻结 $limitTime 分钟");
		}
		//验证3 - 根据用户名判断密码
		elseif($result['password'] !== password($password,$result['salt'])){
			//密码错误，记录错误时间并增加次数
			$Model->data(array('logtime'=>date('Y-m-d H:i:s'),'username'=>$username,'logcount'=>($result['logcount']>=$limitCount ? 1 : $result['logcount']+1)))
				  ->exec('update __ADMIN__ set `logtime`=:logtime,`logcount`=:logcount where `username`=:username');
			$this->error('登录失败，用户名或密码错误');	
		}
		//登录成功
		else{
			//错误次数清零
			$Model->data(array('username'=>$username))->exec('update __ADMIN__ set `logcount`=0 where `username`=:username');
			//保存到Session
			session('admin',array('name'=>$result['username'],'id'=>$result['id']),'save');
			//跳转
			$this->success('','/?p=admin&c=user&a=index'); //因为设置了默认为myindex
		}
	}
	//验证后台登录表单
	public function myloginExecAction(){
//		$this->_input('captcha'); //检查验证码
		$username = $this->_input('username'); //获取用户名
		$password = $this->_input('password'); //获取密码
		//定义登录限制条件
		$limitCount = 3; //限制密码错误次数
		$limitTime = 30; //到达限制时冻结分钟数
		//实例化模型
		$Model = M();
		//根据用户名查询数据
		$result = $Model->data(array('username'=>$username))
				->fetchRow('select `id`,`username`,`password`,`salt`,`logtime`,`logcount` from __ADMIN__ where `username`=:username');
		//验证1 - 用户名不存在
		if(!$result){
			$this->error('登录失败，用户名或密码错误');
		}
		//验证2 - 达到错误次数，将被冻结
		elseif(($result['logcount'] >= $limitCount) && (strtotime($result['logtime']) >= (time()-($limitTime*60)))){
			$this->error("您的账户密码错误达到 $limitCount 次，已被冻结 $limitTime 分钟");
		}
		//验证3 - 根据用户名判断密码
		elseif($result['password'] !== password($password,$result['salt'])){
			//密码错误，记录错误时间并增加次数
			$Model->data(array('logtime'=>date('Y-m-d H:i:s'),'username'=>$username,'logcount'=>($result['logcount']>=$limitCount ? 1 : $result['logcount']+1)))
				  ->exec('update __ADMIN__ set `logtime`=:logtime,`logcount`=:logcount where `username`=:username');
			$this->error('登录失败，用户名或密码错误');	
		}
		//登录成功
		else{
			//错误次数清零
			$Model->data(array('username'=>$username))->exec('update __ADMIN__ set `logcount`=0 where `username`=:username');
			//保存到Session
			session('admin',array('name'=>$result['username'],'id'=>$result['id']),'save');
			//跳转
			$this->success('','/?p=admin&c=user&a=myindex');  //myindex后台主页
		}
	}
	//接收表单并进行验证
	private function _input($name){
		switch($name){
			case 'captcha': //验证码
				$value = I('captcha','post','string');
				$this->_checkCaptcha($value) || $this->error('登录失败，验证码输入有误');
			break;
			case 'username'://用户名
				$value = I('username','post','string');
				preg_match('/^\w{4,10}$/',$value) || $this->error('用户名不合法（4~10位，英文、数字、下划线）');
			break;
			case 'password'://密码
				$value = I('password','post','string');
				preg_match('/^\w{6,12}$/',$value) || $this->error('密码不合法（6-12位，英文、数字、下划线）。');
			break;
		}
		return $value;
	}
	//显示验证码
	public function captchaAction(){
		$Captcha = new Captcha();
		$Captcha->create();
	}
	//判断验证码
	private function _checkCaptcha($code){
		$Captcha = new Captcha();
		return $Captcha->verify($code);
	}
	//退出登录
	public function logoutAction(){
		session('admin','','unset');
		$this->redirect('/?p=admin&c=login');
	}
}