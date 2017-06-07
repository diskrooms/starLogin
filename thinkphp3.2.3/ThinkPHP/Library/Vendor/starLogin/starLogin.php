<?php
class starLogin{
	private $weChatAppId = '';		//微信公众号AppId
	private $weChatAppSecret = '';	//微信公众号AppSecret
	
	public function __construct($appId = '',$appSecret = ''){
		if(empty($appId) || empty($appSecret)){
			throw new exception('appId和appSecret不能为空');
		}
		$this->weChatAppId = $appId;
		$this->weChatAppSecret = $appSecret;
	}
	
	//公众号登录
	public function weChatOALogin($callback='',$scope = 0){
		//dump($_SERVER);
		$curUri = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];	//当前访问地址(不带参数)
		$code = addslashes(trim($_GET['code']));
		if(empty($code)){
			$callback = empty($callback) ? $_SERVER['HTTP_REFERER'] : $callback;
			if(empty($callback)){
				throw new exception('回调地址不能为空');	
			}
			$callback = urlencode($callback);
			$redirectUri = urlencode($curUri.'?callback='.$callback);
			$scope = ($scope == 0) ? 'snsapi_base' : 'snsapi_userinfo';
			$authorizeUrl = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$this->weChatAppId.'&redirect_uri='.$redirectUri.'&response_type=code&scope='.$scope.'&state=isWXAddr&connect_redirect=1#wechat_redirect';	
			header('Location:'.$authorizeUrl);
			exit();
		} else {
			$callback = addslashes(trim($_GET['callback']));
			if(empty($callback)){
				throw new exception('没有接收到回调地址');	
			}
			$access_token_json = file_get_contents('https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$this->weChatAppId.'&secret='.$this->weChatAppSecret.'&code='.$code.'&grant_type=authorization_code');

			$access_token_arr = json_decode($access_token_json,true);
			$access_token = $access_token_arr['access_token'];
			$openid = $access_token_arr['openid'];
			//读取用户信息
			$userinfo_json = file_get_contents('https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN');
            $userinfo_arr = json_decode($userinfo_json,true);
			header('Location:'.$callback);
			return $userinfo_arr;
		}
	}
	
	//二维码扫描登录
	
}