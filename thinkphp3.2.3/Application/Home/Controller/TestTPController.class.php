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
	
	//微信公众号登录
	public function testWeChatOALogin(){
		
		//dump($_SERVER);
		if($_SESSION['uid']){
			echo '登录成功';	
		} else {
			$login = new \starLogin('wx157be178b8b3663f','78038fde460208de8fd0b3b149a42000');
			$userInfo = $login->weChatOALogin('http://www.yaoyaoyouxi.com/',1);
			$_SESSION['uid'] = $userInfo['openid'];
		}
	}
	
	
	
}