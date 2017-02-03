<?php
class wx_qrcode {
	
	 // 微信公众平台APPID
    private $appid;
    // 微信公众平台APP SECRET
    private $app_secret;
    // accesstoken
    private $token;

    /**
     * 构造方法
     */
    function __construct() {
        $this->appid = 'wx9ad6d625246b54b0';
        $this->app_secret = '27f299e76fb681b6a455f209c7731df0';
    }
	
	/**
	 * 远程获取数据，POST模式
	 * 注意：
	 * 1.使用Crul需要修改服务器中php.ini文件的设置，找到php_curl.dll去掉前面的";"就行了
	 * 2.文件夹中cacert.pem是SSL证书请保证其路径有效，目前默认路径是：getcwd().'\\cacert.pem'
	 * @param $url 指定URL完整路径地址
	 * @param $para 请求的数据
	 * @param $input_charset 编码格式。默认值：空值
	 * return 远程输出的数据
	 */
	private function getHttpResponsePOST($url, $para, $input_charset = '') {

		if (trim($input_charset) != '') {
			$url = $url."_input_charset=".$input_charset;
		}
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);//SSL证书认证
		curl_setopt($curl, CURLOPT_HEADER, 0 ); // 过滤HTTP头
		curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
		curl_setopt($curl,CURLOPT_POST,true); // post传输数据
		curl_setopt($curl,CURLOPT_POSTFIELDS,$para);// post传输数据
		$responseText = curl_exec($curl);
		//var_dump( curl_error($curl) );//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
		curl_close($curl);
		
		return $responseText;
	}
	/**
	 * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串，并对字符串做urlencode编码
	 * @param $para 需要拼接的数组
	 * return 拼接完成以后的字符串
	 */
	private function createLinkstringUrlencode($para) {
		$arg  = "";
		while (list ($key, $val) = each ($para)) {
			$arg.=$key."=".urlencode($val)."&";
		}
		//去掉最后一个&字符
		$arg = substr($arg,0,count($arg)-2);
		
		//如果存在转义字符，那么去掉转义
		if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}
		
		return $arg;
	}
	
	
	/**
	 * 远程获取数据，GET模式
	 * 注意：
	 * 1.使用Crul需要修改服务器中php.ini文件的设置，找到php_curl.dll去掉前面的";"就行了
	 * 2.文件夹中cacert.pem是SSL证书请保证其路径有效，目前默认路径是：getcwd().'\\cacert.pem'
	 * @param $url 指定URL完整路径地址
	 * @param $cacert_url 指定当前工作目录绝对路径
	 * return 远程输出的数据
	 */
	private function getHttpResponseGET($url) {
		$curl = curl_init($url);
	    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);//SSL证书认证
		curl_setopt($curl, CURLOPT_HEADER, 0 ); // 过滤HTTP头
		curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
		$responseText = curl_exec($curl);
		//var_dump( curl_error($curl) );//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
		curl_close($curl);
		
		return $responseText;
	}
	
	/**
     * 获取全局TOKEN
     * 
     * @return Array TOKEN及有效期
     */
    private function get_token() {
        // 请求的URL
        $url = 'https://api.weixin.qq.com/cgi-bin/token';
        
        // 公众号的唯一标识
        $param_arr['appid'] = $this->appid;
        // 公众号的appsecret
        $param_arr['secret'] = $this->app_secret;
        $param_arr['grant_type'] = 'client_credential';
        // 返回经过urlencode编码的参数=参数值字符串
        $param_str = $this->createLinkstringUrlencode($param_arr);
        // 发送GET请求
        $responseText = $this->getHttpResponseGET($url . '?' . $param_str);
        // 对返回结果进行数组化
        $result_arr = json_decode($responseText);

        return $result_arr;
    }
	
    /**
     * 获取ticket 用于换取二维码
     * 
     */
    private function get_ticket($param_arr) {
        $post_arr = array();
        $url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$this->token;

        $post_arr['action_name'] = $param_arr['action_name'];
        if (isset($param_arr['scene_id'])) {
        	$post_arr['expire_seconds'] = $param_arr['expire_seconds'];
	        $scene_id = $param_arr['scene_id'];
	        $post_arr['action_info'] = array('scene'=>array('scene_id' => $scene_id ));
        } else {
        	$scene_str = $param_arr['scene_str'];
	        $post_arr['action_info'] = array('scene'=>array('scene_str' => $scene_str ));
        }
        
        $responseText = $this->getHttpResponsePOST($url, json_encode($param_arr));
   
        return json_decode($responseText);
    }
    
    /**
     * 设置全局TOKEN
     * 
     */
    function set_token() {
    	$result_token = $this->get_token();
    	$this->token = $result_token->access_token;
    
    }
    
    /**
     * 获取二维码
     * 
     */
    function get_qrcode($param_arr) {
    	$url = 'https://mp.weixin.qq.com/cgi-bin/showqrcode';
    	$url_arr = array();
    	$ticket = $this->get_ticket($param_arr);
    	$url_arr['ticket'] = $ticket->ticket;
    	
    	$param_str = $this->createLinkstringUrlencode($url_arr);
        
        // 对返回结果进行数组化
        return $this->getHttpResponseGET($url . '?' . $param_str);;
    }


}