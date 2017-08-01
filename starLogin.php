<?php
class starLogin{
	private $weChatAppId = '';		//微信公众号AppId
	private $weChatAppSecret = '';	//微信公众号AppSecret
	
	private $webQQAppId = '';		//QQ AppId
	private $webQQAppSecret = '';	//QQ AppSecret
	
	public function __construct($appId = '',$appSecret = '',$type=1){
		if(empty($appId) || empty($appSecret)){
			throw new exception('appId和appSecret不能为空');
		}
		if($type){
			$this->weChatAppId = $appId;
			$this->weChatAppSecret = $appSecret;
		} else {
			$this->webQQAppId = $appId;
			$this->webQQAppSecret = $appSecret;
		}
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
			$openid = $access_token_arr['openid'];
			$access_token = $access_token_arr['access_token'];
			$userinfo = $this->requestGet('https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token.'&openid='.$openid);
			return json_decode($userinfo,true);
		}
	}
	
	//web QQ登录
	//注意connect.qq.com上的回调地址的大小写
	public function webQQLogin(){
		$curUrl = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];	//当前访问地址(不带参数)
		$code = addslashes(trim($_GET['code']));
		$HTTP_REFERER = isset($_SERVER['HTTP_REFERER'])  ? addslashes(trim($_SERVER['HTTP_REFERER'])) : '';
		$callback = addslashes(trim($_GET['callback'])) ? addslashes(trim($_GET['callback'])) : $HTTP_REFERER;
		$redirectUrl = $curUrl.'?callback='.urlencode($callback);
		if(empty($code)){
			$scope = 'get_user_info';
			$authorizeUrl = "https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id=".$this->webQQAppId."&redirect_uri=".urlencode($redirectUrl)."&state=".md5(uniqid(rand(), TRUE))."&scope=".$scope;
			header('Location:'.$authorizeUrl);
			exit();
		} else {
			$access_token_str = $this->requestGet('https://graph.qq.com/oauth2.0/token?grant_type=authorization_code&client_id='.$this->webQQAppId.'&client_secret='.$this->webQQAppSecret.'&code='.$code.'&redirect_uri='.urlencode($redirectUrl));
			parse_str($access_token_str,$access_token_arr);
			//获取openid
			$openid_str = $this->requestGet('https://graph.qq.com/oauth2.0/me?access_token='.$access_token_arr['access_token'].'&unionid=1');
			//echo $openid_str;
			//exit();
			$openid_str = str_replace('callback(','', $openid_str);
			$openid_str = str_replace(');','', $openid_str);
			$openid_arr = json_decode(trim($openid_str),true);
			$openid = isset($openid_arr['openid']) ? $openid_arr['openid'] : '';
			$unionid = isset($openid_arr['unionid']) ? $openid_arr['unionid'] : '';
			//获取用户信息
			$get_user_info_str = $this->requestGet('https://graph.qq.com/user/get_user_info?access_token='.$access_token_arr['access_token'].'&oauth_consumer_key='.$this->webQQAppId.'&openid='.$openid);
			$qq_user_info_arr = json_decode(trim($get_user_info_str),true);
			$qq_user_info_arr['openid'] = $openid;
			$qq_user_info_arr['unionid'] = $unionid;
			//dump($qq_user_info_arr);
			return $qq_user_info_arr;
		}
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
	//url 		要请求的url地址
	//timeout 	超时时间
	//count		请求总数(超时重发)
	private function requestGet($url = '',$timeout = 6,$count = 3){
		static $index = 0 ;
    	$index++;
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
	        if($content === false){
				if(curl_errno($ch) == CURLE_OPERATION_TIMEDOUT){
					if($index < $count){
						//超时重发
						$this->requestGet($url);
					}
				}
			}
			curl_close($ch);
		} else {
			$content = file_get_contents($url);
		}
		return $content;
	}
	
	//POST请求数据 如果有curl扩展 就使用curl进行请求 如果没有相应模块 就是用file_get_contents函数
	//url 		要请求的url地址
	//timeout 	超时时间
	//count		请求总数(超时重发)
	private function requestPost($url='',$data=array(),$timeout=6,$count=3){
		static $index = 0 ;
    	$index++;
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
	        if($content === false){
				if(curl_errno($ch) == CURLE_OPERATION_TIMEDOUT){
					if($index < $count){
						//超时重发
						$this->requestPost($url,$data);
					}
				}
				
			} 
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