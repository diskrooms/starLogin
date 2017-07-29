<?php
/**
 * @Author diskrooms
 **/
namespace User\Controller;

use Think\Controller;
//api首页
class TestTPController extends Controller {
	//初始化配置
	public function _initialize() {
		header("Content-type:text/html; charset=utf-8");
		Vendor('starLogin.starLogin');
	}
	
	//微信公众号登录接口
	public function testWeChatOALogin(){
		
		//dump($_SERVER);
		if($_SESSION['uid']){
			echo '登录成功';	
		} else {
			$login = new \starLogin('','');
			$userInfo = $login->weChatOALogin(1);
			$_SESSION['uid'] = $userInfo['openid'];
			//处理业务逻辑
			$login->callback();
		}
	}
	
	//微信PC网站扫码登录
	public function testWeChatQRLogin(){
		if($_SESSION['uid']){
			echo '登录成功';	
			
		} else {
			$login = new \starLogin('','');
			$userInfo = $login->weChatQRLogin();
			//dump($userInfo);
			//exit();
			$_SESSION['uid'] = $userInfo['openid'];
			//处理业务逻辑
			
			$login->callback('/testTP/testWeChatQRLoginIndex');
		}
		
	}
	
	//公众号登录回调
	public function testWeChatOALoginIndex(){
		
		$uid = $_SESSION['uid'] ? $_SESSION['uid'] : 0;
		
		$this->assign('uid',$uid);
		$this->display();
	}
	
	//PC微信扫码登录回调
	public function testWeChatQRLoginIndex(){
		
		$uid = $_SESSION['uid'] ? $_SESSION['uid'] : 0;
		
		$this->assign('uid',$uid);
		$this->display();
	}
	
	//webQQ登录测试页
	public function testWebQQLoginIndex(){
		$uid = $_SESSION['uid'] ? $_SESSION['uid'] : 0;
		
		$this->assign('uid',$uid);
		$this->display();
	}
	
	//webQQ登录接口
	public function testWebQQLogin(){
		if($_SESSION['uid']){
			echo '登录成功';
		} else {
			$login = new \starLogin('','',0);		//第一个参数是QQ AppId 第二个参数是QQ AppSecret 第三个参数是登录类型 1表示微信 0表示QQ 缺省为1
			$userInfo = $login->webQQLogin();
			//dump($userInfo);
			//exit();
			$_SESSION['uid'] = $userInfo['openid'];
			//处理业务逻辑
			
			$login->callback();
		}
	}
}