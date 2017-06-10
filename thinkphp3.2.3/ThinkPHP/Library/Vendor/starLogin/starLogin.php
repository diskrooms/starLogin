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
	
	//微信公众号登录
	public function weChatOALogin($scope = 0){
		//dump($_SERVER);
		$curUri = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];	//当前访问地址(不带参数)
		$code = addslashes(trim($_GET['code']));
		$callback = addslashes(trim($_GET['callback'])) ? addslashes(trim($_GET['callback'])) : addslashes(trim($_SERVER['HTTP_REFERER']));;
		if(empty($code)){
			$redirectUri = urlencode($curUri.'?callback='.urlencode($callback));
			$scope = ($scope == 0) ? 'snsapi_base' : 'snsapi_userinfo';
			$authorizeUrl = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$this->weChatAppId.'&redirect_uri='.$redirectUri.'&response_type=code&scope='.$scope.'&state=isWXAddr&connect_redirect=1#wechat_redirect';	
			header('Location:'.$authorizeUrl);
			exit();
		} else {
			$access_token_json = $this->requestGet('https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$this->weChatAppId.'&secret='.$this->weChatAppSecret.'&code='.$code.'&grant_type=authorization_code');
			$access_token_arr = json_decode($access_token_json,true);
			$access_token = $access_token_arr['access_token'];
			$openid = $access_token_arr['openid'];
			
			//读取用户信息
			$userinfo_json = $this->requestGet('https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN');
            $userinfo_arr = json_decode($userinfo_json,true);
			
			return $userinfo_arr;
			
		}
	}
	
	//微信PC网站扫码登录
	public function weChatQRLogin(){
		$curUrl = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];	//当前访问地址(不带参数)
		$code = addslashes(trim($_GET['code']));
		if(empty($code)){
			$redirectUrl = urlencode($curUrl);
			$authorizeUrl = 'https://open.weixin.qq.com/connect/qrconnect?appid='.$this->weChatAppId.'&redirect_uri='.$redirectUrl.'&response_type=code&scope=snsapi_login&state=isWXAddr#wechat_redirect';	
			header('Location:'.$authorizeUrl);
			exit();
		} else {
			$access_token_json = $this->requestGet('https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$this->weChatAppId.'&secret='.$this->weChatAppSecret.'&code='.$code.'&grant_type=authorization_code');
			$access_token_arr = json_decode($access_token_json,true);
			return $access_token_arr;
		}
	}
	
	//web QQ登录
	public function webQQLogin(){
		
	}
	
	
	public function callback($callback=''){
		if(empty($callback)){
			$callback = addslashes(trim($_GET['callback'])) ? addslashes(trim($_GET['callback'])) : addslashes(trim($_SERVER['HTTP_REFERER']));
		}
		if(empty($callback)){
			throw new exception('回调地址不能为空');
		}
		header('Location:'.$callback);
	}
	
	//GET请求数据 如果有curl扩展 就使用curl进行请求 如果没有相应模块 就使用file_get_contents函数
	private function requestGet($url='',$timeout=6){
		if(empty($url)){
			throw new exception('url参数不能为空');
		}
		$ch = curl_init();
		if($ch){
			//设置curl
			curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,FALSE); //不认证证书
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,FALSE);
			curl_setopt($ch, CURLOPT_HEADER, FALSE);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			$content = curl_exec($ch);
	        curl_close($ch);
		} else {
			$content = file_get_contents($url);
		}
		return $content;
	}
	
	//POST请求数据 如果有curl扩展 就使用curl进行请求 如果没有相应模块 就是用file_get_contents函数
	private function requestPost($url='',$data=array(),$timeout=6){
		if(empty($url)){
			throw new exception('url参数不能为空');
		}
		$ch = curl_init();
		if($ch){
			//设置curl
			curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,FALSE); //不认证证书
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,FALSE);
			curl_setopt($ch, CURLOPT_HEADER, FALSE);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_POST, 1);				// post方式
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);	// post数据
			$content = curl_exec($ch);
	        curl_close($ch);
		} else {
			$data = http_build_query($data);
			$context = array(
				'http'=>array(
					'method'=>'POST',
					'content'=>$data
				)
			);
			$context  = stream_context_create($context);
			$content = file_get_contents($url,false,$context);
		}
		return $content;
	}
}